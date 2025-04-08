<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner\Some;

use JsonSerializable;
use PetrKnap\DataSigner\SignableDataInterface;

/**
 * @internal dummy implementation for testing and demonstration purposes
 */
final class DataTransferObject implements JsonSerializable, SignableDataInterface
{
    public function __construct(
        public readonly string $property,
    ) {
    }

    public function toSignableData(): string
    {
        return $this->property;
    }

    public function jsonSerialize(): array
    {
        return [
            'property' => $this->property,
        ];
    }
}
