<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\Provider\CbrCurrencyRateProvider;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

class CbrCurrencyRateProviderTest extends \PHPUnit_Framework_TestCase
{
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
    }

    public function testCbrCurrencyRateProviderGetRates()
    {
        $currencyRateProvider = new CbrCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager
        );
        
        $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\Provider\\CbrCurrencyRateProvider', $currencyRateProvider);

        $currency = $currencyRateProvider->getBaseCurrency();
        $this->assertInstanceOf('\\RedCode\\Currency\\ICurrency', $currency);

        $this->assertEquals('RUB', $currency->getCode());
        $this->assertEquals('cbr', $currencyRateProvider->getName());
        $this->assertEquals(true, $currencyRateProvider->isInversed());

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

        $rates = $currencyRateProvider->getRates(array_values($currencies), new \DateTime('yesterday'));

        $this->assertEquals(2, count($rates));
        foreach($rates as $rate) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }

        $rates = $currencyRateProvider->getRates(array_values($currencies));

        $this->assertEquals(2, count($rates));
        foreach($rates as $rate) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }
    }
}
