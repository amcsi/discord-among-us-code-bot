<?php
declare(strict_types=1);

namespace App\Values;

use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use JetBrains\PhpStorm\Immutable;

/**
 * Holds the source message, voice channel, code, and continent server as the code belonging to the voice channel.
 */
#[Immutable]
class ServerCode
{
    public function __construct(
        public Message $sourceMessage,
        public Channel $voiceChannel,
        public CodeAndServer $codeAndServer
    ) {}
}
