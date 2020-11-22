<?php
declare(strict_types=1);

namespace App\Config;

class DiscordServerConfig
{
    private string $sourceChannelId;
    private string $targetChannelId;
    private array $gameVoiceRegexes;

    public function __construct(string $sourceChannelId, string $targetChannelId, array $gameVoiceRegexes)
    {
        $this->sourceChannelId = $sourceChannelId;
        $this->targetChannelId = $targetChannelId;
        $this->gameVoiceRegexes = $gameVoiceRegexes;
    }

    public function getSourceChannelId(): string
    {
        return $this->sourceChannelId;
    }

    public function getTargetChannelId(): string
    {
        return $this->targetChannelId;
    }

    public function getGameVoiceRegexes(): array
    {
        return $this->gameVoiceRegexes;
    }
}
