<?php
declare(strict_types=1);

namespace App\Values;

use Discord\Parts\Channel\Channel;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
class ServerCode
{
    public function __construct(public Channel $voiceChannel, public string $code) {}
}
