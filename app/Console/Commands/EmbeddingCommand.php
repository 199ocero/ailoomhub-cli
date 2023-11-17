<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\EmbedCollection;
use App\Models\Embedding;
use App\Services\PageRetriever;
use Illuminate\Console\Command;
use App\Models\NotionIntegration;
use App\Models\NotionPage;
use App\Services\OpenAIAgent;
use App\Services\TextChunker;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\{error, select, text, info};

class EmbeddingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:embedding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will be used to generate embedding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()->get();

        if ($users->count() == 0) {
            error('No users found. Please create a user first.');
            error('Command: php artisan make:user');
            return;
        }

        $user = select(
            label: 'Select User',
            options: $users->pluck('name', 'id'),
            required: 'User is required.',
            scroll: 10
        );

        $notionIntegration = NotionIntegration::query()->where('user_id', $user)->get();

        if ($notionIntegration->count() == 0) {
            error('Notion integration not found. Please create an integration first.');
            error('Command: php artisan make:notion');
            return;
        }

        $userFullName = User::query()->where('id', $user)->first()->name;

        $integration = select(
            label: 'Select Notion Integration',
            options: $notionIntegration->pluck('name', 'id'),
            required: 'Notion integration is required.',
            hint: 'These are the integrations connected to user ' . $userFullName . '.',
            scroll: 10
        );

        $embedCollections = EmbedCollection::query()->where('notion_integration_id', $integration)->get();

        if ($embedCollections->count() == 0) {
            error('Embed collection not found. Please create an embed collection first.');
            error('Command: php artisan make:embed-collection');
            return;
        }

        $embedCollection = select(
            label: 'Select Embed Collection',
            options: $embedCollections->pluck('name', 'id'),
            required: 'Embed collection is required.',
            scroll: 10
        );

        $pages = NotionPage::query()->where('embed_collection_id', $embedCollection)->get();

        if ($pages->count() == 0) {
            error('Notion page not found. Please retrieve pages first.');
            error('Command: make:page-retriever');
            return;
        }

        $embedCollectionIntegration = EmbedCollection::query()->with('notionIntegration')->where('id', $embedCollection)->first();

        foreach ($pages as $page) {

            $contents = PageRetriever::make($embedCollectionIntegration->notionIntegration->token)->retrieveById($page->page_id);

            $result = '';

            foreach ($contents as $content) {

                if ($content->getType() != 'paragraph') {
                    continue;
                }

                $plainText = '';
                foreach ($content->getRawContent()['text'] as $item) {
                    $text = $item['plain_text'];
                    $href = $item['href'];

                    if ($href != null) {
                        $text = "[{$text}]({$href}) ";
                    }

                    $plainText .= $text;
                }

                $result .= $plainText;
            }

            $result = $this->cleanText($result);

            try {
                DB::transaction(function () use ($page, $result, $embedCollection) {
                    $textChunker = TextChunker::make($result);
                    $remainingText = $result;

                    info('Embedding Page ID: ' . $page->page_id);

                    while (!empty($remainingText)) {
                        $chunkedText = $textChunker->chunk();

                        info('Word Count: ' . str_word_count($chunkedText));

                        $embeddings = OpenAIAgent::make()->embeddings($chunkedText);

                        Embedding::query()->create([
                            'embed_collection_id' => $embedCollection,
                            'text' => $chunkedText,
                            'embedding' => json_encode($embeddings)
                        ]);

                        $remainingText = substr($remainingText, strlen($chunkedText));

                        $textChunker = TextChunker::make($remainingText);
                    }

                    info('Embedding Completed. Page ID: ' . $page->page_id);
                });
            } catch (\Exception $e) {
                error('Embedding failed: ' . $e->getMessage());
            }
        }
    }

    private function cleanText(string $text): string
    {
        $text = str_replace("\n", "", $text); // Remove new line
        $text = preg_replace('/\.+/', '.', $text); // Remove multiple dots
        $text = preg_replace('/\s+/', ' ', $text); // Remove multiple spaces

        return $text;
    }
}
