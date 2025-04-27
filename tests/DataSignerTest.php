<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use PetrKnap\Binary\Ascii;
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
            'data + domain' => Ascii::FileSeparator->join(
                self::DATA,
                Ascii::UnitSeparator->join(
                    self::DOMAIN,
                    '',
                ),
            ),
            'data + domain + expiresAt' => Ascii::FileSeparator->join(
                self::DATA,
                Ascii::UnitSeparator->join(
                    self::DOMAIN,
                    (string) self::getClock()->now()->getTimestamp(),
                ),
            ),
            'data + expiresAt' => Ascii::FileSeparator->join(
                self::DATA,
                Ascii::UnitSeparator->join(
                    '',
                    (string) self::getClock()->now()->getTimestamp(),
                ),
            ),
        ];
    }
}
