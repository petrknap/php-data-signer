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
                withData: $withData,
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
                    signedData: OptionalString::empty(),
                ),
                false,
                'rawSignature',
            ],
            'rawSignature + expiresAt' => [
                new Signature(
                    rawSignature: 'rawSignature',
                    expiresAt: new DateTimeImmutable('2024-04-05T09:26:54+02:00'),
                    signedData: OptionalString::empty(),
                ),
                false,
                'a:2:{i:0;s:12:"rawSignature";i:1;i:1712302014;}',
            ],
            'rawSignature + expiresAt + signedData' => [
                new Signature(
                    rawSignature: 'rawSignature',
                    expiresAt: new DateTimeImmutable('2024-04-05T09:26:54+02:00'),
                    signedData: OptionalString::of('signedData'),
                ),
                true,
                'a:3:{i:0;s:12:"rawSignature";i:1;i:1712302014;i:2;s:10:"signedData";}',
            ],
            'rawSignature + signedData' => [
                new Signature(
                    rawSignature: 'rawSignature',
                    expiresAt: null,
                    signedData: OptionalString::of('signedData'),
                ),
                true,
                'a:2:{i:0;s:12:"rawSignature";i:2;s:10:"signedData";}',
            ],
        ];
    }

    /**
     * @dataProvider dataEncodesItself
     */
    public function testEncodesItself(bool $withData): void
    {
        $signature = new Signature(
            rawSignature: 'rawSignature',
            expiresAt: new DateTimeImmutable(),
            signedData: OptionalString::of('signedData'),
        );
        self::assertEquals(
            expected: $signature->toBinary(withData: $withData),
            actual: $signature->encode(withData: $withData)->getData(),
        );
    }

    public static function dataEncodesItself(): iterable
    {
        return [
            'without data' => [false],
            'with data' => [true],
        ];
    }
}
