<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Provider\EcbCurrencyRateProvider;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;
use RedCode\Currency\Rate\XML\XMLLoader;

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
            $currencyManager,
            new XMLLoader()
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
     * @expectedException \RedCode\Currency\Rate\Exception\NoRatesAvailableForDateException
     * @expectedExceptionMessageRegExp #No rates available for ....-..-.. date with provider ecb#
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

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\BadXMLQueryException
     * @expectedExceptionMessageRegExp #Could not create XML from query ".*" for provider ecb#
     */
    public function testEcbCurrencyRateProviderGetRatesWithBadXML()
    {
        $currencyManager = $this->getMock('\\RedCode\\Currency\\ICurrencyManager');
        $currencyRateManager = $this->getMock('\\RedCode\\Currency\\Rate\\ICurrencyRateManager');

        $xmlLoader = $this->getMock('\\RedCode\\Currency\\Rate\\XML\\XMLLoader');
        $xmlLoader
            ->method('load')
            ->willReturn(false);

        $this->currencyRateProvider = new EcbCurrencyRateProvider(
            $currencyRateManager,
            $currencyManager,
            $xmlLoader
        );

        $currencies = [];
        $currencies['EUR'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['EUR']
            ->method('getCode')
            ->willReturn('EUR');

        $this->currencyRateProvider->getRates(array_values($currencies));
    }
}

