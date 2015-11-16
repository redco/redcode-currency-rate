# Currency Rates converter library [![Latest Stable Version](https://img.shields.io/packagist/v/redcode/currency-rate.svg?style=flat)](https://packagist.org/packages/redcode/currency-rate) [![Total Downloads](https://img.shields.io/packagist/dt/redcode/currency-rate.svg?style=flat)](https://packagist.org/packages/redcode/currency-rate)

[![Build Status](https://img.shields.io/travis/redco/redcode-currency-rate.svg?style=flat)](https://travis-ci.org/redco/redcode-currency-rate)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/redco/redcode-currency-rate.svg?style=flat)](https://scrutinizer-ci.com/g/redco/redcode-currency-rate/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/redco/redcode-currency-rate.svg?style=flat)](https://scrutinizer-ci.com/g/redco/redcode-currency-rate/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b77d142f-47eb-4e30-81fb-ed56bff5e5bf/mini.png)](https://insight.sensiolabs.com/projects/b77d142f-47eb-4e30-81fb-ed56bff5e5bf)
[![Dependency Status](https://www.versioneye.com/user/projects/5505aebb4a1064727700051d/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5505aebb4a1064727700051d)

This library shows an efficient way to work with currencies and currency rates form [Central Bank of Russia](http://www.cbr.ru/eng/), [European Central Bank](https://www.ecb.europa.eu), and [Yahoo Finance](https://finance.yahoo.com/currency-converter/). It contains base classes and interfaces which hide logic about loading currency rates, one of the most useful implementation is [Symfony2 bundle](https://github.com/redco/redcode-currency-rate-bundle).

## Installing

### Composer
You can easily install it with [composer](https://getcomposer.org) by command:
```
composer require redcode/currency-rate
```

## Documentation

First of all you need to implement services [ICurrencyRateManager](https://github.com/redco/redcode-currency-rate-bundle/blob/master/Manager/CurrencyRateManager.php), [ICurrencyManager](https://github.com/redco/redcode-currency-rate-bundle/blob/master/Manager/CurrencyManager.php). Then DTO or Entity objects  [Currency](https://github.com/redco/redcode-currency-rate-bundle/blob/master/Model/Currency.php) and [CurrencyRate](https://github.com/redco/redcode-currency-rate-bundle/blob/master/Model/CurrencyRate.php).

After that create and configure currencyConverter:
```php
use RedCode\Currency\Rate;

// we have initialized $currencyRateManager and $currencyManager

$providerFactory = new Provider\ProviderFactory();
$providerFactory->addProvider(
  new Provider\EcbCurrencyRateProvider`(
    $currencyRateManager, 
    $currencyManager
  )
);

$converter = new CurrencyConverter(
  $providerFactory, 
  $currencyRateManager, 
  $currencyManager
);

$convertedValue = $converter->convert('USD', 'GBP', 100);
```

## Tests
To run [tests](https://github.com/redco/redcode-currency-rate/tree/master/tests) use command below:
```shell
./tests/runTests.sh
```

## Contribute

Pull requests are welcome. Please see our [CONTRIBUTING](https://github.com/redco/redcode-currency-rate/blob/master/CONTRIBUTING.md) guide.
