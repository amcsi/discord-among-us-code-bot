<?php

namespace App\Console\Commands;

use Discord\Discord;
use Illuminate\Console\Command;

class RunBotCommand extends Command
{
    protected $signature = 'bot:run';

    protected $description = 'Runs the bot';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $discord = new Discord([
            'token' => config('services.discord.authToken'),
        ]);

        $discord->on('ready', function ($discord) {
            echo "Bot is ready.", PHP_EOL;

            // Listen for events here
            $discord->on('message', function ($message) {
                echo "Received a message from {$message->author->username}: {$message->content}", PHP_EOL;
            });
        });

        $discord->run();

        return 0;
    }
}
