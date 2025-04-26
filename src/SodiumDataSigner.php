<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner;

use InvalidArgumentException;
use PetrKnap\CryptoSodium\Sign;
use PetrKnap\Optional\OptionalString;
use PetrKnap\Shorts\HasRequirements;
use SensitiveParameter;
use Throwable;

/**
 * An adapter for {@link https://libsodium.org/} to meet {@see DataSignerInterface}
 *
 * @see Sign (combined mode)
 *
 * @note Use {@see Ed25519DataSigner} if you want to have access to full functionality.
 */
final class SodiumDataSigner implements DataSignerInterface
{
    use HasRequirements;

    private readonly Sign $sodium;

    /**
     * @param non-empty-string $secretKey as returned by {@see Sign::extractSecretKey()}
     * @param non-empty-string $publicKey as returned by {@see Sign::extractPublicKey()}
     */
    public function __construct(
        #[SensitiveParameter]
        private string $secretKey = "\x00", // @todo remove support for binary 4 and use Ascii::Null
        private string $publicKey = "\x00", // @todo remove support for binary 4 and use Ascii::Null
    ) {
        self::checkRequirements(
            classes: [
                Sign::class,
            ],
        );

        if ($this->secretKey === "\x00" && $this->publicKey === "\x00") { // @todo remove support for binary 4 and use Ascii::Null
            throw new InvalidArgumentException('At least one of $secretKey and $publicKey is required');
        }

        $this->sodium = new Sign();
    }

    public function __destruct()
    {
        $this->sodium->eraseData($this->secretKey);
        $this->sodium->eraseData($this->publicKey);
    }

    /**
     * @return Signature signature with hardcoded data
     */
    public function sign(SignableDataInterface|string $data): Signature
    {
        try {
            $dataString = is_string($data) ? $data : $data->toSignableData();
            return new Signature(
                rawSignature: $this->sodium->sign($dataString, $this->secretKey),
                expiresAt: null,
                signedData: OptionalString::empty(),
            );
        } catch (Throwable $reason) {
            throw new Exception\DataSignerCouldNotSignData(__METHOD__, $data, $reason);
        }
    }

    public function verified(Signature|string $signatureWithData): OptionalString
    {
        try {
            $signatureWithDataInstance = is_string($signatureWithData) ? Signature::fromBinary($signatureWithData) : $signatureWithData;
            return OptionalString::of($this->sodium->verified($signatureWithDataInstance->rawSignature, $this->publicKey));
        } catch (Throwable) {
            return OptionalString::empty();
        }
    }

    public function verify(SignableDataInterface|string $data, Signature|string $signature): bool
    {
        $dataString = is_string($data) ? $data : $data->toSignableData();
        return $this->verified($signature)->toNullable() === $dataString;
    }
}
