<?php

namespace App\Console\Commands;

use App\Config\ServerConfigs;
use App\Services\TargetChannelByMessageGetter;
use App\Services\TargetMessageByGuildFetcher;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Illuminate\Console\Command;

class RunBotCommand extends Command
{
    protected $signature = 'bot:run';

    protected $description = 'Runs the bot';

    private ServerConfigs $serverConfigs;
    private $arrayCache;
    private Discord $discord;
    private TargetChannelByMessageGetter $targetChannelByMessageGetter;
    private TargetMessageByGuildFetcher $targetMessageByGuildFetcher;

    public function __construct(
        ServerConfigs $serverConfigs,
        Discord $discord,
        TargetChannelByMessageGetter $targetChannelByMessageGetter,
        TargetMessageByGuildFetcher $targetMessageByGuildFetcher
    ) {
        parent::__construct();
        $this->serverConfigs = $serverConfigs;
        $this->arrayCache = cache()->driver('array');
        $this->discord = $discord;
        $this->targetChannelByMessageGetter = $targetChannelByMessageGetter;
        $this->targetMessageByGuildFetcher = $targetMessageByGuildFetcher;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $discord = $this->discord;

        $discord->on('ready', function (Discord $discord) {
            echo "Bot is ready.", PHP_EOL;

            // Listen for events here
            $discord->on('message', function (Message $message) {
                $targetChannel = $this->targetChannelByMessageGetter->get($message);
                if (!$targetChannel) {
                    // Not among the source channels that should be checked for messages.
                    return null;
                }

                $targetMessagePromise = $this->targetMessageByGuildFetcher->fetch($targetChannel->guild);
                $targetMessagePromise->done(null, fn($failure) => $this->error('Could not save message: ' . $failure));

                echo "Received a message from {$message->author->username}: {$message->content}", PHP_EOL;

                return null;
            });
        });

        $discord->run();

        return 0;
    }
}
