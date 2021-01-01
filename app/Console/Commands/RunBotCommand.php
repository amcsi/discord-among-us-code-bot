<?php

namespace App\Console\Commands;

use App\Config\ServerConfigs;
use App\Services\CodeHandler;
use App\Services\GuildBootHandler;
use App\Services\TargetMessageUpdater;
use App\Values\ServerCodes;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Illuminate\Console\Command;
use function React\Promise\all;

class RunBotCommand extends Command
{
    protected $signature = 'bot:run';

    protected $description = 'Runs the bot';

    public function __construct(
        private ServerConfigs $serverConfigs,
        private Discord $discord,
        private GuildBootHandler $guildBootHandler,
        private TargetMessageUpdater $targetMessageUpdater,
        private CodeHandler $codeHandler,
        private ServerCodes $serverCodes,
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

            // Send welcome messages, replay message history.
            $this->guildBootHandler->bootGuilds();

            // Listen for events here
            $discord->on('message', function (Message $message) {
                $this->codeHandler->handle($message);

                echo "Received a message from {$message->author->username}: {$message->content}", PHP_EOL;
            });

            $discord->on(Event::MESSAGE_UPDATE, function (Message $message) {
                if (!$this->serverCodes->hasMessageServerCode($message->id)) {
                    // This message is not among the sources of the server codes. Ignore it.
                    return;
                }

                $this->codeHandler->handle($message);

                echo "Received an update to a message from {$message->author->username}: {$message->content}", PHP_EOL;
            });

            $discord->on(Event::MESSAGE_DELETE, function ($message) {
                if (!$this->serverCodes->hasMessageServerCode($message->id)) {
                    // This message is not among the sources of the server codes. Ignore it.
                    return;
                }

                $this->codeHandler->handleDeleteBySourceMessageId($message->id);

                echo 'Deleting a message.', PHP_EOL;
            });

            $loop = $discord->getLoop();

            // Periodically check the voice rooms with codes. If any ran out of members, then unset the code.
            $loop->addPeriodicTimer(config('project.voiceTimeout'), function () {
                foreach ($this->serverCodes as $serverCode) {
                    if (!count($serverCode->voiceChannel->members)) {
                        $this->info("Unsetting the code for {$serverCode->voiceChannel->name} due to empty voice channel.");
                        $this->codeHandler->handleDeleteBySourceMessageId($serverCode->sourceMessage->id);
                    }
                }
            });

            $disconnectHandler = function () use ($configsByServerId) {
                $promises = [];
                $this->info('A termination signal has been received.');
                $this->info('Updating the message in each guild to indicate that the bot is offline.');
                foreach ($configsByServerId as $guildId => $serverConfig) {
                    $promises[] = $this->targetMessageUpdater->updateMessage($guildId, trans('bot.disconnected'));
                }

                // Once the "disconnected" message has been sent to all servers, terminate the application.
                all($promises)->always(function () {
                    $this->discord->close(true);
                });
            };
            $loop->addSignal(SIGINT, $disconnectHandler);
            $loop->addSignal(SIGTERM, $disconnectHandler);
        });

        $discord->run();

        return 0;
    }
}
