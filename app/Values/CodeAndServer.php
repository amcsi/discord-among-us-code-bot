<?php
declare(strict_types=1);

namespace App\Values;

use JetBrains\PhpStorm\Immutable;

/**
 * Has an Among Us code and a continent server.
 */
#[Immutable]
class CodeAndServer
{
    public function __construct(
        public string $code,
        public string|null $server,
    ) {}
}
