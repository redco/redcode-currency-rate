<?php

namespace RedCode\Currency\Rate;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
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

    public function __construct(ProviderFactory $providerFactory, ICurrencyRateManager $rateManager, ICurrencyManager $currencyManager)
    {
        $this->providerFactory  = $providerFactory;
        $this->rateManager      = $rateManager;
        $this->currencyManager  = $currencyManager;
    }

    /**
     * Convert currency value
     * @param ICurrency|string $from ICurrency object or currency code
     * @param ICurrency|string $to ICurrency object or currency code
     * @param float $value Value to convert in currency $from
     * @param string|null $provider provider name
     * @param \DateTime|null $rateDate Date for rate (default - today)
     * @throws Exception\ProviderNotFoundException
     * @throws Exception\RateNotFoundException
     * @return float
     */
    public function convert($from, $to, $value, $provider = null, $rateDate = null)
    {
        if(!($from instanceof ICurrency)) {
            $from = $this->currencyManager->getCurrency($from);
        }
        if(!($to instanceof ICurrency)) {
            $to = $this->currencyManager->getCurrency($to);
        }

        $providers = $provider === null ? $this->providerFactory->getAll() : [$this->providerFactory->get($provider)];
        $providers = array_filter($providers);
        if(!count($providers)) {
            throw new ProviderNotFoundException($provider);
        }

        if($rateDate instanceof \DateTime)
        {
            $rateDate->setTime(0, 0, 0);
        }

        $foundValue = null;

        $errorParams = [
            'currency' => null,
            'provider' => null
        ];

        foreach($providers as $provider) {
            /** @var ICurrencyRateProvider $provider */
            if(!$provider->getBaseCurrency()) {
                throw new ProviderNotFoundException($provider->getName());
            }

            if($from->getCode() != $provider->getBaseCurrency()->getCode()) {
                /** @var ICurrencyRate $fromRate  */
                $fromRate = $this->rateManager->getRate($from, $provider, $date);
                if(!$fromRate) {
                    $errorParams['currency'] = $from;
                    $errorParams['provider'] = $provider;
                    continue;
                }

                if(!$provider->isInversed()) {
                    $valueBase = $value / ($fromRate->getRate() *  $fromRate->getNominal());
                }
                else {
                    $valueBase = $fromRate->getRate() / $fromRate->getNominal() * $value;
                }
            }
            else {
                $valueBase = $value;
            }

            if($to->getCode() != $provider->getBaseCurrency()->getCode()) {
                /** @var ICurrencyRate $toRate  */
                $toRate = $this->rateManager->getRate($to, $provider, $rateDate);
                if(!$toRate) {
                    $errorParams['currency'] = $to;
                    $errorParams['provider'] = $provider;
                    continue;
                }
                if(!$provider->isInversed()) {
                    $toRate = $toRate->getNominal() * $toRate->getRate();
                }
                else {
                    $toRate = $toRate->getNominal() / $toRate->getRate();
                }
            }
            else {
                $toRate = 1.0;
            }

            $foundValue = $toRate * $valueBase;

            if($foundValue !== null)
                break;
        }

        if($foundValue === null) {
            throw new RateNotFoundException($errorParams['currency'], $errorParams['provider'], $date);
        }

        return $foundValue;
    }
}
