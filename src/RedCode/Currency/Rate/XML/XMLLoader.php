<?php

namespace RedCode\Currency\Rate\XML;

class XMLLoader
{
    /**
     * @param $url
     *
     * @return \SimpleXMLElement
     */
    public function load($url)
    {
        return simplexml_load_file($url);
    }
}
