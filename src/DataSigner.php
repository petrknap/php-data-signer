<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

/**
 * @note Use {@see DataSignerInterface} if you need strong cross-platform compatibility.
 */
abstract class DataSigner implements DataSignerInterface
{
    public function sign(
        string $data,
    ): Signature {
        return new Signature(
            rawSignature: $this->generateRawSignature(
                data: $data,
            ),
        );
    }

    public function verify(
        string $data,
        Signature|string $signature,
    ): bool {
        $signature = is_string($signature) ? Signature::fromBinary($signature) : $signature;
        $expectedSignature = $this->sign(
            data: $data,
        );
        return $signature->rawSignature === $expectedSignature->rawSignature;
    }

    /**
     * @param string $data binary representation of a data
     *
     * @return string binary representation of a signature
     */
    abstract protected function generateRawSignature(string $data): string;
}
