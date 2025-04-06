<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

interface SignableDataInterface
{
    /**
     * @return string signable by {@see DataSignerInterface}
     */
    public function toSignableData(): string;
}
