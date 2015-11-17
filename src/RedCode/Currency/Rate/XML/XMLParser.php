<?php

namespace RedCode\Currency\Rate\XML;

class XMLParser
{
    /**
     * @param $string
     * @return \SimpleXMLElement
     */
    public function parse($string)
    {
        return new \SimpleXMLElement($string);
    }
}
