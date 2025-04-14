<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use Psr\Clock\ClockInterface;

final class Ed25519DataSignerTest extends DataSignerTestCase
{
    protected static function getDataSigner(ClockInterface $clock): DataSigner
    {
        $keyPair = sodium_crypto_sign_seed_keypair(base64_decode('o8DJ9Tp7MT0nOjzzpnrjNQswHHJgwVKrtGgzwWlflaM='));
        return new Ed25519DataSigner(
            secretKey: sodium_crypto_sign_secretkey($keyPair),
            publicKey: sodium_crypto_sign_publickey($keyPair),
            clock: $clock,
        );
    }

    protected static function getRawSignatures(): iterable
    {
        return [
            'data' => base64_decode('HkLNVpadJJWzhfXAbYRmqC9VTXTuih92zdErqd3ESSlQjsuIr6fpGgwfhr4bMs4KlXUnfaSC7NKVxBz8tFluAA=='),
            'data + domain' => base64_decode('Qei3TzzgIvVIEZ3McpruVpX/BgbWdWDNsxHyLOcd2tlrcz5CNf+20HwyCQMQWwW0vxbMbIQbx1Hw5u+QqvnBBA=='),
            'data + domain + expiresAt' => base64_decode('bNdFISNRne6e6oxnpr3hUu9qOUK5P0T9HTNGW2VSiMqX63ubOI5R8ioHtGzW1enePzkUNrczqswZj/4HL4xVCA=='),
            'data + expiresAt' => base64_decode('7qShuQm25HTTQKCpARurOOM6ggA1hp7CXS1jV6Hmd5hU+Hrb/lu2e0Rbgs8FANObStGXH/zSHa8OXVKX04sDDQ=='),
        ];
    }
}
