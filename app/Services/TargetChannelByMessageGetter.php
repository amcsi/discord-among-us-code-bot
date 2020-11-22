<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\ServerConfigs;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Illuminate\Contracts\Cache\Repository;

/**
 * Gets the target channel (where codes should go) based on an incoming message.
 * The message has to have come from a designated source channel to count.
 */
class TargetChannelByMessageGetter
{
    private Discord $discord;
    private ServerConfigs $serverConfigs;
    private Repository $arrayCache;

    public function __construct(Discord $discord, ServerConfigs $serverConfigs)
    {
        $this->discord = $discord;
        $this->serverConfigs = $serverConfigs;
        $this->arrayCache = cache()->driver('array');
    }

    public function get(Message $message): ?Channel
    {
        $serverConfigBySourceChannelIds = $this->serverConfigs->getServerConfigsBySourceChannelIds();
        if (!isset($serverConfigBySourceChannelIds[$message->channel->id])) {
            // Not among the source channels that should be checked for messages.
            return null;
        }
        $targetChannelId = $serverConfigBySourceChannelIds[$message->channel->id]->getTargetChannelId();

        /** @var Channel $targetChannel */
        $targetChannel = $this->arrayCache->rememberForever(
            "channel_by_id_$targetChannelId",
            fn() => $this->discord->getChannel($targetChannelId)
        );
        return $targetChannel;
    }
}
