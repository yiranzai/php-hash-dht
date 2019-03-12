# hash-dht

<p align="center">
  <img src="https://cdn.yiranzai.cn/yiranzai/logo/mouse/mouse.png" alt="" width="20%">
</p>


[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

PHP Distributed Hash Table, Suitable for assisting in finding distributed nodes corresponding to key.

## Install

Via Composer

``` bash
$ composer require yiranzai/hash-dht
```

## Usage

easy

### init

``` php
$hash = new Yiranzai\Dht\Hash();
$hash->addEntityNode('db_server_one')->addEntityNode('db_server_two');
$dbServer =  $hash->getLocation('key_one');
```

### Reuse it

You have to cache it and pass it in the next time you use it.
Or use the `static::cache` I provided.

```php
$hash = new Yiranzai\Dht\Hash();
$hash->addEntityNode('db_server_one')->addEntityNode('db_server_two');
$dbServer =  $hash->getLocation('key_one');
Yiranzai\Dht\Hash::cache($hash->toArray());
$hash = new Yiranzai\Dht\Hash(Yiranzai\Dht\Hash::getCache());
$dbServer =  $hash->getLocation('key_one');
```

### Delete Entity Node

Delete entity node

```php
$hash = new Yiranzai\Dht\Hash();
$hash->deleteEntityNode('db_server_one');
```

### Change algo

default algo is time33, [See more support](SUPPORT_ALGOS.md)

```php
$hash = new Yiranzai\Dht\Hash();
$hash->algo('sha256');

//or

$hash = new Yiranzai\Dht\Hash(['algo' => YOUR_ALGO]);
```

### Change default cache path

Change default cache path

```php
$hash = new Yiranzai\Dht\Hash();
$hash->path(YOUR_PATH);

//or

$hash = new Yiranzai\Dht\Hash(['cachePath' => YOUR_PATH]);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email wuqingdzx@gmail.com instead of using the issue tracker.

## Credits

- [yiranzai][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/yiranzai/hash-dht.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/yiranzai/php-hash-dht/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/yiranzai/php-hash-dht.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/yiranzai/php-hash-dht.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/yiranzai/hash-dht.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/yiranzai/hash-dht
[link-travis]: https://travis-ci.org/yiranzai/php-hash-dht
[link-scrutinizer]: https://scrutinizer-ci.com/g/yiranzai/php-hash-dht/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/yiranzai/php-hash-dht
[link-downloads]: https://packagist.org/packages/yiranzai/hash-dht
[link-author]: https://github.com/yiranzai
[link-contributors]: ../../contributors
