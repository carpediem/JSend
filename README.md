JSend
==========

[![Latest Version](https://img.shields.io/github/release/carpediem/jsend.svg?style=flat-square)](https://github.com/carpediem/jsend/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/carpediem/JSend/master.svg?style=flat-square)](https://travis-ci.org/carpediem/JSend)
[![HHVM Status](https://img.shields.io/hhvm/carpediem/jsend.svg?style=flat-square)](http://hhvm.h4cc.de/package/carpediem/jsend)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/carpediem/jsend.svg?style=flat-square)](https://scrutinizer-ci.com/g/carpediem/jsend/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/carpediem/jsend.svg?style=flat-square)](https://scrutinizer-ci.com/g/carpediem/jsend)
[![Total Downloads](https://img.shields.io/packagist/dt/carpediem/jsend.svg?style=flat-square)](https://packagist.org/packages/carpediem/jsend)


JSend is a simple library to ease creation of [JSend compliant](https://labs.omniti.com/labs/jsend) HTTP response.

Highlights
-------

* Simple API
* Immutable Value Object
* Fully documented
* Fully Unit tested
* Framework-agnostic
* Composer ready, [PSR-2] and [PSR-4] compliant

System Requirements
-------

You need **PHP >= 5.5.0** to use `JSend` but the latest stable version of PHP/HHVM is recommended.

Install
-------

Install `JSend` using Composer.

```
$ composer require carpediem/jsend
```

Documentation
-------

Full documentation can be found at [carpediem.github.io/JSend](http://carpediem.github.io/JSend). Contribute to this documentation in the [gh-pages branch](https://github.com/carpediem/jsend/tree/gh-pages)

Testing
-------

`JSend` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/). To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Security
-------

If you discover any security related issues, please email dev@carpediem.fr instead of using the issue tracker.

Credits
-------

- [All Contributors](https://github.com/carpediem/JSend/graphs/contributors)

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
