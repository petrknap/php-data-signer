<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SignatureTest extends TestCase
{
    /**
     * @dataProvider dataConvertsItself
     */
    public function testConvertsItselfToBinary(Signature $signature, string $expectedBinary): void
    {
        self::assertEquals(
            $expectedBinary,
            $signature->toBinary(),
        );
    }

    /**
     * @dataProvider dataConvertsItself
     */
    public function testConvertsItselfFromBinary(Signature $expectedSignature, string $binary): void
    {
        self::assertEquals(
            $expectedSignature,
            Signature::fromBinary($binary),
        );
    }

    public static function dataConvertsItself(): iterable
    {
        return [
            'rawSignature' => [
                new Signature(
                    rawSignature: 'rawSignature',
                    expiresAt: null,
                ),
                'rawSignature',
            ],
            'rawSignature + expiresAt' => [
                new Signature(
                    rawSignature: 'rawSignature',
                    expiresAt: new DateTimeImmutable('2024-04-05T09:26:54+02:00'),
                ),
                'a:2:{i:0;s:12:"rawSignature";i:1;i:1712302014;}',
            ],
        ];
    }

    public function testEncodesItself(): void
    {
        $signature = new Signature(
            rawSignature: 'rawSignature',
            expiresAt: new DateTimeImmutable(),
        );
        self::assertEquals(
            expected: $signature->toBinary(),
            actual: $signature->encode()->getData(),
        );
    }
}
