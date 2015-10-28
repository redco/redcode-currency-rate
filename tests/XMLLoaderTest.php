<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\Rate\Provider\EcbCurrencyRateProvider;
use RedCode\Currency\Rate\XML\XMLLoader;

class XMLLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new XMLLoader();
        $loader->load(EcbCurrencyRateProvider::BASE_URL);
    }

    public function testLoadWithIncorrectUrl()
    {
        $loader = new XMLLoader();

        try {
            $loader->load('http://incorrect_url');
        } catch (\Exception $e) {
            self::assertContains('simplexml_load_file(): php_network_getaddresses:', $e->getMessage());
        }
    }
}
