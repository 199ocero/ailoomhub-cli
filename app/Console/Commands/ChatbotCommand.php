<?php

namespace App\Console\Commands;

use App\Models\User;

use App\Models\EmbedCollection;
use App\Models\Embedding;
use Illuminate\Console\Command;
use App\Models\NotionIntegration;
use App\Services\OpenAIAgent;

use function Laravel\Prompts\{error, select, text, info, spin};

class ChatbotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ask:chatbot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will be used to ask questions to chatbot';

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

        $embedCollectionIntegration = EmbedCollection::query()->where('id', $embedCollection)->first();

        $question = text(
            label: 'Ask me a Question',
            required: 'Question is required.',
        );

        $questionEmbedding = spin(
            fn () => OpenAIAgent::make()->embeddings($question),
            'Fetching embedding for question...'
        );

        $vector = json_encode($questionEmbedding);

        $result = spin(
            fn () => Embedding::query()
                ->select("text")
                ->selectSub("embedding <=> '{$vector}'::vector", "distance")
                ->where('embed_collection_id', $embedCollectionIntegration->id)
                ->limit(1)
                ->orderBy('distance', 'asc')
                ->get(),
            'Finding similar embeddings...'
        );

        $threshold = 0.2;

        $context = $result->filter(function ($item) use ($threshold) {
            return (float)$item->distance >= $threshold;
        })->pluck('text')->toArray();

        if (count($context) == 0) {
            $context = "No context found";
        } else {
            $context = implode(" ", $context);
        }

        $response = spin(
            fn () => OpenAIAgent::make()->askQuestion($context, $question, 200),
            'Answering question...'
        );

        info("Answer: \n" . $response);
    }
}
