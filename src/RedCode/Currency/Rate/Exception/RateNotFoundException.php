<?php

namespace RedCode\Currency\Rate\Exception;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

/**
 * @author maZahaca
 */
class RateNotFoundException extends \Exception
{
    /**
     * @var \RedCode\Currency\ICurrency
     */
    protected $currency;

    /**
     * @var \RedCode\Currency\Rate\Provider\ICurrencyRateProvider
     */
    protected $provider;

    /**
     * @var \DateTime
     */
    protected $date;

    public function __construct(ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $date = null)
    {
        $this->currency = $currency;
        $this->date     = $date;
        $this->provider = $provider;
    }

    /**
     * @return \RedCode\Currency\ICurrency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return \RedCode\Currency\Rate\Provider\ICurrencyRateProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
