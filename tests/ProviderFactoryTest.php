<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;
use RedCode\Currency\Rate\Provider\ProviderFactory;

class ProviderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ICurrencyRateProvider
     */
    private $provider;

    /**
     * @var string
     */
    private $providerName       = 'testProviderName';

    CONST PROVIDER_INTERFACE    = '\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider';

    public function setUp()
    {
        $this->provider     = $this->getMock(self::PROVIDER_INTERFACE);
        $this->provider
            ->method('getName')
            ->willReturn($this->providerName)
        ;
    }

    public function testProviderFactoryCreate()
    {
        $factory    = new ProviderFactory([$this->provider]);
        $provider   = $factory->get($this->providerName);

        $this->assertInstanceOf(self::PROVIDER_INTERFACE, $provider);

        $providers = $factory->getAll();
        $this->assertEquals(1, count($providers));

        foreach($providers as $provider) {
            $this->assertInstanceOf('\\RedCode\\Currency\\Rate\\Provider\\ICurrencyRateProvider', $provider);
        }

        $this->assertNull($factory->get('WrongProviderName'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Provider must be instance of ICurrencyRateProvider
     */
    public function testProviderFactoryCreateFailure()
    {
        return new ProviderFactory(['Not an instance of ICurrencyRateProvider']);
    }
}

