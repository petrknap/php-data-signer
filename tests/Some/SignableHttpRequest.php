<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner\Some;

use PetrKnap\DataSigner\SignableHttpRequest as Base;
use Psr\Http\Message\RequestInterface;

final class SignableHttpRequest extends Base
{
    private const SIGNABLE_HEADERS = [
        'Host',
        'X-User-Id',
        'X-Timestamp',
    ];

    public function __construct(RequestInterface $request)
    {
        $uri = $request->getUri();
        $query = $uri->getQuery();
        parent::__construct(
            method: $request->getMethod(),
            uri: $uri->getPath() . ($query === '' ? '' : '?' . $query),
            headers: array_filter(array_map(
                $request->getHeaderLine(...),
                array_combine(self::SIGNABLE_HEADERS, self::SIGNABLE_HEADERS),
            )),
            body: (string) $request->getBody(),
        );
    }
}
