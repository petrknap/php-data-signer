<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use Psr\Clock\ClockInterface;

/**
 * @note Tests internal implementation of {@see DataSigner} via {@see Some\DataSigner}.
 */
final class DataSignerTest extends DataSignerTestCase
{
    protected static function getDataSigner(ClockInterface $clock): DataSigner
    {
        return new Some\DataSigner(
            clock: $clock,
        );
    }

    protected static function getRawSignatures(): iterable
    {
        return [
            'data' => self::DATA,
            'data + domain' => self::DATA . DataSigner::FILE_SEPARATOR . self::DOMAIN . DataSigner::UNIT_SEPARATOR,
            'data + domain + expiresAt' => self::DATA . DataSigner::FILE_SEPARATOR . self::DOMAIN . DataSigner::UNIT_SEPARATOR . self::getClock()->now()->getTimestamp(),
            'data + expiresAt' => self::DATA . DataSigner::FILE_SEPARATOR . DataSigner::UNIT_SEPARATOR . self::getClock()->now()->getTimestamp(),
        ];
    }
}
