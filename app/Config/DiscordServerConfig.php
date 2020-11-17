<?php
declare(strict_types=1);

namespace App\Config;

class DiscordServerConfig
{
    private string $sourceChannelId;
    private string $targetChannelId;

    public function __construct(string $sourceChannelId, string $targetChannelId)
    {
        $this->sourceChannelId = $sourceChannelId;
        $this->targetChannelId = $targetChannelId;
    }

    public function getSourceChannelId(): string
    {
        return $this->sourceChannelId;
    }

    public function getTargetChannelId(): string
    {
        return $this->targetChannelId;
    }
}
