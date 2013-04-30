<?php

namespace RedCode\Currency\Rate;

use RedCode\Currency\ICurrency;

/**
 * @author maZahaca
 */
interface ICurrencyRate
{
    /**
     * Return rate date
     * @return \DateTime
     */
    public function getDate();

    /**
     * Return currency nominal relative base currency
     * @return float
     */
    public function getNominal();

    /**
     * Return currency rate
     * @return float
     */
    public function getRate();

    /**
     * Get currency of rate
     * @return ICurrency
     */
    public function getCurrency();

    /**
     * Get rate provider name
     * @return string
     */
    public function getProviderName();
}
