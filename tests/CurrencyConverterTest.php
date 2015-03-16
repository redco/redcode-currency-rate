<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\CurrencyConverter;
use RedCode\Currency\Rate\Exception\ProviderNotFoundException;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;
use RedCode\Currency\Rate\Provider\ProviderFactory;

class CurrencyConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CurrencyConverter
     */
    private $currencyConverter;

    /**
     * @var string
     */
    private $inversedProviderName = 'testInversedProviderName';

    /**
     * @var string
     */
    private $notInversedProviderName = 'testNotInversedProviderName';

    /**
     * @var string
     */
    private $badProviderName = 'badProviderName';

    /**
     * @var ICurrencyRateManager
     */
    private $currencyRateManager;

    /**
     * @var ICurrencyManager
     */
    private $currencyManager;

    public function setUp()
    {
        $currencies        = [];
        $currencies['RUB'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['RUB']
            ->method('getCode')
            ->willReturn('RUB')
        ;

        $currencies['EUR'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['EUR']
            ->method('getCode')
            ->willReturn('EUR')
        ;

        $currencies['USD'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['USD']
            ->method('getCode')
            ->willReturn('USD')
        ;

        $currencies['GBP'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['GBP']
            ->method('getCode')
            ->willReturn('GBP')
        ;

        $inversedProvider = $this->getMock('\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider');
        $inversedProvider
            ->method('getName')
            ->willReturn($this->inversedProviderName)
        ;
        $inversedProvider
            ->method('getBaseCurrency')
            ->willReturn($currencies['RUB'])
        ;
        $inversedProvider
            ->method('isInversed')
            ->willReturn(true)
        ;
        $notInversedProvider = $this->getMock('\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider');
        $notInversedProvider
            ->method('getName')
            ->willReturn($this->notInversedProviderName)
        ;
        $notInversedProvider
            ->method('getBaseCurrency')
            ->willReturn($currencies['EUR'])
        ;
        $notInversedProvider
            ->method('isInversed')
            ->willReturn(false)
        ;

        $factory = new ProviderFactory([$inversedProvider, $notInversedProvider]);

        $this->currencyRateManager = $this->getMock('\\RedCode\\Currency\\Rate\\ICurrencyRateManager');
        $this->currencyRateManager
            ->method('getNewInstance')
            ->will($this->returnCallback(
                    function (ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $date, $rateValue, $nominal) {
                        $rate = $this->getMock('\\RedCode\\Currency\\Rate\\ICurrencyRate');

                        $rate
                            ->method('getDate')
                            ->willReturn($date)
                        ;
                        $rate
                            ->method('getRate')
                            ->willReturn($rateValue)
                        ;
                        $rate
                            ->method('getNominal')
                            ->willReturn($nominal)
                        ;
                        $rate
                            ->method('getProviderName')
                            ->willReturn($provider->getName())
                        ;
                        $rate
                            ->method('getCurrency')
                            ->willReturn($currency)
                        ;
                        return $rate;
                    })
            )
        ;

        $this->currencyRateManager
            ->method('getRate')
            ->will($this->returnCallback(
                    function (ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $rateDate = null) {
                        switch(true) {
                            case $provider->getName() == $this->inversedProviderName && $currency->getCode() == 'EUR':
                                $rateValue = 40;
                                break;
                            case $provider->getName() == $this->inversedProviderName && $currency->getCode() == 'USD':
                                $rateValue = 30;
                                break;
                            case $provider->getName() == $this->notInversedProviderName && $currency->getCode() == 'RUB':
                                $rateValue = 40;
                                break;
                            case $provider->getName() == $this->notInversedProviderName && $currency->getCode() == 'USD':
                                $rateValue = 1.13;
                                break;
                            default:
                                return null;
                        }


                        $rate = $this->getMock('\\RedCode\\Currency\\Rate\\ICurrencyRate');
                        $rate
                            ->method('getDate')
                            ->willReturn(!empty($rateDate) ? $rateDate : new \DateTime())
                        ;
                        $rate
                            ->method('getRate')
                            ->willReturn($rateValue)
                        ;
                        $rate
                            ->method('getNominal')
                            ->willReturn(1)
                        ;
                        $rate
                            ->method('getProviderName')
                            ->willReturn($provider->getName())
                        ;
                        $rate
                            ->method('getCurrency')
                            ->willReturn($currency)
                        ;

                        return $rate;
                    })
            )
        ;

        $this->currencyManager = $this->getMock('\\RedCode\\Currency\\ICurrencyManager');
        $this->currencyManager
            ->method('getCurrency')
            ->will($this->returnCallback(function ($name) use ($currencies) {
                $name = strtoupper($name);
                if(isset($currencies[$name])) {
                    return $currencies[$name];
                }
                return null;
            }))
        ;
        $this->currencyManager
            ->method('getAll')
            ->will($this->returnCallback(function () use ($currencies) {
                return array_values($currencies);
            }))
        ;

        $this->currencyConverter = new CurrencyConverter(
            $factory,
            $this->currencyRateManager,
            $this->currencyManager
        );

        $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\CurrencyConverter', $this->currencyConverter);
    }

    public function testCurrencyConverterCreate()
    {
        $value = $this->currencyConverter->convert('RUB', 'RUB', 1);
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('USD', 'USD', 1);
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('RUB', 'EUR', 40);
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('RUB', 'EUR', 40, $this->inversedProviderName);
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('RUB', 'EUR', 40, $this->inversedProviderName, new \DateTime());
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('RUB', 'EUR', 40, $this->inversedProviderName, false);
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('EUR', 'RUB', 1);
        $this->assertEquals(40, $value);

        $value = $this->currencyConverter->convert('EUR', 'USD', 1);
        $this->assertEquals(1.33, round($value, 2));

        $value = $this->currencyConverter->convert('RUB', 'EUR', 40, $this->notInversedProviderName);
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('EUR', 'RUB', 1, $this->notInversedProviderName);
        $this->assertEquals(40, $value);

        $value = $this->currencyConverter->convert('EUR', 'USD', 1, $this->notInversedProviderName);
        $this->assertEquals(1.13, $value);
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\ProviderNotFoundException
     */
    public function testCurrencyConverterProviderNotFoundException()
    {
        $this->currencyConverter->convert('RUB', 'EUR', 1, 'wrongProvider');
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\CurrencyNotFoundException
     */
    public function testCurrencyConverterCurrencyToNotFoundException()
    {
        $this->currencyConverter->convert('RUB', 'BYR', 1);
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\CurrencyNotFoundException
     */
    public function testCurrencyConverterCurrencyFromNotFoundException()
    {
        $this->currencyConverter->convert('BYR', 'RUB', 1);
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\RateNotFoundException
     */
    public function testCurrencyConverterCurrencyToRateNotFoundException()
    {
        $this->currencyConverter->convert('RUB', 'GBP', 1);
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\RateNotFoundException
     */
    public function testCurrencyConverterCurrencyFromRateNotFoundException()
    {
        $this->currencyConverter->convert('GBP', 'RUB', 1);
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\ProviderNotFoundException
     */
    public function testCurrencyConverterBadProviderNotFoundException()
    {
        $badProvider = $this->getMock('\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider');
        $badProvider
            ->method('getName')
            ->willReturn($this->badProviderName)
        ;

        $factory            = new ProviderFactory([$badProvider]);
        $currencyConverter  = new CurrencyConverter(
            $factory,
            $this->currencyRateManager,
            $this->currencyManager
        );

        $currencyConverter->convert('GBP', 'RUB', 1, $this->badProviderName);
    }
}

