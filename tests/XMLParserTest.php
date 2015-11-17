<?php

namespace RedCode\Currency\Tests;

use RedCode\Currency\Rate\XML\XMLParser;

class XMLParserTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new XMLParser();
        $loader->parse('<?xml version="1.0" encoding="UTF-8"?>
            <note>
                <to>Tove</to>
                <from>Jani</from>
                <heading>Reminder</heading>
                <body>Don\'t forget me this weekend!</body>
            </note>');
    }

    public function testLoadWithIncorrectUrl()
    {
        $loader = new XMLParser();

        try {
            $loader->parse('incorrect_xml');
        } catch (\Exception $e) {
            self::assertInstanceOf('\\Exception', $e);
        }
    }
}
