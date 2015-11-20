<?php

namespace RedCode\Currency\Rate\SOAP;

class SOAPLoader
{
    /**
     * @param $url
     * @param \DateTime $date
     * @return mixed
     */
    public function load($url, \DateTime $date)
    {
        $client = new \SoapClient($url);
        $curs = $client->GetCursOnDate(["On_date" => $date->format('Y-m-d')]);

        return $curs->GetCursOnDateResult->any;
    }
}
