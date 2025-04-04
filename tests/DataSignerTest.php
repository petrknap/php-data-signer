<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

final class DataSignerTest extends DataSignerTestCase
{
    protected static function getDataSigner(): DataSigner
    {
        return new SomeDataSigner();
    }

    protected static function getRawSignatures(): iterable
    {
        return [
            'data' => self::DATA,
        ];
    }
}
