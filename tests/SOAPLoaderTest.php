<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\Rate\Provider\CbrCurrencyRateProvider;
use RedCode\Currency\Rate\SOAP\SOAPLoader;

class SOAPLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new SOAPLoader();
        $loader->load(CbrCurrencyRateProvider::BASE_URL, new \DateTime());
    }

    public function testLoadWithIncorrectUrl()
    {
        $loader = new SOAPLoader();

        try {
            $loader->load('http://incorrect_url', new \DateTime());
        } catch (\Exception $e) {
            self::assertInstanceOf('\\Exception', $e);
        }
    }
}
