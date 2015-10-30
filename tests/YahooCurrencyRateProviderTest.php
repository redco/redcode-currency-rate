<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;
use RedCode\Currency\Rate\Provider\YahooCurrencyRateProvider;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\XML\XMLLoader;

class YahooCurrencyRateProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ICurrencyRateManager */
    private $currencyRateManager;

    /** @var  ICurrencyManager */
    private $currencyManager;

    /** @var  array */
    private $currencies;

    public function setUp()
    {
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

    public function testYahooCurrencyRateProvider()
    {
        $currencyRateProvider = new YahooCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getXMLLoaderMock()
        );

        self::assertInstanceOf(
            '\\RedCode\\Currency\\Rate\\Provider\\YahooCurrencyRateProvider',
            $currencyRateProvider
        );

        $currency = $currencyRateProvider->getBaseCurrency();
        self::assertInstanceOf('\\RedCode\\Currency\\ICurrency', $currency);
        self::assertEquals('USD', $currency->getCode());
        self::assertEquals('yahoo', $currencyRateProvider->getName());
        self::assertEquals(false, $currencyRateProvider->isInversed());
    }

    public function testYahooCurrencyRateProviderGetRates()
    {
        $currencyRateProvider = new YahooCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getXMLLoaderMock()
        );

        $rates = $currencyRateProvider->getRates(array_values($this->currencies), new \DateTime('2015-10-20'));

        self::assertEquals(2, count($rates));
        foreach ($rates as $rate) {
            self::assertInstanceOf('\\RedCode\\Currency\\Rate\\ICurrencyRate', $rate);
        }
    }

    public function testYahooCurrencyRateProviderGetRatesForIncorrectDate()
    {
        $currentDate = new \DateTime('now');
        $estDate = new \DateTime('now', new \DateTimeZone('EST'));

        $currencyRateProvider = new YahooCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            new XMLLoader()
        );

        try {
            $currencyRateProvider->getRates(array_values($this->currencies));
        } catch (\Exception $e) {
            if ((in_array($currentDate->format('w'), ['6', '7'], true)) ||
                ($currentDate->format('Y-m-d') !== $estDate->format('Y-m-d'))
            ) {
                self::assertInstanceOf(
                    '\\RedCode\\Currency\\Rate\\Exception\\NoRatesAvailableForDateException',
                    $e
                );
            }
        }
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\BadXMLQueryException
     * @expectedExceptionMessageRegExp #Could not create XML from query ".*" for provider yahoo#
     */
    public function testYahooCurrencyRateProviderGetRatesWithBadXML()
    {
        $currencyRateProvider = new YahooCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getXMLLoaderMockWithLoadFalse()
        );

        $currencies = [];
        $currencies['EUR'] = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currencies['EUR']
            ->method('getCode')
            ->willReturn('EUR');

        $currencyRateProvider->getRates(array_values($currencies));
    }

    /**
     * @expectedException \RedCode\Currency\Rate\Exception\NoRatesAvailableForDateException
     * @expectedExceptionMessageRegExp #No rates available for ....-..-.. date with provider yahoo#
     */
    public function testYahooCurrencyRateProviderGetRatesWithNullObject()
    {
        $currencyRateProvider = new YahooCurrencyRateProvider(
            $this->currencyRateManager,
            $this->currencyManager,
            $this->_getXMLLoaderMock(true)
        );

        $currencyRateProvider->getRates(array_values($this->currencies), new \DateTime('2015-10-25'));
    }

    /**
     * @param bool|false $empty
     *
     * @return XMLLoader
     */
    private function _getXMLLoaderMock($empty = false)
    {
        $quote = [
            'quote' => [
                [
                    '@attributes' =>
                        ['Symbol' => 'RUB%3dX'],
                    'Close'       => '0.47',
                ],
                [
                    '@attributes' =>
                        ['Symbol' => 'EUR%3dX'],
                    'Close'       => '0.12',
                ],
            ],
        ];

        if (true === $empty) {
            $quote = [
                'quote' => null,
            ];
        }

        $xmlResponse = (object)[
            'results' => (object)$quote,
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