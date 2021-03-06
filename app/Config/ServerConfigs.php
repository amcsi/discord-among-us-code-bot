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
            // Among Us - Hungary
            '746048273990549576' => new DiscordServerConfig('746048275336921172', '?', [
                '/\bThe Skeld\b/',
                '/\bMira HQ\b/',
                '/\bPolus\b/',
                '/^Fun Lobby\b/',
                '/^Proximity Lobby\b/',
                '/^Duo\b/',
                '/^Trio\b/',
                '/^Squad\b/',
            ]),
            // Among Us Magyarország / Hungary (Drelaky)
            '752281132195905649' => new DiscordServerConfig('755519615131582464', '?', [
                '/\bJátékterem\b/',
                '/\bH&S\b/',
            ]),
        ]);
    }

    /**
     * @return Collection|DiscordServerConfig[]
     */
    public function getServerConfigsBySourceChannelIds(): Collection
    {
        if (!$this->configsBySourceChannelId) {
            $this->configsBySourceChannelId = $this->configsByServer->keyBy(
                fn(DiscordServerConfig $config) => $config->sourceChannelId
            );
        }
        return $this->configsBySourceChannelId;
    }

    public function getConfigsByServerId()
    {
        return $this->configsByServer;
    }
}
