<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use DateTimeImmutable;
use DateTimeInterface;
use Psr\Clock\ClockInterface;

/**
 * @note Use {@see DataSignerInterface} if you need strong cross-platform compatibility.
 */
abstract class DataSigner implements DataSignerInterface
{
    protected readonly ClockInterface $clock;

    public function __construct(
        private readonly string|null $domain = null,
        ClockInterface|null $clock = null,
    ) {
        $this->clock = $clock ?? new class () implements ClockInterface {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };
    }

    public function sign(
        string $data,
        DateTimeInterface|null $expiresAt = null,
    ): Signature {
        return new Signature(
            rawSignature: $this->generateRawSignature(
                data: $this->domain . $data . $expiresAt?->getTimestamp(),
            ),
            expiresAt: $expiresAt,
        );
    }

    public function verify(
        string $data,
        Signature|string $signature,
    ): bool {
        $signature = is_string($signature) ? Signature::fromBinary($signature) : $signature;
        if ($signature->expiresAt !== null && $signature->expiresAt < $this->clock->now()) {
            return false;
        }
        $expectedSignature = $this->sign(
            data: $data,
            expiresAt: $signature->expiresAt,
        );
        return $signature->rawSignature === $expectedSignature->rawSignature;
    }

    /**
     * @param non-empty-string|null $domain
     *
     * @return static with given domain
     */
    abstract public function withDomain(string|null $domain): static;

    /**
     * @param string $data binary representation of a data
     *
     * @return string binary representation of a signature
     */
    abstract protected function generateRawSignature(string $data): string;
}
