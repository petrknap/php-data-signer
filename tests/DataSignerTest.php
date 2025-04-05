<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use Psr\Clock\ClockInterface;

/**
 * @note Tests internal implementation of {@see DataSigner} via {@see SomeDataSigner}.
 */
final class DataSignerTest extends DataSignerTestCase
{
    protected static function getDataSigner(ClockInterface $clock): DataSigner
    {
        return new SomeDataSigner(
            clock: $clock,
        );
    }

    protected static function getRawSignatures(): iterable
    {
        return [
            'data' => self::DATA,
            'data + expiresAt' => self::DATA . self::getClock()->now()->getTimestamp(),
            'domain + data' => self::DOMAIN . self::DATA,
            'domain + data + expiresAt' => self::DOMAIN . self::DATA . self::getClock()->now()->getTimestamp(),
        ];
    }
}
