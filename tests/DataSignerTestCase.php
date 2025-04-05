<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

abstract class DataSignerTestCase extends TestCase
{
    public const NOW = '2025-04-05T09:06:09+02:00';
    protected const DATA = 'data';
    protected const DOMAIN = 'domain';

    public function testWorksWithRawSignature(): void
    {
        $dataSigner = static::getDataSigner(self::getClock());
        $rawSignature = static::getRawSignatures()['data'];

        self::assertSame(base64_encode($rawSignature), base64_encode($dataSigner->sign(self::DATA)->toBinary()));
        self::assertTrue($dataSigner->verify(self::DATA, $rawSignature));
    }

    /**
     * @dataProvider dataSignsData
     */
    public function testSignsData(
        DataSigner $dataSigner,
        DateTimeInterface|null $expiresAt,
        Signature $expectedSignature,
    ): void {
        self::assertEquals(
            $expectedSignature,
            $dataSigner->sign(
                data: self::DATA,
                expiresAt: $expiresAt,
            ),
        );
    }

    public static function dataSignsData(): iterable
    {
        $dataSigner = static::getDataSigner(self::getClock());
        $rawSignatures = static::getRawSignatures();
        $makeSignature = static fn (string $rawSignature, DateTimeInterface|null $expiresAt): Signature => new Signature(
            rawSignature: $rawSignature,
            expiresAt: $expiresAt,
        );
        return [
            'data' => [
                $dataSigner,
                null,
                $makeSignature($rawSignatures['data'], null),
            ],
            'data + expiresAt' => [
                $dataSigner,
                self::getClock()->now(),
                $makeSignature($rawSignatures['data + expiresAt'], self::getClock()->now()),
            ],
            'domain + data' => [
                $dataSigner->withDomain(self::DOMAIN),
                null,
                $makeSignature($rawSignatures['domain + data'], null),
            ],
            'domain + data + expiresAt' => [
                $dataSigner->withDomain(self::DOMAIN),
                self::getClock()->now(),
                $makeSignature($rawSignatures['domain + data + expiresAt'], self::getClock()->now()),
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
        foreach (self::dataSignsData() as $case => $data) {
            yield $case => [$data[0], $data[2]];
        }
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
        $dataSigner = static::getDataSigner(self::getClock());
        return [
            'modified data' => [
                $dataSigner,
                $dataSigner->sign('signed data'),
            ],
            'modified signature' => [
                $dataSigner,
                new Signature(
                    rawSignature: 'modified signature',
                    expiresAt: null,
                ),
            ],
            'wrong domain' => [
                $dataSigner->withDomain('wrong domain'),
                $dataSigner->withDomain(self::DOMAIN)->sign(self::DATA),
            ],
            'expired signature' => [
                $dataSigner,
                $dataSigner->sign(self::DATA, self::getClock()->now()->modify('-1 second')),
            ],
        ];
    }

    protected static function getClock(): ClockInterface
    {
        return new class () implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable(DataSignerTestCase::NOW);
            }
        };
    }

    abstract protected static function getDataSigner(ClockInterface $clock): DataSigner;

    /**
     * @return array<non-empty-string, string>
     */
    abstract protected static function getRawSignatures(): iterable;
}
