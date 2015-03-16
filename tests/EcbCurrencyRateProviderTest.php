<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Provider\EcbCurrencyRateProvider;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

class EcbCurrencyRateProviderTest extends \PHPUnit_Framework_TestCase
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

        $this->currencyRateProvider = new EcbCurrencyRateProvider(
            $currencyRateManager,
            $currencyManager
        );

        $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\Provider\\EcbCurrencyRateProvider', $this->currencyRateProvider);
    }

    public function testEcbCurrencyRateProviderGetRates()
    {
        $currency = $this->currencyRateProvider->getBaseCurrency();
        $this->assertInstanceOf('\\RedCode\\Currency\\ICurrency', $currency);

        $this->assertEquals('EUR', $currency->getCode());
        $this->assertEquals('ecb', $this->currencyRateProvider->getName());
        $this->assertEquals(false, $this->currencyRateProvider->isInversed());

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

        $rates = $this->currencyRateProvider->getRates(array_values($currencies), new \DateTime('today'));

        $this->assertEquals(2, count($rates));
        foreach($rates as $rate) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }

        $rates = $this->currencyRateProvider->getRates(array_values($currencies));

        $this->assertEquals(2, count($rates));
        foreach($rates as $rate) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage ECB service allow load only rates for current date
     */
    public function testEcbCurrencyRateProviderGetRatesYesterday()
    {
        $currencies        = [];
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

        $this->currencyRateProvider->getRates(array_values($currencies), new \DateTime('yesterday'));
    }
}

