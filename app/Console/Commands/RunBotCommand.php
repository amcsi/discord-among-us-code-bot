<?php

namespace App\Console\Commands;

use App\Config\ServerConfigs;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Illuminate\Console\Command;
use React\Promise\Deferred;

class RunBotCommand extends Command
{
    protected $signature = 'bot:run';

    protected $description = 'Runs the bot';

    private ServerConfigs $serverConfigs;
    private $arrayCache;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ServerConfigs $serverConfigs)
    {
        parent::__construct();
        $this->serverConfigs = $serverConfigs;
        $this->arrayCache = cache()->driver('array');
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

        $serverConfigBySourceChannelIds = $this->serverConfigs->getServerConfigsBySourceChannelIds();
        $configsByServerId = $this->serverConfigs->getConfigsByServerId();

        $discord->on('ready', function (Discord $discord) use ($serverConfigBySourceChannelIds, $configsByServerId) {
            echo "Bot is ready.", PHP_EOL;

            // Listen for events here
            $discord->on('message', function (Message $message) use ($discord, $serverConfigBySourceChannelIds, $configsByServerId) {
                if (!isset($serverConfigBySourceChannelIds[$message->channel->id])) {
                    // Not among the source channels that should be checked for messages.
                    return null;
                }
                $guild = $message->channel->guild;
                $guildId = $guild->id;
                $targetChannelId = $configsByServerId[$guildId]->getTargetChannelId();

                $onHasTargetMessageDeferred = new Deferred();
                $onHasTargetMessagePromise = $onHasTargetMessageDeferred->promise();

                $onHasTargetMessagePromise->done(function (Message $message) {
                    dump($message);
                });
                /** @var Channel $targetChannel */
                $targetChannel = $this->arrayCache->rememberForever("channel_by_id_$targetChannelId", function () use (
                    $discord,
                    $targetChannelId
                ) {
                    return $discord->getChannel($targetChannelId);
                });

                $targetMessageCacheKey = "target_message_by_guild_id_$guildId";
                $targetMessage = $this->arrayCache->get($targetMessageCacheKey);
                if ($targetMessage) {
                    $onHasTargetMessageDeferred->resolve($targetMessage);
                } else {
                    $targetChannel->getMessageHistory(['limit' => 10])->done(function (Collection $messages) use (
                        $targetMessageCacheKey,
                        $targetChannel,
                        $onHasTargetMessageDeferred,
                        $discord
                    ) {
                        $targetMessage = null;
                        /** @var Message[] $messages */
                        foreach ($messages as $message) {
                            // Is this message made by this bot?
                            if ($message->author->id === $discord->user->id) {
                                // Good, take that message.
                                $targetMessage = $message;
                                break;
                            }
                        }
                        if ($targetMessage) {
                            $this->arrayCache->forever($targetMessageCacheKey, $targetMessage);
                            // Found a message.
                            $onHasTargetMessageDeferred->resolve($targetMessage);
                        } else {
                            // No message yet? Then create one.
                            $targetChannel->sendMessage('This is my message :)')->done(
                                function (Message $newMessage) use (
                                    $targetMessageCacheKey,
                                    $onHasTargetMessageDeferred
                                ) {
                                    $this->arrayCache->forever($targetMessageCacheKey, $newMessage);
                                    $onHasTargetMessageDeferred->resolve($newMessage);
                                },
                                function ($failure) {
                                    $this->error('Could not save message: ' . $failure);
                                }
                            );
                        }
                    });
                    return $targetMessage;
                }

                echo "Received a message from {$message->author->username}: {$message->content}", PHP_EOL;

                return null;
            });
        });

        $discord->run();

        return 0;
    }
}
