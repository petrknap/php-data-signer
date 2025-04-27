<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use InvalidArgumentException;
use PetrKnap\Binary\Ascii;
use PetrKnap\CryptoSodium\Sign;
use PetrKnap\Shorts\HasRequirements;
use Psr\Clock\ClockInterface;
use SensitiveParameter;

/**
 * @see Sign (detached mode)
 *
 * @note Use {@see SodiumDataSigner} if you want to pack signature with data by {@link https://libsodium.org/}.
 */
final class Ed25519DataSigner extends DataSigner
{
    use HasRequirements;

    private readonly Sign $sodium;

    /**
     * @param non-empty-string $secretKey as returned by {@see Sign::extractSecretKey()}
     * @param non-empty-string $publicKey as returned by {@see Sign::extractPublicKey()}
     */
    public function __construct(
        #[SensitiveParameter]
        private string $secretKey = Ascii::NULL,
        private string $publicKey = Ascii::NULL,
        string|null $domain = null,
        ClockInterface|null $clock = null,
    ) {
        self::checkRequirements(
            classes: [
                Sign::class,
            ],
        );

        if ($this->secretKey === Ascii::NULL && $this->publicKey === Ascii::NULL) {
            throw new InvalidArgumentException('At least one of $secretKey and $publicKey is required');
        }

        $this->sodium = new Sign();

        parent::__construct(
            domain: $domain,
            clock: $clock,
        );
    }

    public function __destruct()
    {
        $this->sodium->eraseData($this->secretKey);
        $this->sodium->eraseData($this->publicKey);
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
        return $this->sodium->signDetached($rawData, $this->secretKey);
    }

    protected function verifyRawDataByRawSignature(string $rawData, string $rawSignature): bool
    {
        return $this->sodium->verifyDetached($rawSignature, $rawData, $this->publicKey);
    }
}
