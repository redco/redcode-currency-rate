<?php

namespace RedCode\Currency\Rate\Exception;

class NoRatesAvailableForDateException extends \Exception
{
    /**
     * @var \RedCode\Currency\ICurrency
     */
    protected $currency;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var \DateTime
     */
    protected $date;

    public function __construct(\DateTime $date, $providerName)
    {
        $this->date = $date;
        $this->providerName = $providerName;

        $this->message = sprintf('No rates available for %s date with provider %s', $date->format('Y-m-d'), $providerName);
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getProviderName()
    {
        return $this->providerName;
    }
}
