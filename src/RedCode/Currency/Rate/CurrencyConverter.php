<?php

namespace RedCode\Currency\Rate;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\Exception\CurrencyNotFoundException;
use RedCode\Currency\Rate\Exception\ProviderNotFoundException;
use RedCode\Currency\Rate\Exception\RateNotFoundException;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;
use RedCode\Currency\Rate\Provider\ProviderFactory;

/**
 * @author maZahaca
 */
class CurrencyConverter
{
    /**
     * @var Provider\ProviderFactory
     */
    private $providerFactory;

    /**
     * @var ICurrencyRateManager
     */
    private $rateManager;

    /**
     * @var ICurrencyManager
     */
    private $currencyManager;

    /**
     * @param ProviderFactory $providerFactory
     * @param ICurrencyRateManager $rateManager
     * @param ICurrencyManager $currencyManager
     */
    public function __construct(ProviderFactory $providerFactory, ICurrencyRateManager $rateManager, ICurrencyManager $currencyManager)
    {
        $this->providerFactory  = $providerFactory;
        $this->rateManager      = $rateManager;
        $this->currencyManager  = $currencyManager;
    }

    /**
     * Convert value in different currency
     * @param ICurrency|string $from ICurrency object or currency code
     * @param ICurrency|string $to ICurrency object or currency code
     * @param float $value value to convert in currency $from
     * @param string|null $provider provider name
     * @param \DateTime|bool|null $rateDate Date for rate (default - today, false - any date)
     * @throws Exception\ProviderNotFoundException
     * @throws Exception\RateNotFoundException
     * @throws Exception\CurrencyNotFoundException
     * @return float
     */
    public function convert($from, $to, $value, $provider = null, $rateDate = null)
    {
        $to         = $this->getCurrency($to);
        $from       = $this->getCurrency($from);
        $providers  = $provider === null ? $this->providerFactory->getAll() : [$this->providerFactory->get($provider)];
        $providers  = array_filter($providers);
        if (!count($providers)) {
            throw new ProviderNotFoundException($provider);
        }

        $date = $rateDate === false ? null : (($rateDate instanceof \DateTime) ? $rateDate : new \DateTime());
        $date && $date->setTime(0, 0, 0);

        $foundValue = null;

        $errorParams = [
            'currency' => null,
            'provider' => null
        ];

        foreach ($providers as $provider) {
            /** @var ICurrencyRateProvider $provider */
            if (!$provider->getBaseCurrency()) {
                throw new ProviderNotFoundException($provider->getName());
            }

            $valueBase = $this->getValueBase($value, $from, $provider, $date);
            if ($valueBase === null) {
                $errorParams['currency'] = $from;
                $errorParams['provider'] = $provider;
                continue;
            }

            $toRate = $this->getRate($to, $provider, $date);
            if ($toRate === null) {
                $errorParams['currency'] = $to;
                $errorParams['provider'] = $provider;
                continue;
            }

            $foundValue = $toRate * $valueBase;

            if ($foundValue !== null) {
                break;
            }
        }

        if ($foundValue === null) {
            throw new RateNotFoundException($errorParams['currency'], $errorParams['provider'], $date);
        }

        return $foundValue;
    }

    /**
     * Extract currency from code or object
     *
     * @param string|ICurrency $currency
     * @return ICurrency
     * @throws CurrencyNotFoundException
     */
    private function getCurrency($currency)
    {
        if (!($currency instanceof ICurrency)) {
            $code = $currency;
            $currency = $this->currencyManager->getCurrency($code);
            if (!($currency instanceof ICurrency)) {
                throw new CurrencyNotFoundException($code);
            }
        }
        return $currency;
    }

    /**
     * Get base value
     *
     * @param float $value
     * @param ICurrency $currency
     * @param ICurrencyRateProvider $provider
     * @param \DateTime|null $date
     * @return float|null
     */
    private function getValueBase($value, ICurrency $currency, ICurrencyRateProvider $provider, $date = null)
    {
        $valueBase = $value;
        if ($currency->getCode() != $provider->getBaseCurrency()->getCode()) {
            $rate = $this->rateManager->getRate($currency, $provider, $date);
            if (!$rate) {
                return null;
            }

            if (!$provider->isInversed()) {
                $valueBase = $value / ($rate->getRate() * $rate->getNominal());
            } else {
                $valueBase = $rate->getRate() / $rate->getNominal() * $value;
            }
        }

        return $valueBase;
    }

    /**
     * Get currency rate
     *
     * @param ICurrency $currency
     * @param ICurrencyRateProvider $provider
     * @param null $date
     * @return float
     */
    private function getRate(ICurrency $currency, ICurrencyRateProvider $provider, $date = null)
    {
        if ($currency->getCode() != $provider->getBaseCurrency()->getCode()) {
            $rate = $this->rateManager->getRate($currency, $provider, $date);
            if (!$rate) {
                return null;
            }
            if (!$provider->isInversed()) {
                $rate = $rate->getNominal() * $rate->getRate();
            } else {
                $rate = $rate->getNominal() / $rate->getRate();
            }
        } else {
            $rate = 1.0;
        }

        return $rate;
    }
}
