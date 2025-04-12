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
            'data + domain' => base64_decode('W4l96M0av16MFfBuEoByGUlS7SKtqL4SkPU8mArbtY0='),
            'data + domain + expiresAt' => base64_decode('FIDtxqWlt86GsGsrCofKJrDZSzFA+ruslW3ELOxR9Cc='),
            'data + expiresAt' => base64_decode('mS25zoGWghyPp/or4vm+16t1Ee1IfvLmViDX3+tbk70='),
        ];
    }
}
