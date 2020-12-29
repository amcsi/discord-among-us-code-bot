<?php
declare(strict_types=1);

namespace App\Message;

use Discord\Parts\Channel\Channel;
use JetBrains\PhpStorm\Immutable;

/**
 * A Discord message that is deleted.
 */
#[Immutable]
class DeletedMessage
{
    public function __construct(public string $id, public Channel $channel) {}
}
