<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner\Exception;

use PetrKnap\DataSigner\DataSignerInterface;
use PetrKnap\DataSigner\Signature;
use RuntimeException;

final class DataSignerCouldNotVerifySignatureWithOriginalData extends RuntimeException implements DataSignerException
{
    public function __construct(
        string $message,
        public readonly DataSignerInterface $dataSigner,
        public readonly Signature $signatureWithOriginalData,
    ) {
        parent::__construct($message);
    }
}
