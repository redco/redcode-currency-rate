<?php

namespace RedCode\Currency\Rate;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

/**
 * @author maZahaca
 */
interface ICurrencyRateManager
{
    /**
     * Create new rate with params
     *
     * @param \RedCode\Currency\ICurrency $currency
     * @param Provider\ICurrencyRateProvider $provider
     * @param \DateTime $date
     * @param float $rate
     * @param float $nominal
     * @return ICurrencyRate
     */
    public function getNewInstance(ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $date, $rate, $nominal);

    /**
     * Get rate by params
     *
     * @param \RedCode\Currency\ICurrency $currency
     * @param Provider\ICurrencyRateProvider $provider
     * @param \DateTime $rateDate
     * @return ICurrencyRate
     */
    public function getRate(ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $rateDate = null);

    /**
     * Save rates
     *
     * @param ICurrencyRate[] $rates
     * @return mixed
     */
    public function saveRates($rates);
}
