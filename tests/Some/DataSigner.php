<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner\Some;

use PetrKnap\DataSigner\DataSigner as AbstractDataSigner;

/**
 * @internal dummy implementation for testing and demonstration purposes
 */
final class DataSigner extends AbstractDataSigner
{
    public function withDomain(string|null $domain): static
    {
        return new self(
            domain: $domain,
            clock: $this->clock,
        );
    }

    protected function generateRawSignature(string $rawData): string
    {
        return $rawData;
    }
}
