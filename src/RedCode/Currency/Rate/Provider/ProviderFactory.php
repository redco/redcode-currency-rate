<?php

namespace RedCode\Currency\Rate\Provider;

/**
 * @author maZahaca
 */ 
class ProviderFactory
{
    private $providers;

    /**
     * @param ICurrencyRateProvider[] $providers
     * @throws \Exception
     */
    public function __construct($providers)
    {
        if(is_array($providers)) {
            foreach($providers as $provider) {
                if(!($provider instanceof ICurrencyRateProvider)) {
                    throw new \Exception('Provider must be instance of ICurrencyRateProvider');
                }
                $this->providers[$provider->getName()] = $provider;
            }
        }
    }

    /**
     * @param $name
     * @return ICurrencyRateProvider|null
     */
    public function get($name)
    {
        if(isset($this->providers[$name])) {
            return $this->providers[$name];
        }
    }
}
