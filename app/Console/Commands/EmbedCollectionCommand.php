<?php

namespace App\Console\Commands;

use App\Models\EmbedCollection;
use App\Models\NotionIntegration;
use App\Models\User;
use Illuminate\Console\Command;
use FiveamCode\LaravelNotionApi\Notion;
use Illuminate\Support\Facades\Crypt;

use function Laravel\Prompts\{error, select, text, info};

class EmbedCollectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:embed-collection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will be used to create embed collection';

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

        $name = text(
            label: 'Provide Embed Collection Name',
            placeholder: 'E.g. My Collection',
            required: 'Embed collection name is required.'
        );

        EmbedCollection::query()->create([
            'user_id' => $user,
            'notion_integration_id' => $integration,
            'name' => $name
        ]);

        info('Embed collection created successfully.');
    }
}
