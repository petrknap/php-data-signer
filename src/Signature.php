<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use DateTimeImmutable;
use DateTimeInterface;
use PetrKnap\Binary\BinariableInterface;
use PetrKnap\Binary\BinariableTrait;
use PetrKnap\Binary\Encoder;
use PetrKnap\Binary\Serializer;
use RuntimeException;

final class Signature implements BinariableInterface, Serializer\SelfSerializerInterface
{
    use BinariableTrait;

    /**
     * @internal public to be accessible in {@see DataSigner}
     */
    public const DATETIME_FORMAT_UNIX_TIMESTAMP = 'U';

    private const KEY_RAW_SIGNATURE = 0;
    private const KEY_EXPIRES_AT = 1;

    public function __construct(
        public readonly string $rawSignature,
        public readonly DateTimeInterface|null $expiresAt,
    ) {
    }

    /**
     * @note To restore the encoded instance, you need to decode it first and then call the {@see self::fromBinary()} method.
     */
    public function encode(): Encoder
    {
        return new Encoder($this->toBinary());
    }

    public function toBinary(): string
    {
        if ($this->expiresAt === null) {
            return $this->rawSignature;
        }
        return self::getSerializer()->serialize([
            self::KEY_RAW_SIGNATURE => $this->rawSignature,
            self::KEY_EXPIRES_AT => (int) $this->expiresAt->format(self::DATETIME_FORMAT_UNIX_TIMESTAMP),
        ]);
    }

    public static function fromBinary(string $data): self
    {
        try {
            /** @var array<int, string|int> $unserialized */
            $unserialized = @self::getSerializer()->unserialize($data); // @todo remove support for binary 4 and remove error suppression
            /** @var string $rawSignature */
            $rawSignature = $unserialized[self::KEY_RAW_SIGNATURE]
                ?? throw new class () extends RuntimeException implements Serializer\Exception\SerializerException {
                };
            $rawExpiresAt = $unserialized[self::KEY_EXPIRES_AT]
                ?? throw new class () extends RuntimeException implements Serializer\Exception\SerializerException {
                };
            /** @var DateTimeImmutable|null $expiresAt */
            $expiresAt = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT_UNIX_TIMESTAMP, (string) $rawExpiresAt);
        } catch (Serializer\Exception\SerializerException) {
            $rawSignature = $data;
            $expiresAt = null;
        }
        return new self(
            rawSignature: $rawSignature,
            expiresAt: $expiresAt,
        );
    }

    private static function getSerializer(): Serializer\SerializerInterface
    {
        return new Serializer\Php();
    }
}
