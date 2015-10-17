<?php

namespace RedCode\Currency\Rate\Provider;

use GuzzleHttp\Client;
use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\ICurrencyRate;
use RedCode\Currency\Rate\ICurrencyRateManager;

class YahooCurrencyRateProvider implements ICurrencyRateProvider
{
    const PROVIDER_NAME = 'yahoo';

    public function __construct(ICurrencyRateManager $currencyRateManager, ICurrencyManager $currencyManager)
    {
        $this->currencyRateManager  = $currencyRateManager;
        $this->currencyManager      = $currencyManager;
    }

    /**
     * Load rates by date
     *
     * @param ICurrency[] $currencies
     * @param \DateTime|null $date
     *
     * @return ICurrencyRate[]
     */
    public function getRates($currencies, \DateTime $date = null)
    {
        $currencyCodes = [];

        foreach ($currencies as $currency) {
            $currencyCodes[] = $currency->getCode();
        }

        if (null === $date) {
            $date = new \DateTime();
            $results = $this->_processActualRequest($currencyCodes);
        } else {
            $results = $this->_processHistoricalRequest($currencyCodes, $date);
        }

        $rates = [];
        foreach ($results as $code => $rate) {
            $rates[$code] = $this->currencyRateManager->getNewInstance(
                $this->currencyManager->getCurrency($code),
                $this,
                $date,
                $rate,
                1
            );
        }

        return $rates;
    }

    /**
     * @param $currencyCodes
     *
     * @return array
     */
    private function _processActualRequest($currencyCodes)
    {
        $baseUrl =  'http://query.yahooapis.com';
        $requestString = '/v1/public/yql?q=';
        $query = 'select * from yahoo.finance.xchange where pair in ("'.implode('","', $currencyCodes).'")';
        $query .= '&env=store://datatables.org/alltableswithkeys';
        $query = str_replace(' ', '%20', $query);

        $client = new Client();
        $response = $client->request('GET', $baseUrl . $requestString . $query);

        $string = (string) $response->getBody();

        $ratesXml = new \SimpleXMLElement($string);

        $currencies = [];

        /** @var \SimpleXMLElement $rate */
        foreach ($ratesXml->results->rate as $rate) {
            $id = (string)$rate->attributes()['id'];
            $id = str_replace('=X', '', $id);

            $currencies[$id] = (string)$rate->Rate;
        }

        return $currencies;
    }

    /**
     * @param $currencyCodes
     * @param \DateTime $date
     *
     * @return array
     */
    private function _processHistoricalRequest($currencyCodes, \DateTime $date)
    {
        $url = 'http://finance.yahoo.com/connection/currency-converter-cache';
        $query = '?date=' . $date->format('Ymd');

        $client = new Client();
        $response = $client->request('GET', $url . $query);

        $jsonResponse = (string)$response->getBody();
        $jsonResponse = str_replace([
            '/**/YAHOO.Finance.CurrencyConverter.addConversionRates(',
            ');'
        ], '', $jsonResponse);

        $parsedResponse = json_decode($jsonResponse);

        $currencies = [];
        foreach ($parsedResponse->list->resources as $resource) {
            $fields = $resource->resource->fields;

            $symbol = str_replace('=X', '', $fields->symbol);

            if (in_array($symbol, $currencyCodes, true)) {
                $currencies[$symbol] = $fields->price;
            }
        }

        return $currencies;
    }

    /**
     * Get base currency of provider
     * @return ICurrency
     */
    public function getBaseCurrency()
    {
        return $this->currencyManager->getCurrency('USD');
    }

    /**
     * Get name of provider
     * @return string
     */
    public function getName()
    {
        return self::PROVIDER_NAME;
    }

    /**
     * If rate is direct - return false
     * If rate is inversed - return true
     * @return bool
     */
    public function isInversed()
    {
        return false;
    }
}
