<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use DateTimeImmutable;
use DateTimeInterface;
use PetrKnap\Optional\OptionalString;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

abstract class DataSignerTestCase extends TestCase
{
    public const NOW = '2025-04-05T09:06:09+02:00';
    public const DATA = 'data';
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
        SignableDataInterface|string $data,
        DateTimeInterface|null $expiresAt,
        Signature $expectedSignature,
    ): void {
        self::assertEquals(
            $expectedSignature,
            $dataSigner->sign(
                data: $data,
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
            signedData: OptionalString::of(self::DATA),
        );
        $cases = [
            'data' => [
                $dataSigner,
                self::DATA,
                null,
            ],
            'data + domain' => [
                $dataSigner->withDomain(self::DOMAIN),
                self::DATA,
                null,
            ],
            'data + domain + expiresAt' => [
                $dataSigner->withDomain(self::DOMAIN),
                self::DATA,
                self::getClock()->now(),
            ],
            'data + expiresAt' => [
                $dataSigner,
                self::DATA,
                self::getClock()->now(),
            ],
        ];
        foreach ($cases as $case => $arguments) {
            $arguments[3] = $makeSignature($rawSignatures[$case], $arguments[2]);
            yield $case => $arguments;
            $arguments[1] = new Some\DataTransferObject($arguments[1]);
            yield str_replace('data', 'data transfer object', $case) => $arguments;
        }
    }

    /**
     * @dataProvider dataVerifiesDataBySignature
     */
    public function testVerifiesDataBySignature(
        DataSigner $dataSigner,
        SignableDataInterface|string $data,
        Signature $signature,
    ): void {
        self::assertTrue($dataSigner->verify(
            data: $data,
            signature: $signature,
        ));
    }

    public static function dataVerifiesDataBySignature(): iterable
    {
        foreach (self::dataSignsData() as $case => $data) {
            yield $case => [$data[0], $data[1], $data[3]];
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
                    signedData: OptionalString::empty(),
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
