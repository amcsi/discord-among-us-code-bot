<?php
declare(strict_types=1);

namespace App\Services;

use Psr\Log\LoggerInterface;

class PromiseFailHandler
{
    public function __construct(private LoggerInterface $logger) {}

    public function __invoke($error)
    {
        $this->logger->warning($error);
    }
}
