<?php

namespace RedCode\Currency\Rate;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Exception\RateNotFoundException;
use RedCode\Currency\Rate\Provider\ProviderFactory;
use Symfony\Component\Validator\Constraints\DateTime;

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

    public function __construct(ProviderFactory $providerFactory, ICurrencyRateManager $rateManager)
    {
        $this->providerFactory  = $providerFactory;
        $this->rateManager      = $rateManager;
    }

    /**
     * Convert currency value
     * @param ICurrency $from
     * @param ICurrency $to
     * @param float $value
     * @param string $providerName
     * @throws \Exception
     * @throws RateNotFoundException
     * @return float
     */
    public function convert(ICurrency $from, ICurrency $to, $value, $providerName = 'cbr')
    {
        $provider = $this->providerFactory->get($providerName);
        if(!$provider) {
            throw new \Exception("CurrencyRateProvider for name {$providerName} not found");
        }

        $date = new \DateTime();
        $date->setTime(0, 0, 0);

        /** @var $fromRate  */
        $fromRate = $this->rateManager->getRate($from, $provider, $date);
        if(!$fromRate) {
            throw new RateNotFoundException($from, $provider, $date);
        }

        $valueBase = $fromRate->getRate() / $fromRate->getNominal() * $value;

        if($to->getCode() == $provider->getBaseCurrency()->getCode()) {
            return $valueBase;
        }

        $toRate = $this->rateManager->getRate($to, $provider, $date);
        if(!$toRate) {
            throw new RateNotFoundException($to, $provider, $date);
        }

        return $toRate->getNominal() / $toRate->getRate() * $valueBase;
    }
}
