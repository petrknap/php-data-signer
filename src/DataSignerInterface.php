<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

/**
 * @note Use {@see DataSigner} if you want to have access to full functionality.
 */
interface DataSignerInterface
{
    /**
     * @param string $data binary representation of a data
     */
    public function sign(
        string $data,
    ): Signature;

    /**
     * @param string $data binary representation of a data
     * @param Signature|string $signature instance or binary representation of a signature
     */
    public function verify(
        string $data,
        Signature|string $signature,
    ): bool;
}
