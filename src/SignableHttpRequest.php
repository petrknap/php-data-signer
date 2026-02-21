<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

/**
 * @note Defines a shared contract; implement your environment‑specific version.
 *
 * @see Some\SignableHttpRequest for inspiration
 */
abstract class SignableHttpRequest implements SignableDataInterface
{
    protected readonly string $method;
    /**
     * @var array<string, string>
     */
    protected readonly array $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        string $method,
        protected readonly string $uri,
        array $headers,
        protected readonly string $body,
    ) {
        $this->method = static::normalizeMethod($method);
        $this->headers = static::normalizeHeaders($headers);
    }

    /**
     * @return non-empty-string HTTP/1.1-like - headers are normalized by {@see static::normalizeHeaders()}
     *
     * @link https://datatracker.ietf.org/doc/html/rfc2616
     */
    public function toSignableData(): string
    {
        $http11Like = ["{$this->method} {$this->uri} HTTP/1.1"];
        foreach ($this->headers as $name => $value) {
            $http11Like[] = "{$name}: {$value}";
        }
        $http11Like[] = '';
        $http11Like[] = $this->body;

        return implode("\r\n", $http11Like);
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<string, string>
     */
    protected static function normalizeHeaders(array $headers): array
    {
        ksort($headers); // stabilize merge
        $normalizedHeaders = $prenormalizedHeaders = [];
        foreach ($headers as $name => $value) {
            $normalizedName = implode('-', array_map('ucfirst', explode('-', strtolower($name))));
            $stringValue = (string) $value;
            if (array_key_exists($normalizedName, $prenormalizedHeaders)) {
                $prenormalizedHeaders[$normalizedName][] = $stringValue;
            } else {
                $prenormalizedHeaders[$normalizedName] = [$stringValue];
            }
        }
        foreach ($prenormalizedHeaders as $key => $values) {
            sort($values);
            $normalizedHeaders[$key] = implode(',', $values);
        }
        ksort($normalizedHeaders); // stabilize output
        return $normalizedHeaders;
    }

    protected static function normalizeMethod(string $method): string
    {
        return strtoupper($method);
    }
}
