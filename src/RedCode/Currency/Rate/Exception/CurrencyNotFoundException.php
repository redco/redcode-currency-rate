<?php

namespace RedCode\Currency\Rate\Exception;

/**
 * @author maZahaca
 */
class CurrencyNotFoundException extends \Exception
{
    /**
     * @var string
     */
    protected $currency;

    public function __construct($currency)
    {
        $this->currency = $currency;
        $this->message = sprintf('Requested currency %s is not found', $currency);
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
