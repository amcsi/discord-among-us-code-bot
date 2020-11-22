<?php
declare(strict_types=1);

namespace App\Services;

use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Illuminate\Contracts\Cache\Repository;
use React\Promise\Deferred;
use React\Promise\Promise;

class TargetMessageByGuildFetcher
{
    private Discord $discord;
    private Repository $arrayCache;
    private TargetChannelByGuildGetter $targetChannelByGuildGetter;

    public function __construct(Discord $discord, TargetChannelByGuildGetter $targetChannelByGuildGetter)
    {
        $this->discord = $discord;
        $this->arrayCache = cache()->driver('array');
        $this->targetChannelByGuildGetter = $targetChannelByGuildGetter;
    }

    public function fetch(Guild $guild): Promise
    {
        $targetChannel = $this->targetChannelByGuildGetter->get($guild);
        $guildId = $guild->id;
        $onHasTargetMessageDeferred = new Deferred();
        $onHasTargetMessagePromise = $onHasTargetMessageDeferred->promise();

        $targetMessageCacheKey = "target_message_by_guild_id_$guildId";
        $targetMessage = $this->arrayCache->get($targetMessageCacheKey);
        if ($targetMessage) {
            $onHasTargetMessageDeferred->resolve($targetMessage);
        } else {
            $targetChannel->getMessageHistory(['limit' => 10])->done(function (Collection $messages) use (
                $targetMessageCacheKey,
                $targetChannel,
                $onHasTargetMessageDeferred
            ) {
                $targetMessage = null;
                /** @var Message[] $messages */
                foreach ($messages as $message) {
                    // Is this message made by this bot?
                    if ($message->author->id === $this->discord->user->id) {
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
                    $targetChannel->sendMessage(trans('bot.startingMessage'))->done(
                        function (Message $newMessage) use (
                            $targetMessageCacheKey,
                            $onHasTargetMessageDeferred
                        ) {
                            $this->arrayCache->forever($targetMessageCacheKey, $newMessage);
                            $onHasTargetMessageDeferred->resolve($newMessage);
                        },
                        function ($failure) use ($onHasTargetMessageDeferred) {
                            $onHasTargetMessageDeferred->reject($failure);
                        }
                    );
                }
            });
        }

        return $onHasTargetMessagePromise;
    }
}
