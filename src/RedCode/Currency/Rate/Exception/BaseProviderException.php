<?php

namespace RedCode\Currency\Rate\Exception;

use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

abstract class BaseProviderException extends \Exception
{
    /**
     * @var \RedCode\Currency\Rate\Provider\ICurrencyRateProvider
     */
    private $provider;

    /**
     * @param ICurrencyRateProvider $provider
     */
    public function __construct(ICurrencyRateProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return ICurrencyRateProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
