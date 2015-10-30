<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Provider\EcbCurrencyRateProvider;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\XML\XMLLoader;

class EcbCurrencyRateProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ICurrencyRateManager */
    private $currencyRateManager;

    /** @var  ICurrencyManager */
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


        $currencyRateManager = $this->getMock('\\RedCode\\Currency\\Rate\\ICurrencyRateManager');
        $currencyRateManager
            ->method('getNewInstance')
            ->will(
                self::returnCallback(
                    function (
                        ICurrency $currency,
                        ICurrencyRateProvider $provider,
                        \DateTime $date,
                        $rateValue,
                        $nominal
                    ) {
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
                    }
                )
            );

        $currencyManager = $this->getMock('\\RedCode\\Currency\\ICurrencyManager');
        $currencyManager
            ->method('getCurrency')
            ->will(
                self::returnCallback(
                    function ($name) use ($currencies) {
                        $name = strtoupper($name);
                        if (array_key_exists($name, $currencies)) {
                            return $currencies[$name];
                        }

                        return null;
                    }
                )
            );
        $currencyManager
            ->method('getAll')
            ->will(
                self::returnCallback(
                    function () use ($currencies) {
                        return array_values($currencies);
                    }
                )
            );

        $this->currencyRateManager = $currencyRateManager;
        $this->currencyManager = $currencyManager;
        $this->currencies = $currencies;
    }

    public function testEcbCurrencyRateProvider()
    {
        $currencyRateProvider = new EcbCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getXMLLoaderMock()
        );

        self::assertInstanceOf(
            '\\RedCode\\Currency\\Rate\\Provider\\EcbCurrencyRateProvider',
            $currencyRateProvider
        );

        $currency = $currencyRateProvider->getBaseCurrency();
        self::assertInstanceOf('\\RedCode\\Currency\\ICurrency', $currency);
        self::assertEquals('EUR', $currency->getCode());
        self::assertEquals('ecb', $currencyRateProvider->getName());
        self::assertEquals(false, $currencyRateProvider->isInversed());
    }

    public function testEcbCurrencyRateProviderGetRates()
    {
        $currencyRateProvider = new EcbCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            new XMLLoader()
        );

        $rates = $currencyRateProvider->getRates(array_values($this->currencies), new \DateTime('today'));

        self::assertEquals(2, count($rates));
        foreach ($rates as $rate) {
            self::assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\NoRatesAvailableForDateException
     * @expectedExceptionMessageRegExp #No rates available for ....-..-.. date with provider ecb#
     */
    public function testEcbCurrencyRateProviderGetRatesYesterday()
    {
        $currencyRateProvider = new EcbCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getXMLLoaderMock()
        );

        $currencyRateProvider->getRates(array_values($this->currencies), new \DateTime('yesterday'));
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\BadXMLQueryException
     * @expectedExceptionMessageRegExp #Could not create XML from query ".*" for provider ecb#
     */
    public function testEcbCurrencyRateProviderGetRatesWithBadXML()
    {
        $currencyRateProvider = new EcbCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getXMLLoaderMockWithLoadFalse()
        );

        $currencyRateProvider->getRates(array_values($this->currencies));
    }

    /**
     * @return XMLLoader
     */
    private function _getXMLLoaderMock()
    {
        $xmlResponse = (object)[
            'Cube' => (object)[
                'Cube' => (object)[
                    'Cube' => [
                        [
                            'currency' => 'RUB',
                            'rate'     => '0.71820',
                        ],
                        [
                            'currency' => 'USD',
                            'rate'     => '1.1017',
                        ],
                    ],
                ],
            ],
        ];

        $xmlLoader = $this->getMock('\\RedCode\\Currency\\Rate\\XML\\XMLLoader');
        $xmlLoader
            ->method('load')
            ->willReturn($xmlResponse);

        return $xmlLoader;
    }

    /**
     * @return XMLLoader
     */
    private function _getXMLLoaderMockWithLoadFalse()
    {
        $xmlLoader = $this->getMock('\\RedCode\\Currency\\Rate\\XML\\XMLLoader');
        $xmlLoader
            ->method('load')
            ->willReturn(false);

        return $xmlLoader;
    }
}

