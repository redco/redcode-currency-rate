<?php

namespace RedCode\Currency\Rate\Provider;

class ProviderFactory
{
    private $providers;

    /**
     * @param ICurrencyRateProvider[] $providers
     * @throws \Exception
     */
    public function __construct($providers = [])
    {
        if (is_array($providers)) {
            foreach ($providers as $provider) {
                if (!($provider instanceof ICurrencyRateProvider)) {
                    throw new \Exception('Provider must be instance of ICurrencyRateProvider');
                }
                $this->addProvider($provider);
            }
        }
    }

    /**
     * @param $name
     * @return ICurrencyRateProvider|null
     */
    public function get($name)
    {
        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }
    }

    /**
     * Add a provider to factory
     * @param ICurrencyRateProvider $provider
     * @return $this
     */
    public function addProvider(ICurrencyRateProvider $provider)
    {
        $this->providers[$provider->getName()] = $provider;

        return $this;
    }

    /**
     * @return ICurrencyRateProvider[]
     */
    public function getAll()
    {
        return $this->providers;
    }
}
