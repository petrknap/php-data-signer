<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

/**
 * @itnernal dummy implementation for testing and demonstration purposes
 */
final class SomeDataSigner extends DataSigner
{
    public function withDomain(string|null $domain): static
    {
        return new self(
            domain: $domain,
        );
    }

    protected function generateRawSignature(string $data): string
    {
        return $data;
    }
}
