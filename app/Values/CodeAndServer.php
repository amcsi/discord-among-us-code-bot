<?php
declare(strict_types=1);

namespace App\Values;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class CodeAndServer
{
    public function __construct(
        public string $code,
        public string|null $server,
    ) {}
}
