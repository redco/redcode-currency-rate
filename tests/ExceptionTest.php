<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\Rate\Exception\CurrencyNotFoundException;
use RedCode\Currency\Rate\Exception\NoRatesAvailableForDateException;
use RedCode\Currency\Rate\Exception\ProviderNotFoundException;
use RedCode\Currency\Rate\Exception\RateNotFoundException;
use RedCode\Currency\Rate\Exception\BadXMLQueryException;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    private $currencyName   = 'EUR';
    private $providerName   = 'providerName';
    private $query = 'some bad query';

    public function testCurrencyNotFoundException()
    {
        $e              = new CurrencyNotFoundException($this->currencyName);
        $this->assertEquals($this->currencyName, $e->getCurrency());
    }

    public function testProviderNotFoundException()
    {
        $e              = new ProviderNotFoundException($this->providerName);
        $this->assertEquals(sprintf('Provider for name %s not found', $this->providerName), $e->getMessage());
    }

    public function testRateNotFoundException()
    {
        $currency = $this->getMock('\\RedCode\\Currency\\ICurrency');
        $currency
            ->method('getCode')
            ->willReturn($this->currencyName)
        ;

        $provider = $this->getMock('\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider');
        $provider
            ->method('getName')
            ->willReturn($this->providerName);
    }

    public function testBadXMLQueryException()
    {
        $e = new BadXMLQueryException($this->query, $this->providerName);
        $this->assertEquals($this->providerName, $e->getProviderName());
        $this->assertEquals($this->query, $e->getQuery());
    }

    public function NoRatesAvailableForDateException()
    {
        $date = new \DateTime();
        $e = new NoRatesAvailableForDateException(new \DateTime(), $this->providerName);
        $this->assertEquals($this->providerName, $e->getProviderName());
        $this->assertEquals($date, $e->getDate());
    }
}
