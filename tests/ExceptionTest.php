<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\Rate\Exception\CurrencyNotFoundException;
use RedCode\Currency\Rate\Exception\ProviderNotFoundException;
use RedCode\Currency\Rate\Exception\RateNotFoundException;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    private $currencyName   = 'EUR';
    private $providerName   = 'providerName';

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
            ->willReturn($this->providerName)
        ;

        $date           = new \DateTime();
        $e              = new RateNotFoundException($currency, $provider, $date);

        $this->assertEquals($this->providerName, $e->getProvider()->getName());
        $this->assertEquals($this->currencyName, $e->getCurrency()->getCode());
        $this->assertEquals($date, $e->getDate());
    }
}
