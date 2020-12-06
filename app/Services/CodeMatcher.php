<?php
declare(strict_types=1);

namespace App\Services;

use App\Values\CodeAndServer;

/**
 * Reads chat message texts and tries to extract the Among Us code and server.
 */
class CodeMatcher
{
    public static function matchText(string $messageText): ?CodeAndServer
    {
        $match = preg_match('/\b((?!SERVER)[A-Z]{6})\b/', $messageText, $matches);

        if (!$match) {
            return null;
        }

        $code = $matches[1];

        $server = preg_match('/\b(NA|EU)\b/', $messageText, $matches) ? $matches[1] : null;

        return new CodeAndServer($code, $server);
    }

    public static function matchAndFormatText(string $messageText): string
    {
        $codeAndServer = self::matchText($messageText);
        if (!$codeAndServer) {
            return '';
        }

        $components = [$codeAndServer->code];
        if ($codeAndServer->server) {
            $components[] = "({$codeAndServer->server})";
        }

        return implode(' ', $components);
    }
}
