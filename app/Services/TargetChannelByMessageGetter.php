<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\ServerConfigs;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Illuminate\Contracts\Cache\Repository;

/**
 * Gets the target channel (where codes should go) based on an incoming message.
 * The message has to have come from a designated source channel to count.
 */
class TargetChannelByMessageGetter
{
    private Repository $arrayCache;

    public function __construct(
        private ServerConfigs $serverConfigs,
        private TargetChannelByGuildGetter $targetChannelByGuildGetter,
    ) {
        $this->arrayCache = cache()->driver('array');
    }

    public function get(Message $message): ?Channel
    {
        $serverConfigBySourceChannelIds = $this->serverConfigs->getServerConfigsBySourceChannelIds();
        if (!isset($serverConfigBySourceChannelIds[$message->channel->id])) {
            // Not among the source channels that should be checked for messages.
            return null;
        }

        return $this->targetChannelByGuildGetter->get($message->channel->guild);
    }
}
