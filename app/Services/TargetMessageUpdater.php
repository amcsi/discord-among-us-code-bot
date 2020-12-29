<?php
declare(strict_types=1);

namespace App\Services;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use React\Promise\ExtendedPromiseInterface;

class TargetMessageUpdater
{
    public function __construct(
        private Discord $discord,
        private TargetMessageByGuildFetcher $targetMessageByGuildFetcher,
        private PromiseFailHandler $promiseFailHandler
    )
    {}

    public function updateMessage(string $guildId, string $contents): ExtendedPromiseInterface
    {
        $guild = $this->discord->guilds->get('id', $guildId);
        return $this->targetMessageByGuildFetcher->fetch($guild)->then(
            function (Message $message) use ($contents) {
                $message->content = trans($contents);
                return $message->channel->messages->save($message)->then(null, $this->promiseFailHandler);
            },
            $this->promiseFailHandler
        );
    }
}
