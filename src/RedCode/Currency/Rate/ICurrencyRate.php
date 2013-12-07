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
     * @return int
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

    /**
     * Set date of rate
     * @param \DateTime $date
     * @return self
     */
    public function setDate($date);

    /**
     * Set nominal value
     * @param $nominal
     * @return self
     */
    public function setNominal($nominal);

    /**
     * Set rate
     * @param $rate
     * @return self
     */
    public function setRate($rate);

    /**
     * Set currency
     * @param ICurrency $currency
     * @return self
     */
    public function setCurrency(ICurrency $currency);

    /**
     * Set provider name
     * @param $provider
     * @return self
     */
    public function setProviderName($provider);
}
