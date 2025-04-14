<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

/**
 * @note Use {@see DataSigner} if you want to have access to full functionality.
 */
interface DataSignerInterface
{
    /**
     * @param SignableDataInterface|string $data signable data instance or binary representation of a data
     *
     * @throws Exception\DataSignerCouldNotSignData
     */
    public function sign(
        SignableDataInterface|string $data,
    ): Signature;

    /**
     * @param SignableDataInterface|string $data signable data instance or binary representation of a data
     * @param Signature|non-empty-string $signature signature instance or binary representation of a signature
     */
    public function verify(
        SignableDataInterface|string $data,
        Signature|string $signature,
    ): bool;
}
