# Data Signer

This library provides a logic for digital signing and validating of a binary data.

Inputs and outputs are binary data, don't be afraid to use the [`petrknap/binary`](https://github.com/petrknap/php-binary).


## Usage

The **basic use** of [a `DataSigner`](./src/DataSignerInterface.php) is quite simple:
```php
namespace PetrKnap\DataSigner;

$signer = new SomeDataSigner();
$data = 'some data';
$signature = $signer->sign($data);
if ($signer->verify($data, $signature)) {
    echo 'Data was successfully verified by signature.';
}
```

### Domain-specific signing

If you need to **limit the validity** of the signature **to a specific purpose** (domain), just set it to [the `DataSigner`](./src/DataSigner.php):
```php
namespace PetrKnap\DataSigner;

$signer = new SomeDataSigner();
$data = 'some data';
$signature = $signer->withDomain('password_reset')->sign($data);
if (!$signer->withDomain('cookies')->verify($data, $signature)) {
    echo 'You can not use signature generated for `password_reset` in `cookies`.';
}
```


---

Run `composer require petrknap/data-signer` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
