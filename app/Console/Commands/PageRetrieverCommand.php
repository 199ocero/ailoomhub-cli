<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\NotionPage;
use App\Models\EmbedCollection;
use App\Services\PageRetriever;
use Illuminate\Console\Command;
use App\Models\NotionIntegration;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\{select, error, progress, info};

class PageRetrieverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:page-retriever';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will be used to retrieve page links';

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

        $userNotionIntegration = NotionIntegration::query()->where('user_id', $user)->first();

        if ($userNotionIntegration == null) {
            error('Notion integration not found. Please create an integration first.');
            error('Command: php artisan make:notion');
            return;
        }

        $embedCollections = EmbedCollection::query()->where('notion_integration_id', $userNotionIntegration->id)->get();

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

        $notionIntegration = EmbedCollection::query()
            ->with('notionIntegration')
            ->where('id', $embedCollection)
            ->first();

        $pages = PageRetriever::make($notionIntegration->notionIntegration->token)->retrieve();

        if ($pages->count() == 0) {
            error('It seems like you didn\'t connect any pages to your integration. See the Notion documentation for more details.');
            return;
        }

        $progress = progress(label: 'Retrieving pages from Notion', steps: $pages->count());

        $progress->start();

        try {
            DB::transaction(function () use ($pages, $embedCollection, $progress) {
                foreach ($pages as $page) {
                    NotionPage::updateOrCreate(
                        [
                            'page_id' => $page->getId(),
                            'embed_collection_id' => $embedCollection,
                        ]
                    );

                    $progress->advance();
                }
            });
        } catch (\Exception $e) {
            error("Transaction failed: " . $e->getMessage());
        }

        $progress->finish();

        info('Pages retrieved successfully.');
    }
}
