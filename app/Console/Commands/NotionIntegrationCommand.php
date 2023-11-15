<?php

namespace App\Console\Commands;

use App\Models\NotionIntegration;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use function Laravel\Prompts\{password, text, select, error, info};

class NotionIntegrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:notion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will be used to create notion token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()->get()->count();

        if ($users == 0) {
            error('No users found. Please create a user first.');
            error('Command: php artisan make:user');
            return;
        }

        $user = select(
            label: 'Select a user',
            options: User::query()->pluck('name', 'id'),
            required: 'User is required.',
            scroll: 10
        );

        $name = text(
            label: 'Provide Notion integration name',
            placeholder: 'E.g. AILoomHub',
            required: 'Integration name is required.',
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 255 => 'The name must not exceed 255 characters.',
                default => null
            },
            hint: 'We suggest to use the name of integration that you created on Notion.'
        );

        $notionSecret = password(
            label: 'Provide Notion Secret',
            placeholder: 'E.g. secret_eIp...',
            required: 'Notion Secret is required',
            hint: 'We need this to access the Notion API.'
        );

        NotionIntegration::create([
            'user_id' => $user,
            'name' => $name,
            'token' => Crypt::encryptString($notionSecret),
        ]);

        info('Notion created successfully.');
    }
}
