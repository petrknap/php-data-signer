<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use DateTimeImmutable;
use DateTimeInterface;
use PetrKnap\Binary\BinariableInterface;
use PetrKnap\Binary\BinariableTrait;
use PetrKnap\Binary\Encoder;
use PetrKnap\Binary\Serializer;
use PetrKnap\Optional\OptionalString;
use RuntimeException;

/**
 * @note A change in the values of `KEY_` constants is a breaking change.
 */
final class Signature implements BinariableInterface, Serializer\SelfSerializerInterface
{
    use BinariableTrait;

    /**
     * @internal public to be accessible in {@see DataSigner}
     */
    public const DATETIME_FORMAT_UNIX_TIMESTAMP = 'U';

    private const KEY_RAW_SIGNATURE = 0;
    private const KEY_EXPIRES_AT = 1;
    private const KEY_SIGNED_DATA = 2;

    /**
     * @param string $rawSignature binary representation of the signature
     * @param OptionalString $signedData optional not verified binary representation of the signed data
     */
    public function __construct(
        public readonly string $rawSignature,
        public readonly DateTimeInterface|null $expiresAt,
        public readonly OptionalString $signedData,
    ) {
    }

    /**
     * @note To restore the encoded instance, you need to decode it first and then call the {@see self::fromBinary()} method.
     * @note The {@see Encoder} only encodes data - encoded data are readable.
     */
    public function encode(bool $withData = false): Encoder
    {
        return new Encoder($this->toBinary($withData));
    }

    public function toBinary(bool $withData = false): string
    {
        if (!$withData && $this->expiresAt === null) {
            return $this->rawSignature;
        }
        return self::getSerializer()->serialize(array_filter([
            self::KEY_RAW_SIGNATURE => $this->rawSignature,
            self::KEY_EXPIRES_AT => $this->expiresAt === null ? null : (int) $this->expiresAt->format(self::DATETIME_FORMAT_UNIX_TIMESTAMP),
            self::KEY_SIGNED_DATA => $withData ? $this->signedData->orElseThrow(
                static fn () => new class () extends RuntimeException implements Serializer\Exception\SerializerException {
                }, // @todo remove support for binary 4 and create correct exception
            ) : null,
        ], static fn (mixed $value): bool => $value !== null));
    }

    public static function fromBinary(string $data): self
    {
        try {
            /** @var array<int, string|int> $unserialized */
            $unserialized = @self::getSerializer()->unserialize($data); // @todo remove support for binary 4 and remove error suppression
            /** @var string $rawSignature */
            $rawSignature = $unserialized[self::KEY_RAW_SIGNATURE]
                ?? throw new class () extends RuntimeException implements Serializer\Exception\SerializerException {
                }; // @todo remove support for binary 4 and throw correct exception
            $rawExpiresAt = $unserialized[self::KEY_EXPIRES_AT] ?? null;
            /** @var DateTimeImmutable|null $expiresAt */
            $expiresAt = $rawExpiresAt === null ? null : DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT_UNIX_TIMESTAMP, (string) $rawExpiresAt);
            /** @var string|null $rawSignedData */
            $rawSignedData = $unserialized[self::KEY_SIGNED_DATA] ?? null;
            $signedData = OptionalString::ofNullable($rawSignedData);
        } catch (Serializer\Exception\SerializerException) {
            $rawSignature = $data;
            $expiresAt = null;
            $signedData = OptionalString::empty();
        }
        return new self(
            rawSignature: $rawSignature,
            expiresAt: $expiresAt,
            signedData: $signedData,
        );
    }

    private static function getSerializer(): Serializer\SerializerInterface
    {
        return new Serializer\Php();
    }
}
