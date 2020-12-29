<?php

namespace App\Console\Commands;

use App\Config\ServerConfigs;
use App\Services\CodeHandler;
use App\Services\PromiseFailHandler;
use App\Services\TargetChannelByMessageGetter;
use App\Services\TargetMessageByGuildFetcher;
use App\Services\TargetMessageUpdater;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Illuminate\Console\Command;

class RunBotCommand extends Command
{
    protected $signature = 'bot:run';

    protected $description = 'Runs the bot';

    public function __construct(
        private ServerConfigs $serverConfigs,
        private Discord $discord,
        private TargetChannelByMessageGetter $targetChannelByMessageGetter,
        private TargetMessageByGuildFetcher $targetMessageByGuildFetcher,
        private TargetMessageUpdater $targetMessageUpdater,
        private CodeHandler $codeHandler,
        private PromiseFailHandler $promiseFailHandler,
    ) {
        parent::__construct();
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

            $configsByServerId = $this->serverConfigs->getConfigsByServerId();
            foreach ($configsByServerId as $guildId => $serverConfig) {
                $this->targetMessageUpdater->updateMessage($guildId, trans('bot.justConnected'));
            }

            // Listen for events here
            $discord->on('message', function (Message $message) {
                $targetChannel = $this->targetChannelByMessageGetter->get($message);
                if (!$targetChannel) {
                    // Not among the source channels that should be checked for messages.
                    return null;
                }

                $targetMessagePromise = $this->targetMessageByGuildFetcher->fetch($targetChannel->guild);
                $targetMessagePromise->done(function (Message $targetMessage) use ($message) {
                    $this->codeHandler->handle($message, $targetMessage)->then(null, $this->promiseFailHandler);
                }, fn($failure) => $this->error('Could not save message: ' . $failure));

                echo "Received a message from {$message->author->username}: {$message->content}", PHP_EOL;

                return null;
            });

            $loop = $discord->getLoop();
            $disconnectHandler = function () use ($configsByServerId) {
                foreach ($configsByServerId as $guildId => $serverConfig) {
                    $this->targetMessageUpdater->updateMessage($guildId, trans('bot.disconnected'));
                }
            };
            $loop->addSignal(SIGINT, $disconnectHandler);
            $loop->addSignal(SIGTERM, $disconnectHandler);
        });

        $discord->run();

        return 0;
    }
}
