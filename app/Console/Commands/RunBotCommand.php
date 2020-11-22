<?php

namespace App\Console\Commands;

use App\Config\ServerConfigs;
use App\Services\CodeHandler;
use App\Services\TargetChannelByMessageGetter;
use App\Services\TargetMessageByGuildFetcher;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;

class RunBotCommand extends Command
{
    protected $signature = 'bot:run';

    protected $description = 'Runs the bot';

    private ServerConfigs $serverConfigs;
    private Discord $discord;
    private TargetChannelByMessageGetter $targetChannelByMessageGetter;
    private TargetMessageByGuildFetcher $targetMessageByGuildFetcher;
    private CodeHandler $codeHandler;
    private $promiseFailHandler;

    public function __construct(
        ServerConfigs $serverConfigs,
        Discord $discord,
        TargetChannelByMessageGetter $targetChannelByMessageGetter,
        TargetMessageByGuildFetcher $targetMessageByGuildFetcher,
        CodeHandler $codeHandler,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->serverConfigs = $serverConfigs;
        $this->discord = $discord;
        $this->targetChannelByMessageGetter = $targetChannelByMessageGetter;
        $this->targetMessageByGuildFetcher = $targetMessageByGuildFetcher;
        $this->codeHandler = $codeHandler;
        $this->promiseFailHandler = function ($error) use ($logger) {
            $logger->error($error);
            $this->error($error);
        };
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

            foreach ($this->serverConfigs->getConfigsByServerId() as $guildId => $serverConfig) {
                $guild = $this->discord->guilds->get('id', $guildId);
                $this->targetMessageByGuildFetcher->fetch($guild)->done(
                    function (Message $message) {
                        $message->content = trans('bot.justConnected');
                        $message->channel->messages->save($message)->then(null, $this->promiseFailHandler);
                    },
                    $this->promiseFailHandler
                );
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
        });

        $discord->run();

        return 0;
    }
}
