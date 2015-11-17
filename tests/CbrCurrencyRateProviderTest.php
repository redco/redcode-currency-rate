<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\Provider\CbrCurrencyRateProvider;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;
use RedCode\Currency\Rate\XML\XMLParser;
use RedCode\Currency\Rate\SOAP\SOAPLoader;

class CbrCurrencyRateProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ICurrencyRateManager */
    private $currencyRateManager;

    /** @var ICurrencyManager */
    private $currencyManager;

    /** @var  array */
    private $currencies;

    public function setUp()
    {
        $currencies = [];
        $currencies['RUB'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['RUB']
            ->method('getCode')
            ->willReturn('RUB');

        $currencies['EUR'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['EUR']
            ->method('getCode')
            ->willReturn('EUR');

        $currencies['USD'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['USD']
            ->method('getCode')
            ->willReturn('USD');

        $this->currencyRateManager = $this->getMock('\\RedCode\\Currency\\Rate\\ICurrencyRateManager');
        $this->currencyRateManager
            ->method('getNewInstance')
            ->will($this->returnCallback(
                function (ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $date, $rateValue, $nominal) {
                    $rate = $this->getMock('\\RedCode\\Currency\\Rate\\ICurrencyRate');

                    $rate
                        ->method('getDate')
                        ->willReturn($date);
                    $rate
                        ->method('getRate')
                        ->willReturn($rateValue);
                    $rate
                        ->method('getNominal')
                        ->willReturn($nominal);
                    $rate
                        ->method('getProviderName')
                        ->willReturn($provider->getName());
                    $rate
                        ->method('getCurrency')
                        ->willReturn($currency);
                    return $rate;
                })
            );

        $this->currencyManager = $this->getMock('\\RedCode\\Currency\\ICurrencyManager');
        $this->currencyManager
            ->method('getCurrency')
            ->will($this->returnCallback(function ($name) use ($currencies) {
                $name = strtoupper($name);
                if (isset($currencies[$name])) {
                    return $currencies[$name];
                }
                return null;
            }));
        $this->currencyManager
            ->method('getAll')
            ->will($this->returnCallback(function () use ($currencies) {
                return array_values($currencies);
            }));

        $this->currencies = $currencies;
    }

    public function testCbrCurrencyRateProvider()
    {
        $currencyRateProvider = new CbrCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getSOAPLoaderMock(),
            $this->_getXMLParserMock()
        );

        self::assertInstanceOf(
            '\\RedCode\\Currency\\Rate\\Provider\\CbrCurrencyRateProvider',
            $currencyRateProvider
        );

        $currency = $currencyRateProvider->getBaseCurrency();
        self::assertInstanceOf('\\RedCode\\Currency\\ICurrency', $currency);
        self::assertEquals('RUB', $currency->getCode());
        self::assertEquals('cbr', $currencyRateProvider->getName());
        self::assertEquals(true, $currencyRateProvider->isInversed());
    }

    public function testCbrCurrencyRateProviderGetTodayRates()
    {
        $currencyRateProvider = new CbrCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getSOAPLoaderMock(),
            $this->_getXMLParserMock()
        );

        $rates = $currencyRateProvider->getRates(array_values($this->currencies));

        $this->assertEquals(3, count($rates));
        foreach ($rates as $rate) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }
    }

    public function testCbrCurrencyRateProviderGetYesterdayRates()
    {
        $currencyRateProvider = new CbrCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getSOAPLoaderMock(),
            $this->_getXMLParserMock()
        );

        $rates = $currencyRateProvider->getRates(array_values($this->currencies), new \DateTime('yesterday'));

        $this->assertEquals(3, count($rates));
        foreach ($rates as $rate) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }
    }

    /**
     * @return SOAPLoader
     */
    private function _getSOAPLoaderMock()
    {
        $soapLoader = $this->getMock('\\RedCode\\Currency\\Rate\\SOAP\\SOAPLoader');
        $soapLoader
            ->method('load')
            ->willReturn(false);

        return $soapLoader;
    }

    /**
     * @return XMLParser
     */
    private function _getXMLParserMock()
    {
        $xmlParser = $this->getMock('\\RedCode\\Currency\\Rate\\XML\\XMLParser');
        $xmlParser
            ->method('parse')
            ->willReturn($this->_getSimpleXMLElementMock());

        return $xmlParser;
    }

    private function _getSimpleXMLElementMock()
    {
        $simpleXMLElementMock = $this->getMock('Object', ['xpath']);
        $simpleXMLElementMock
            ->method('xpath')
            ->willReturn([
                (object)[
                    'Vcurs' => 'USD',
                    'Vnom' => '0.47'
                ]
            ]);
        return $simpleXMLElementMock;
    }
}
