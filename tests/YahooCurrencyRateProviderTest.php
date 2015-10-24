<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;
use RedCode\Currency\Rate\Provider\YahooCurrencyRateProvider;

class YahooCurrencyRateProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ICurrencyRateProvider
     */
    private $currencyRateProvider;

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

        $currencyManager = $this->getMock('\\RedCode\\Currency\\ICurrencyManager');
        $currencyManager
            ->method('getCurrency')
            ->will($this->returnCallback(function ($name) use ($currencies) {
                $name = strtoupper($name);
                if(array_key_exists($name, $currencies)) {
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

        $this->currencyRateProvider = new YahooCurrencyRateProvider(
            $currencyRateManager,
            $currencyManager
        );

        $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\Provider\\YahooCurrencyRateProvider', $this->currencyRateProvider);
    }

    public function testYahooCurrencyRateProviderGetRates()
    {
        $currency = $this->currencyRateProvider->getBaseCurrency();
        $this->assertInstanceOf('\\RedCode\\Currency\\ICurrency', $currency);

        $this->assertEquals('USD', $currency->getCode());
        $this->assertEquals('yahoo', $this->currencyRateProvider->getName());
        $this->assertEquals(false, $this->currencyRateProvider->isInversed());

        $currencies        = [];
        $currencies['EUR'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['EUR']
            ->method('getCode')
            ->willReturn('EUR')
        ;

        $currencies['RUB'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['RUB']
            ->method('getCode')
            ->willReturn('RUB')
        ;

        $rates = $this->currencyRateProvider->getRates(array_values($currencies), new \DateTime('yesterday'));

        $this->assertEquals(2, count($rates));
        foreach($rates as $rate) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }

        $rates = $this->currencyRateProvider->getRates(array_values($currencies), new \DateTime('2015-10-20'));

        $this->assertEquals(2, count($rates));
        foreach($rates as $rate) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }
    }

    public function testYahooCurrencyRateProviderGetRatesForVacation()
    {
        $currencies        = [];
        $currencies['EUR'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['EUR']
            ->method('getCode')
            ->willReturn('EUR')
        ;

        $currencies['RUB'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['RUB']
            ->method('getCode')
            ->willReturn('RUB')
        ;
        $rates = $this->currencyRateProvider->getRates(array_values($currencies));
    }
}