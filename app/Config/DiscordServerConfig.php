<?php
declare(strict_types=1);

namespace App\Config;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class DiscordServerConfig
{
    public function __construct(
        public string $sourceChannelId,
        public string $targetChannelId,
        public array $gameVoiceRegexes
    ) {}
}
