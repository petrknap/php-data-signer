# Data Signer

This library provides a logic for digital signing and validating of a binary data.

Inputs and outputs are binary data, don't be afraid to [use the `petrknap/binary`](https://github.com/petrknap/php-binary).


## Usage

The **basic use** of [a Data Signer interface](./src/DataSignerInterface.php) is quite simple:
```php
use PetrKnap\DataSigner\Some;

$signer = new Some\DataSigner();
$data = 'some data';
$signature = $signer->sign($data);
if ($signer->verify($data, $signature)) {
    echo 'Data was successfully verified by signature.';
}
```

### Domain-specific signing

If you need to **limit the validity** of the signature **to a specific purpose** (domain), just set it to [the Data Signer service](./src/DataSigner.php):
```php
use PetrKnap\DataSigner\Some;

$signer = new Some\DataSigner();
$data = 'some data';
$signature = $signer->withDomain('password_reset')->sign($data);
if (!$signer->withDomain('cookies')->verify($data, $signature)) {
    echo 'You can not use signature generated for `password_reset` in `cookies`.';
}
```

### Time-limited signing

If you need to **limit the validity** of the signature **to specific time** (expiration), just give it to [the Data Signer's method sign](./src/DataSigner.php):
```php
use PetrKnap\DataSigner\Some;

$signer = new Some\DataSigner();
$data = 'some data';
$signature = $signer->sign($data, expiresAt: new DateTimeImmutable('2025-04-05 09:40:53+02:00'));
if (!$signer->verify($data, $signature)) {
    echo 'You can not use signature after its expiration.';
}
```

### Signable data transfer object

If you **communicate through data transfer objects**, you can use [a Signable Data interface](./src/SignableDataInterface.php):
```php
use PetrKnap\DataSigner\Some;

$apiClient = new class (new Some\DataSigner()) {
    public function __construct(private readonly Some\DataSigner $dataSigner) {}
    public function put(Some\DataTransferObject $payload): void {
        $signature = $this->dataSigner->sign($payload);
        $requestBody = [
            ...$payload->jsonSerialize(),
            'signature' => $signature->encode()->base64()->getData(),
        ];
        echo json_encode($requestBody);  # here should be the API call
    }
};
$apiClient->put(new Some\DataTransferObject(
    property: 'some value',
));
```

### Communication trough 3rd party machine

If you need to **sign data forwarded trough 3rd party machine** (f.e. by token or cookie), you can use [the Signature with data](./src/Signature.php):
```php
use PetrKnap\Binary\Binary;
use PetrKnap\DataSigner\Some;

$signer = new Some\DataSigner();

$passwordResetToken = $signer->withDomain('password_reset')->sign(
    data: 'some_user',
    expiresAt: (new DateTimeImmutable())->modify('+3 hours'),
)->encode(withData: true)->zlib()->base64(urlSafe: true)->getData();

$verifiedUserIdentifier = $signer->withDomain('password_reset')->verified(
    Binary::decode($passwordResetToken)->base64()->zlib()->getData(),
)->orElseThrow();
echo "Verified user identifier is `{$verifiedUserIdentifier}`.";
```

**WARNING:** The data are only signed (readable), [use the `petrknap/crypto-sodium`](https://github.com/petrknap/php-crypto-sodium) if you need to encrypt them.


---

Run `composer require petrknap/data-signer` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
