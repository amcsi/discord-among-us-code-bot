<?php
declare(strict_types=1);

namespace App\Services;

use Discord\Discord;
use Discord\Parts\Channel\Message;

class TargetMessageUpdater
{
    public function __construct(
        private Discord $discord,
        private TargetMessageByGuildFetcher $targetMessageByGuildFetcher,
        private PromiseFailHandler $promiseFailHandler
    )
    {}

    public function updateMessage(string $guildId, string $contents)
    {
        $guild = $this->discord->guilds->get('id', $guildId);
        $this->targetMessageByGuildFetcher->fetch($guild)->done(
            function (Message $message) use ($contents) {
                $message->content = trans($contents);
                $message->channel->messages->save($message)->then(null, $this->promiseFailHandler);
            },
            $this->promiseFailHandler
        );
    }
}
