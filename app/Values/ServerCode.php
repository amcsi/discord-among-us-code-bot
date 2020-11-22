<?php
declare(strict_types=1);

namespace App\Values;

use Discord\Parts\Channel\Channel;

class ServerCode
{
    private Channel $voiceChannel;
    private string $code;

    public function __construct(Channel $voiceChannel, string $code)
    {
        $this->voiceChannel = $voiceChannel;
        $this->code = $code;
    }

    public function getVoiceChannel(): Channel
    {
        return $this->voiceChannel;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
