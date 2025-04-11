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

    private const KEY_RAW_SIGNATURE = 0;
    private const KEY_EXPIRES_AT = 1;
    private const KEY_ORIGINAL_DATA = 2;

    public function __construct(
        public readonly string $rawSignature,
        public readonly DateTimeInterface|null $expiresAt,
        public readonly OptionalString $originalData,
    ) {
    }

    /**
     * @note To restore the encoded instance, you need to decode it first and then call the {@see self::fromBinary()} method.
     * @note The {@see Encoder} only encodes data - encoded data are readable.
     */
    public function encode(bool $withOriginalData = false): Encoder
    {
        return new Encoder($this->toBinary($withOriginalData));
    }

    public function toBinary(bool $withOriginalData = false): string
    {
        if (!$withOriginalData && $this->expiresAt === null) {
            return $this->rawSignature;
        }
        return self::getSerializer()->serialize(array_filter([
            self::KEY_RAW_SIGNATURE => $this->rawSignature,
            self::KEY_EXPIRES_AT => $this->expiresAt === null ? null : (int) $this->expiresAt->format('U'),
            self::KEY_ORIGINAL_DATA => $withOriginalData ? $this->originalData->orElseThrow(
                static fn () => new class () extends RuntimeException implements Serializer\Exception\SerializerException {
                }, // @todo remove support for binary 4 and create correct exception
            ) : null,
        ], static fn (mixed $value): bool => $value !== null));
    }

    public static function fromBinary(string $data): self
    {
        try {
            /** @var array<int, string|int> $unserialized */
            $unserialized = @self::getSerializer()->unserialize($data); // @todo remove support for binary 4
            /** @var string $rawSignature */
            $rawSignature = $unserialized[self::KEY_RAW_SIGNATURE]
                ?? throw new class () extends RuntimeException implements Serializer\Exception\SerializerException {
                }; // @todo remove support for binary 4 and throw correct exception
            $rawExpiresAt = $unserialized[self::KEY_EXPIRES_AT] ?? null;
            /** @var DateTimeImmutable|null $expiresAt */
            $expiresAt = $rawExpiresAt === null ? null : DateTimeImmutable::createFromFormat('U', (string) $rawExpiresAt);
            /** @var string|null $rawOriginalData */
            $rawOriginalData = $unserialized[self::KEY_ORIGINAL_DATA] ?? null;
            $originalData = OptionalString::ofNullable($rawOriginalData);
        } catch (Serializer\Exception\SerializerException) {
            $rawSignature = $data;
            $expiresAt = null;
            $originalData = OptionalString::empty();
        }
        return new self(
            rawSignature: $rawSignature,
            expiresAt: $expiresAt,
            originalData: $originalData,
        );
    }

    private static function getSerializer(): Serializer\SerializerInterface
    {
        return new Serializer\Php();
    }
}
