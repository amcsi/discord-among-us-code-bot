<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\ServerConfigs;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Illuminate\Contracts\Cache\Repository;

class TargetChannelByGuildGetter
{
    private ServerConfigs $serverConfigs;
    private Repository $arrayCache;

    public function __construct(ServerConfigs $serverConfigs)
    {
        $this->serverConfigs = $serverConfigs;
        $this->arrayCache = cache()->driver('array');
    }

    public function get(Guild $guild): Channel
    {
        $guildId = $guild->id;
        $configsByGuildId = $this->serverConfigs->getConfigsByServerId();
        $targetChannelId = $configsByGuildId[$guildId]->getTargetChannelId();

        $targetChannelCacheKey = "target_channel_by_guild_id_$guildId";
        return $this->arrayCache->rememberForever(
            $targetChannelCacheKey,
            fn() => $guild->channels->get('id', $targetChannelId)
        );
    }
}
