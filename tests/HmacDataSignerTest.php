<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

final class HmacDataSignerTest extends DataSignerTestCase
{
    protected static function getDataSigner(): DataSigner
    {
        return new HmacDataSigner('sha256', 'secret');
    }

    protected static function getRawSignatures(): iterable
    {
        return [
            'data' => base64_decode('GywWt1vSqHDBFBU8zaW8/KYzFLxyL6Fg1pDeEzzLuds='),
            'domain + data' => base64_decode('9DBXdOVO+vhWvm6F5ZSE8Np6cSaeVnsEPIxelmKZIic='),
        ];
    }
}
