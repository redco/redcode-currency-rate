<?php


namespace RedCode\Currency\Rate\Provider;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\ICurrencyRate;

/**
 * @author maZahaca
 */
interface ICurrencyRateProvider
{
    /**
     * Load rates by date
     * @param ICurrency[] $currencies
     * @param \DateTime|null $date
     * @return ICurrencyRate[]
     */
    public function getRates($currencies, \DateTime $date = null);

    /**
     * Get base currency of provider
     * @return ICurrency
     */
    public function getBaseCurrency();

    /**
     * Get name of provider
     * @return string
     */
    public function getName();

    /**
     * If rate is direct - return false
     * If rate is inversed - return true
     * @return bool
     */
    public function isInversed();
}