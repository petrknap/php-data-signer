# Data Signer

This library provides a logic for digital signing and validating of a binary data.

Inputs and outputs are binary data, don't be afraid to use the [`petrknap/binary`](https://github.com/petrknap/php-binary).


## Usage

```php
namespace PetrKnap\DataSigner;

$signer = new SomeDataSigner();

$data = 'some data';
$signature = $signer->sign($data);

if ($signer->verify($data, $signature)) {
    echo 'Data was successfully verified by signature.';
}
```

---

Run `composer require petrknap/data-signer` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
