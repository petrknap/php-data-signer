<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

/**
 * Dummy implementation for testing purposes
 */
final class SomeDataSigner extends DataSigner
{
    protected function generateRawSignature(string $data): string
    {
        return $data;
    }
}
