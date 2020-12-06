<?php
declare(strict_types=1);

namespace Tests\Services;

use App\Services\CodeMatcher;
use PHPUnit\Framework\TestCase;

class CodeMatcherTest extends TestCase
{
    /**
     * @dataProvider provideNoMatches
     */
    public function testNoMatch($input)
    {
        self::assertNull(CodeMatcher::matchText($input));
    }

    public function provideNoMatches()
    {
        return [
            'empty string' => [''],
            'lowercase' => ['asdfgh'],
            'not enough letters' => ['ASDFG'],
            'too many letters' => ['ASDFGHJ'],
            'SERVER' => ['SERVER'],
        ];
    }

    /**
     * @dataProvider provideMatches
     */
    public function testMatch(string $expectedCode, string|null $expectedServer, string $input)
    {
        $result = CodeMatcher::matchText($input);
        self::assertSame($expectedCode, $result->code);
        self::assertSame($expectedServer, $result->server);
    }

    public function provideMatches()
    {
        return [
            'code' => ['ABCDEF', null, 'ABCDEF'],
            'two codes' => ['ABCDEF', null, 'ABCDEF GHIJKL'],
            'server name' => ['ABCDEF', 'NA', 'ABCDEF NA'],
            'eu' => ['ABCDEF', 'EU', 'ABCDEF EU'],
            'server name appearing before code' => ['ABCDEF', 'NA', 'NA ABCDEF'],
            'newlines' => ['ABCDEF', 'NA', "NA\nABCDEF"],
            'compound' => ['ABCDEF', 'NA', "18+Játékterem 1\nABCDEF\nSERVER:NA"],
        ];
    }
}
