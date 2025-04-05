<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use Psr\Clock\ClockInterface;

final class HmacDataSignerTest extends DataSignerTestCase
{
    protected static function getDataSigner(ClockInterface $clock): DataSigner
    {
        return new HmacDataSigner(
            hashAlgorithm: 'sha256',
            key: 'secret',
            clock: $clock,
        );
    }

    protected static function getRawSignatures(): iterable
    {
        return [
            'data' => base64_decode('GywWt1vSqHDBFBU8zaW8/KYzFLxyL6Fg1pDeEzzLuds='),
            'data + expiresAt' => base64_decode('gz98uKKzzuxdIPRI305Ouq9YHNKxzonaIPZHFM+MZJY='),
            'domain + data' => base64_decode('9DBXdOVO+vhWvm6F5ZSE8Np6cSaeVnsEPIxelmKZIic='),
            'domain + data + expiresAt' => base64_decode('oVGUfzBMm+nY/cdS7GNhHBZUS/5zyWN57NLXrxmaCXY='),
        ];
    }
}
