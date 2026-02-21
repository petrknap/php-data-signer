# Data Signer

This library provides a logic for digital signing and validating of a binary data.

Inputs and outputs are binary data, don't be afraid to [use the `petrknap/binary`](https://github.com/petrknap/php-binary).
The data are only signed (readable), [use the `petrknap/crypto-sodium`](https://github.com/petrknap/php-crypto-sodium) if you need to encrypt them.

- Implementations
  - [Edwards-curve Digital Signature Algorithm (Ed25519)](./src/Ed25519DataSigner.php)
  - [Hash-based Message Authentication Code (HMAC)](./src/HmacDataSigner.php)
  - [Sodium (Ed25519)](./src/SodiumDataSigner.php)
- Usage
  - [Basic use](#usage)
  - [Domain-specific signing](#domain-specific-signing)
  - [Time-limited signing](#time-limited-signing)
  - [Signable HTTP request](#signable-http-request)
  - [Signable data transfer object](#signable-data-transfer-object)
  - [Communication trough 3rd party machine](#communication-trough-3rd-party-machine)



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


### Signable HTTP request

If you **communicate trough HTTP**, you can use [a Signable HTTP Request](./src/SignableHttpRequest.php).
The following **example is more complex and uses asymmetric cryptography** to show you how to use it **the best way**.
You can simply make it symmetric by change of the data signer.
```php
namespace PetrKnap\DataSigner;

# Sender side
$apiSigner = new Ed25519DataSigner(secretKey: base64_decode('o8DJ9Tp7MT0nOjzzpnrjNQswHHJgwVKrtGgzwWlflaNua/ZaQnutowbocwmRQ1FaJ0C5tJg0jBe8rBay1sOvzQ=='));
$apiRequest = new \GuzzleHttp\Psr7\Request(
    method: 'GET',
    uri: 'https://example.com/api/items',
    headers: [
        'X-User-Id' => 89,
    ],
);
$apiRequest = $apiRequest->withHeader(
    'X-Signature',
    (string) $apiSigner->sign(new Some\SignableHttpRequest($apiRequest))->encode()->base64(),
);
echo $apiRequest->getHeaderLine('X-Signature');  # here should be the HTTP client call

# Receiver side
$usersPublicKeyMap = [
    89 => base64_decode('bmv2WkJ7raMG6HMJkUNRWidAubSYNIwXvKwWstbDr80='),
];
$userId = $apiRequest->getHeaderLine('X-User-Id');
$apiSigner = new Ed25519DataSigner(publicKey: $usersPublicKeyMap[$userId] ?? throw new \Exception('User not found'));
if ( ! $apiSigner->verify(new Some\SignableHttpRequest($apiRequest), base64_decode($apiRequest->getHeaderLine('X-Signature')))) {
    throw new \Exception('Unauthorized request');
}
echo "Request was authorized and verified for user `{$userId}` and now can be processed.";
```

As this use case has two sides, [use only the basic implementation](#usage) which will generate signature without additional data.
On the other side can be someone who can not use the same implementation of data signer.

This example is build over `libsodium`, so **it can be used across platforms and languages**.


### Signable data transfer object

If you **communicate through data transfer objects**, you can use [a Signable Data interface](./src/SignableDataInterface.php):
```php
use PetrKnap\DataSigner\Some;

$apiClient = new class (new Some\DataSigner()) {
    public function __construct(private readonly Some\DataSigner $dataSigner) {}
    public function put(Some\DataTransferObject $payload): void {
        $request = [
            'payload' => $payload,
            'signature' => (string) $this->dataSigner->sign($payload)->encode()->base64(),
        ];
        echo json_encode($request);  # here should be the API call
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

$passwordResetToken = (string) $signer->withDomain('password_reset')->sign(
    data: 'some_user',
    expiresAt: (new DateTimeImmutable())->modify('+3 hours'),
)->encode(withData: true)->zlib()->base64(urlSafe: true);

$verifiedUserIdentifier = $signer->withDomain('password_reset')->verified(
    (string) Binary::decode($passwordResetToken)->base64()->zlib(),
)->orElseThrow();
echo "Verified user identifier is `{$verifiedUserIdentifier}`.";
```

---

Run `composer require petrknap/data-signer` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
