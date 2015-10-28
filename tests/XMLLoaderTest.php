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

        libxml_use_internal_errors(true);
        $loader->load('incorrect_url');
        $errors = libxml_get_errors();

        self::assertEquals(1, count($errors));
        self::assertEquals("failed to load external entity \"incorrect_url\"\n", $errors[0]->message);
    }
}
