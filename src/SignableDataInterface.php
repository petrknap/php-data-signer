<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use PetrKnap\Binary\Ascii;

interface SignableDataInterface
{
    /**
     * @note To avoid collisions, you can use {@see Ascii} separators.
     *
     * @return string signable by {@see DataSignerInterface}
     */
    public function toSignableData(): string;
}
