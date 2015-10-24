<?php

namespace RedCode\Currency\Rate\Provider;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\Exception\BadXMLQueryException;
use RedCode\Currency\Rate\Exception\NoRatesAvailableForDateException;
use RedCode\Currency\Rate\ICurrencyRate;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\XML\XMLLoader;

/**
 * @author maZahaca
 */
class EcbCurrencyRateProvider implements ICurrencyRateProvider
{
    const PROVIDER_NAME = 'ecb';
    const BASE_URL = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * @var \RedCode\Currency\Rate\ICurrencyRateManager
     */
    private $currencyRateManager;

    /**
     * @var ICurrencyManager
     */
    private $currencyManager;

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
     * @param \DateTime $date
     * @throws \Exception
     * @return ICurrencyRate[]
     */
    public function getRates($currencies, \DateTime $date = null)
    {
        if ($date === null) {
            $date = new \DateTime('now');
        }

        if ($date->format('Y-m-d') !== date('Y-m-d')) {
            throw new NoRatesAvailableForDateException($date, $this);
        }

        $ratesXml = $this->xmlLoader->load(self::BASE_URL);

        if (false === $ratesXml) {
            throw new BadXMLQueryException(self::BASE_URL, $this);
        }

        $result = array();
        foreach ($currencies as $currency) {
            $rate = null;
            foreach ($ratesXml->Cube->Cube->Cube as $ecbRate) {
                if ((string)$ecbRate['currency'] === $currency->getCode()) {
                    $rate = (float)$ecbRate['rate'];
                    break;
                }
            }

            if (!$rate) {
                continue;
            }

            $rate = $this->currencyRateManager->getNewInstance(
                $this->currencyManager->getCurrency($currency->getCode()),
                $this,
                $date,
                $rate,
                1
            );

            $result[$currency->getCode()] = $rate;
        }

        return $result;
    }

    /**
     * Get base currency
     * @return ICurrency
     */
    public function getBaseCurrency()
    {
        return $this->currencyManager->getCurrency('EUR');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::PROVIDER_NAME;
    }

    /**
     * @inheritdoc
     */
    public function isInversed()
    {
        return false;
    }
}
