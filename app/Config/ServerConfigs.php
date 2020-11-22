<?php
declare(strict_types=1);

namespace App\Config;

use Illuminate\Support\Collection;

class ServerConfigs
{
    /** @var Collection|DiscordServerConfig[] */
    private $configsByServer;
    /** @var Collection|DiscordServerConfig[]|null */
    private $configsBySourceChannelId;

    public function __construct()
    {
        $this->configsByServer = collect([
            // Test server
            '774696290398765137' => new DiscordServerConfig('774696290398765140', '778324539771191347', ['/^Voice/']),
        ]);
    }

    /**
     * @return Collection|DiscordServerConfig[]
     */
    public function getServerConfigsBySourceChannelIds(): Collection
    {
        if (!$this->configsBySourceChannelId) {
            $this->configsBySourceChannelId = $this->configsByServer->keyBy(
                fn(DiscordServerConfig $config) => $config->getSourceChannelId()
            );
        }
        return $this->configsBySourceChannelId;
    }

    public function getConfigsByServerId()
    {
        return $this->configsByServer;
    }
}
