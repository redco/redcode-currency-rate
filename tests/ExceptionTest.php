<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\Rate\Exception\CurrencyNotFoundException;
use RedCode\Currency\Rate\Exception\NoRatesAvailableForDateException;
use RedCode\Currency\Rate\Exception\ProviderNotFoundException;
use RedCode\Currency\Rate\Exception\RateNotFoundException;
use RedCode\Currency\Rate\Exception\BadXMLQueryException;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var string  */
    private $currencyName = 'EUR';

    /** @var string  */
    private $providerName = 'providerName';

    /** @var string  */
    private $query = 'some bad query';

    /**
     * @var ICurrencyRateProvider
     */
    private $currencyRateProvider;

    public function setUp()
    {
        $this->currencyRateProvider = $this->getMock('\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider');
        $this->currencyRateProvider
            ->method('getName')
            ->willReturn('testName');
    }

    public function testCurrencyNotFoundException()
    {
        $e = new CurrencyNotFoundException($this->currencyName);
        $this->assertEquals($this->currencyName, $e->getCurrency());
    }

    public function testProviderNotFoundException()
    {
        $e = new ProviderNotFoundException($this->providerName);
        $this->assertEquals(sprintf('Provider for name %s not found', $this->providerName), $e->getMessage());
    }

    public function testRateNotFoundException()
    {
        $currency = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currency
            ->method('getCode')
            ->willReturn('EUR');

        $currencyRateProvider = $this->getMock('\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider');
        $currencyRateProvider
            ->method('getName')
            ->willReturn('testName');

        $date = new \DateTime();

        $e = new RateNotFoundException($currency, $currencyRateProvider, $date);

        $this->assertEquals($currency, $e->getCurrency());
        $this->assertEquals($date, $e->getDate());
        $this->assertEquals($currencyRateProvider, $e->getProvider());
    }

    public function testBadXMLQueryException()
    {


        $e = new BadXMLQueryException($this->query, $this->currencyRateProvider);
        $this->assertEquals($this->currencyRateProvider, $e->getProvider());
        $this->assertEquals($this->query, $e->getQuery());
    }

    public function testNoRatesAvailableForDateException()
    {
        $date = new \DateTime();

        $e = new NoRatesAvailableForDateException(new \DateTime(), $this->currencyRateProvider);
        $this->assertEquals($this->currencyRateProvider, $e->getProvider());
        $this->assertEquals($date, $e->getDate());
    }
}
