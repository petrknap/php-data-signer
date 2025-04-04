<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

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
    ) {
    }

    protected function generateRawSignature(string $data): string
    {
        return hash_hmac(
            algo: $this->hashAlgorithm,
            data: $data,
            key: $this->key,
            binary: true,
        );
    }
}
