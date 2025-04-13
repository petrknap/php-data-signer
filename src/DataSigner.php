<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use DateTimeImmutable;
use DateTimeInterface;
use PetrKnap\Optional\OptionalString;
use Psr\Clock\ClockInterface;

/**
 * @note Use {@see DataSignerInterface} if you need strong cross-platform compatibility.
 * @note Uses {@see self::FILE_SEPARATOR} to separate data and metadata.
 */
abstract class DataSigner implements DataSignerInterface
{
    /**
     * @todo remove support for binary 4 and use Ascii::FileSeparator
     *
     * @internal public for test purposes only
     */
    public const FILE_SEPARATOR = "\x1C";
    /**
     * @todo remove support for binary 4 and use Ascii::UnitSeparator
     *
     * @internal public for test purposes only
     */
    public const UNIT_SEPARATOR = "\x1F";

    protected readonly ClockInterface $clock;

    public function __construct(
        private readonly string|null $domain = null,
        ClockInterface|null $clock = null,
    ) {
        $this->clock = $clock ?? new class () implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };
    }

    public function sign(
        SignableDataInterface|string $data,
        DateTimeInterface|null $expiresAt = null,
    ): Signature {
        $data = is_string($data) ? $data : $data->toSignableData();
        return new Signature(
            rawSignature: $this->generateRawSignature(self::packDataWithMetadata(
                $data,
                $this->domain,
                $expiresAt?->format(Signature::DATETIME_FORMAT_UNIX_TIMESTAMP),
            )),
            expiresAt: $expiresAt,
            signedData: OptionalString::of($data),
        );
    }

    /**
     * @param Signature|string $signatureWithData signature instance or binary representation of a signature
     *
     * @return OptionalString optional binary representation of the verified data
     */
    public function verified(
        Signature|string $signatureWithData,
    ): OptionalString {
        $signatureWithData = is_string($signatureWithData)
            ? Signature::fromBinary($signatureWithData)
            : $signatureWithData;
        return $signatureWithData->signedData->filter(
            fn (string $data): bool => $this->verify($data, $signatureWithData),
        );
    }

    public function verify(
        SignableDataInterface|string $data,
        Signature|string $signature,
    ): bool {
        $signature = is_string($signature) ? Signature::fromBinary($signature) : $signature;
        if ($signature->expiresAt !== null && $signature->expiresAt < $this->clock->now()) {
            return false;
        }
        $expectedSignature = $this->sign(
            data: $data,
            expiresAt: $signature->expiresAt,
        );
        return $signature->rawSignature === $expectedSignature->rawSignature;
    }

    /**
     * @param non-empty-string|null $domain
     *
     * @return static with given domain
     */
    abstract public function withDomain(string|null $domain): static;

    /**
     * @param string $rawData binary representation of a data
     *
     * @return string binary representation of a signature
     */
    abstract protected function generateRawSignature(string $rawData): string;

    private static function packDataWithMetadata(string $data, string|null ...$metadata): string
    {
        foreach ($metadata as $unit) {
            if ($unit !== null) {
                return $data . self::FILE_SEPARATOR . implode(self::UNIT_SEPARATOR, $metadata); // @todo remove support for binary 4 and use Ascii::join()
            }
        }
        return $data;
    }
}
