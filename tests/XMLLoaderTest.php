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
            $loader->load('incorrect_url');
        } catch (\Exception $e) {
            self::assertEquals('simplexml_load_file(): I/O warning : failed to load external entity "incorrect_url"', $e->getMessage());
        }
    }
}
