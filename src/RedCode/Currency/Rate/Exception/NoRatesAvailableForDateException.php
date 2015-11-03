<?php

namespace RedCode\Currency\Rate\Exception;

use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

class NoRatesAvailableForDateException extends BaseProviderException
{
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
        parent::__construct($provider);
        $this->date = $date;

        $this->message = sprintf('No rates available for %s date with provider %s', $date->format('Y-m-d'), $provider->getName());
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
