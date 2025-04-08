<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use PetrKnap\Shorts\PhpUnit\MarkdownFileTestInterface;
use PetrKnap\Shorts\PhpUnit\MarkdownFileTestTrait;
use PHPUnit\Framework\TestCase;

final class ReadmeTest extends TestCase implements MarkdownFileTestInterface
{
    use MarkdownFileTestTrait;

    public static function getPathToMarkdownFile(): string
    {
        return __DIR__ . '/../README.md';
    }

    public static function getExpectedOutputsOfPhpExamples(): iterable
    {
        return [
            'usage' => 'Data was successfully verified by signature.',
            'domain-specific-signing' => 'You can not use signature generated for `password_reset` in `cookies`.',
            'time-limited-signing' => 'You can not use signature after its expiration.',
            'signable-data-transfer-object' => '{"property":"some value","signature":"c29tZSB2YWx1ZQ=="}',
        ];
    }
}
