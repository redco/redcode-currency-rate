<?php

namespace RedCode\Currency\Rate\Exception;

use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

abstract class BaseProviderException extends \Exception
{
    /**
     * @var \RedCode\Currency\Rate\Provider\ICurrencyRateProvider
     */
    protected $provider;

    /**
     * @return ICurrencyRateProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
