<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SignableHttpRequestTest extends TestCase
{
    #[DataProvider('dataConvertsToSignableData')]
    public function testConvertsToSignableData(string $method, string $uri, array $headers, string $body, string $expectedSignableData): void
    {
        self::assertSame($expectedSignableData, (new class (
            method: $method,
            uri: $uri,
            headers: $headers,
            body: $body,
        ) extends SignableHttpRequest {
        })->toSignableData());
    }

    public static function dataConvertsToSignableData(): array
    {
        return [
            'request' => ['POST', 'path?que=ry', ['he-ad' => 'er'], 'body', <<<EoS
POST path?que=ry HTTP/1.1\r
He-Ad: er\r
\r
body
EoS],
            'lower-cased method' => ['method', 'uri', ['he-ad' => 'er'], 'body', <<<EoS
METHOD uri HTTP/1.1\r
He-Ad: er\r
\r
body
EoS],
            'upper-cased header' => ['METHOD', 'uri', ['HE-AD' => 'ER'], 'body', <<<EoS
METHOD uri HTTP/1.1\r
He-Ad: ER\r
\r
body
EoS],
            'unsorted headers' => ['METHOD', 'uri', ['b' => 3, 'a' => 2, 'B' => 1], 'body', <<<EoS
METHOD uri HTTP/1.1\r
A: 2\r
B: 1,3\r
\r
body
EoS],
            'empty headers' => ['METHOD', 'uri', [], 'body', <<<EoS
METHOD uri HTTP/1.1\r
\r
body
EoS],
            'empty body' => ['METHOD', 'uri', ['he-ad' => 'er'], '', <<<EoS
METHOD uri HTTP/1.1\r
He-Ad: er\r
\r

EoS],
            'duplicate header' => ['METHOD', 'uri', ['he-ad' => 'er', 'HE-AD' => 'ER'], '', <<<EoS
METHOD uri HTTP/1.1\r
He-Ad: ER,er\r
\r

EoS],
        ];
    }
}
