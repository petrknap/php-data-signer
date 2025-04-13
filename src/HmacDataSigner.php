<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use Psr\Clock\ClockInterface;
use SensitiveParameter;

final class HmacDataSigner extends DataSigner
{
    /**
     * @param non-empty-string $hashAlgorithm value of {@see hash_algos()}
     * @param string $key binary representation of a key
     */
    public function __construct(
        private readonly string $hashAlgorithm,
        #[SensitiveParameter]
        private readonly string $key,
        string|null $domain = null,
        ClockInterface|null $clock = null,
    ) {
        parent::__construct(
            domain: $domain,
            clock: $clock,
        );
    }

    public function withDomain(string|null $domain): static
    {
        return new self(
            hashAlgorithm: $this->hashAlgorithm,
            key: $this->key,
            domain: $domain,
            clock: $this->clock,
        );
    }

    protected function generateRawSignature(string $rawData): string
    {
        return hash_hmac(
            algo: $this->hashAlgorithm,
            data: $rawData,
            key: $this->key,
            binary: true,
        );
    }
}
