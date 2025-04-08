# Data Signer

This library provides a logic for digital signing and validating of a binary data.

Inputs and outputs are binary data, don't be afraid to use the [`petrknap/binary`](https://github.com/petrknap/php-binary).


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


---

Run `composer require petrknap/data-signer` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
