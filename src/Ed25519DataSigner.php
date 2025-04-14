<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use PetrKnap\Shorts\HasRequirements;
use Psr\Clock\ClockInterface;
use SensitiveParameter;

/**
 * @see sodium_crypto_sign_detached()
 */
final class Ed25519DataSigner extends DataSigner
{
    use HasRequirements;

    /**
     * @param non-empty-string $secretKey as returned by {@see sodium_crypto_sign_secretkey()}
     * @param non-empty-string $publicKey as returned by {@see sodium_crypto_sign_publickey()}
     */
    public function __construct(
        #[SensitiveParameter]
        private string $secretKey = "\x00", // @todo remove support for binary 4 and use Ascii::Null
        private readonly string $publicKey = "\x00", // @todo remove support for binary 4 and use Ascii::Null
        string|null $domain = null,
        ClockInterface|null $clock = null,
    ) {
        self::checkRequirements(
            functions: [
                'sodium_memzero',
                'sodium_crypto_sign_detached',
                'sodium_crypto_sign_verify_detached',
            ],
        );
        parent::__construct(
            domain: $domain,
            clock: $clock,
        );
    }

    public function __destruct()
    {
        sodium_memzero($this->secretKey);
    }

    public function withDomain(string|null $domain): static
    {
        return new self(
            secretKey: $this->secretKey,
            publicKey: $this->publicKey,
            domain: $domain,
            clock: $this->clock,
        );
    }

    protected function generateRawSignature(string $rawData): string
    {
        return sodium_crypto_sign_detached($rawData, $this->secretKey);
    }

    protected function verifyRawDataByRawSignature(string $rawData, string $rawSignature): bool
    {
        return sodium_crypto_sign_verify_detached($rawSignature, $rawData, $this->publicKey);
    }
}
