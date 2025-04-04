<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use PHPUnit\Framework\TestCase;

abstract class DataSignerTestCase extends TestCase
{
    protected const DATA = 'data';

    public function testWorksWithRawSignature(): void
    {
        $dataSigner = static::getDataSigner();
        $rawSignature = static::getRawSignatures()['data'];

        self::assertSame(base64_encode($rawSignature), base64_encode($dataSigner->sign(self::DATA)->toBinary()));
        self::assertTrue($dataSigner->verify(self::DATA, $rawSignature));
    }

    /**
     * @dataProvider dataSignsData
     */
    public function testSignsData(
        DataSigner $dataSigner,
        Signature $expectedSignature,
    ): void {
        self::assertEquals(
            $expectedSignature,
            $dataSigner->sign(
                data: self::DATA,
            ),
        );
    }

    public static function dataSignsData(): iterable
    {
        $dataSigner = static::getDataSigner();
        $rawSignatures = static::getRawSignatures();
        $makeSignature = static fn (string $rawSignature): Signature => new Signature(
            rawSignature: $rawSignature,
        );
        return [
            'data' => [
                $dataSigner,
                $makeSignature($rawSignatures['data']),
            ],
        ];
    }

    /**
     * @dataProvider dataVerifiesDataBySignature
     */
    public function testVerifiesDataBySignature(
        DataSigner $dataSigner,
        Signature $signature,
    ): void {
        self::assertTrue($dataSigner->verify(
            data: self::DATA,
            signature: $signature,
        ));
    }

    public static function dataVerifiesDataBySignature(): iterable
    {
        return static::dataSignsData();
    }

    /**
     * @dataProvider dataDoesNotVerifyDataBySignature
     */
    public function testDoesNotVerifyDataBySignature(
        DataSigner $dataSigner,
        Signature $signature,
    ): void {
        self::assertFalse($dataSigner->verify(
            data: self::DATA,
            signature: $signature,
        ));
    }

    public static function dataDoesNotVerifyDataBySignature(): iterable
    {
        $dataSigner = static::getDataSigner();
        return [
            'modified data' => [
                $dataSigner,
                $dataSigner->sign('signed data'),
            ],
            'modified signature' => [
                $dataSigner,
                new Signature(
                    rawSignature: 'modified signature',
                ),
            ],
        ];
    }

    abstract protected static function getDataSigner(): DataSigner;

    /**
     * @return array<non-empty-string, string>
     */
    abstract protected static function getRawSignatures(): iterable;
}
