<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\CurrencyConverter;
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
    private $providerName = 'testProviderName';

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

        $provider = $this->getMock('\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider');
        $provider
            ->method('getName')
            ->willReturn($this->providerName)
        ;
        $provider
            ->method('getBaseCurrency')
            ->willReturn($currencies['RUB'])
        ;
        $provider
            ->method('isInversed')
            ->willReturn(true)
        ;
        $factory = new ProviderFactory([$provider]);

        $currencyRateManager = $this->getMock('\\RedCode\\Currency\\Rate\\ICurrencyRateManager');
        $currencyRateManager
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

        $currencyRateManager
            ->method('getRate')
            ->will($this->returnCallback(
                    function (ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $rateDate = null) {
                        $rateValue = null;
                        switch($currency->getCode()) {
                            case 'EUR':
                                $rateValue = 40;
                                break;
                            case 'USD':
                                $rateValue = 30;
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

        $currencyManager = $this->getMock('\\RedCode\\Currency\\ICurrencyManager');
        $currencyManager
            ->method('getCurrency')
            ->will($this->returnCallback(function ($name) use ($currencies) {
                $name = strtoupper($name);
                if(isset($currencies[$name])) {
                    return $currencies[$name];
                }
                return null;
            }))
        ;
        $currencyManager
            ->method('getAll')
            ->will($this->returnCallback(function () use ($currencies) {
                return array_values($currencies);
            }))
        ;

        $this->currencyConverter = new CurrencyConverter(
            $factory,
            $currencyRateManager,
            $currencyManager
        );

        $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\CurrencyConverter', $this->currencyConverter);
    }

    public function testCurrencyConverterCreate()
    {
        $value = $this->currencyConverter->convert('RUB', 'EUR', 40);
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('RUB', 'EUR', 40, $this->providerName);
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('RUB', 'EUR', 40, $this->providerName, new \DateTime());
        $this->assertEquals(1, $value);

        $value = $this->currencyConverter->convert('EUR', 'RUB', 1);
        $this->assertEquals(40, $value);

        $value = $this->currencyConverter->convert('EUR', 'USD', 1);
        $this->assertEquals(1.33, round($value, 2));
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\ProviderNotFoundException
     */
    public function testCurrencyConverterCreateProviderNotFound()
    {
        $this->currencyConverter->convert('RUB', 'EUR', 1, 'wrongProvider');
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\CurrencyNotFoundException
     */
    public function testCurrencyConverterCreateCurrencyToNotFound()
    {
        $this->currencyConverter->convert('RUB', 'BYR', 1);
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\CurrencyNotFoundException
     */
    public function testCurrencyConverterCreateCurrencyFromNotFound()
    {
        $this->currencyConverter->convert('BYR', 'RUB', 1);
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\RateNotFoundException
     */
    public function testCurrencyConverterCreateCurrencyRateNotFound()
    {
        $this->currencyConverter->convert('RUB', 'GBP', 1);
    }
}

