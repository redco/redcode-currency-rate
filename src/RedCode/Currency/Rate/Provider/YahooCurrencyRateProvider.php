<?php

namespace RedCode\Currency\Rate\Provider;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\ICurrencyRate;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\Exception\NoRatesAvailableForDateException;
use RedCode\Currency\Rate\Exception\BadXMLQueryException;
use RedCode\Currency\Rate\XML\XMLLoader;

class YahooCurrencyRateProvider implements ICurrencyRateProvider
{
    const PROVIDER_NAME = 'yahoo';
    const BASE_URL = 'http://query.yahooapis.com/v1/public/yql';

    /**
     * @var \RedCode\Currency\Rate\ICurrencyRateManager
     */
    private $currencyRateManager;

    /**
     * @var ICurrencyManager
     */
    private $currencyManager;

    /**
     * @var XMLLoader
     */
    private $xmlLoader;

    /**
     * @param ICurrencyRateManager $currencyRateManager
     * @param ICurrencyManager $currencyManager
     * @param XMLLoader $xmlLoader
     */
    public function __construct(
        ICurrencyRateManager $currencyRateManager,
        ICurrencyManager $currencyManager,
        XMLLoader $xmlLoader
    ) {
        $this->currencyRateManager = $currencyRateManager;
        $this->currencyManager = $currencyManager;
        $this->xmlLoader = $xmlLoader;
    }

    /**
     * Load rates by date
     *
     * @param ICurrency[] $currencies
     * @param \DateTime|null $date
     *
     * @return ICurrencyRate[]
     *
     * @throws NoRatesAvailableForDateException
     * @throws BadXMLQueryException
     */
    public function getRates($currencies, \DateTime $date = null)
    {
        $currencyCodes = [];

        foreach ($currencies as $currency) {
            $currencyCodes[] = $currency->getCode() . '=X';
        }

        if (null === $date) {
            $date = new \DateTime();
        }

        $queryData = [
            'q' => 'select * from yahoo.finance.historicaldata where symbol in ("' . implode('","', $currencyCodes) . '") and startDate = "' . $date->format('Y-m-d') . '" and endDate = "' . $date->format('Y-m-d') . '"',
            'env' => 'store://datatables.org/alltableswithkeys',
        ];

        $query = self::BASE_URL . '?' . http_build_query($queryData);
        $ratesXml = $this->xmlLoader->load($query);

        if (false === $ratesXml) {
            throw new BadXMLQueryException($query, $this);
        }
        if (0 === count($ratesXml->results->quote)) {
            throw new NoRatesAvailableForDateException($date, $this);
        }

        $rates = [];
        /** @var \SimpleXMLElement $rate */
        foreach ($ratesXml->results->quote as $quote) {
            $quote = (array)$quote;
            $code = (string)$quote['@attributes']['Symbol'];
            $code = str_replace('%3dX', '', $code);
            $rate = $quote['Close'];

            $rates[$code] = $this->currencyRateManager->getNewInstance(
                $this->currencyManager->getCurrency($code),
                $this,
                $date,
                (float)$rate,
                1
            );
        }

        return $rates;
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
