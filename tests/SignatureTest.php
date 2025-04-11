<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use DateTimeImmutable;
use PetrKnap\Optional\OptionalString;
use PHPUnit\Framework\TestCase;

final class SignatureTest extends TestCase
{
    /**
     * @dataProvider dataConvertsItself
     */
    public function testConvertsItselfToBinary(Signature $signature, bool $withData, string $expectedBinary): void
    {
        self::assertEquals(
            $expectedBinary,
            $signature->toBinary(
                withOriginalData: $withData,
            ),
        );
    }

    /**
     * @dataProvider dataConvertsItself
     */
    public function testConvertsItselfFromBinary(Signature $expectedSignature, bool $_, string $binary): void
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
                    originalData: OptionalString::empty(),
                ),
                false,
                'rawSignature',
            ],
            'rawSignature + expiresAt' => [
                new Signature(
                    rawSignature: 'rawSignature',
                    expiresAt: new DateTimeImmutable('2024-04-05T09:26:54+02:00'),
                    originalData: OptionalString::empty(),
                ),
                false,
                'a:2:{i:0;s:12:"rawSignature";i:1;i:1712302014;}',
            ],
            'rawSignature + expiresAt + originalData' => [
                new Signature(
                    rawSignature: 'rawSignature',
                    expiresAt: new DateTimeImmutable('2024-04-05T09:26:54+02:00'),
                    originalData: OptionalString::of('originalData'),
                ),
                true,
                'a:3:{i:0;s:12:"rawSignature";i:1;i:1712302014;i:2;s:12:"originalData";}',
            ],
            'rawSignature + originalData' => [
                new Signature(
                    rawSignature: 'rawSignature',
                    expiresAt: null,
                    originalData: OptionalString::of('originalData'),
                ),
                true,
                'a:2:{i:0;s:12:"rawSignature";i:2;s:12:"originalData";}',
            ],
        ];
    }

    /**
     * @dataProvider dataEncodesItself
     */
    public function testEncodesItself(bool $withOriginalData): void
    {
        $signature = new Signature(
            rawSignature: 'rawSignature',
            expiresAt: new DateTimeImmutable(),
            originalData: OptionalString::of('originalData'),
        );
        self::assertEquals(
            expected: $signature->toBinary(withOriginalData: $withOriginalData),
            actual: $signature->encode(withOriginalData: $withOriginalData)->getData(),
        );
    }

    public static function dataEncodesItself(): iterable
    {
        return [
            'without original data' => [false],
            'with original data' => [true],
        ];
    }
}
