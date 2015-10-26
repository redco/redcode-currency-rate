<?php

namespace RedCode\Currency\Rate\Exception;

use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

class NoRatesAvailableForDateException extends \Exception
{
    /**
     * @var \RedCode\Currency\Rate\Provider\ICurrencyRateProvider
     */
    protected $provider;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @param \DateTime $date
     * @param ICurrencyRateProvider $provider
     */
    public function __construct(\DateTime $date, ICurrencyRateProvider $provider)
    {
        $this->date = $date;
        $this->provider = $provider;

        $this->message = sprintf('No rates available for %s date with provider %s', $date->format('Y-m-d'), $provider->getName());
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return ICurrencyRateProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
