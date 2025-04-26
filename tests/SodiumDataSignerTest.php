<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use PHPUnit\Framework\TestCase;

final class SodiumDataSignerTest extends TestCase
{
    public const B64_SECRET_KEY = 'o8DJ9Tp7MT0nOjzzpnrjNQswHHJgwVKrtGgzwWlflaNua/ZaQnutowbocwmRQ1FaJ0C5tJg0jBe8rBay1sOvzQ==';
    public const B64_PUBLIC_KEY = 'bmv2WkJ7raMG6HMJkUNRWidAubSYNIwXvKwWstbDr80=';

    public function testSignsAndVerifiesData(): void
    {
        $dataSigner = new SodiumDataSigner(
            secretKey: base64_decode(self::B64_SECRET_KEY),
            publicKey: base64_decode(self::B64_PUBLIC_KEY),
        );

        $signatureWithData = $dataSigner->sign(DataSignerTestCase::DATA)->toBinary();
        self::assertSame(
            DataSignerTestCase::DATA,
            $dataSigner->verified($signatureWithData)->orElseThrow(),
        );
    }
}
