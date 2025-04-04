<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use PetrKnap\Binary\BinariableInterface;
use PetrKnap\Binary\BinariableTrait;
use PetrKnap\Binary\Serializer;

final class Signature implements BinariableInterface, Serializer\SelfSerializerInterface
{
    use BinariableTrait;

    public function __construct(
        public readonly string $rawSignature,
    ) {
    }

    public function toBinary(): string
    {
        return $this->rawSignature;
    }

    public static function fromBinary(string $data): self
    {
        return new self(
            rawSignature: $data,
        );
    }
}
