<?php

namespace RedCode\Currency\Rate\Exception;

use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

class BadXMLQueryException extends \Exception
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @var \RedCode\Currency\Rate\Provider\ICurrencyRateProvider
     */
    protected $provider;

    /**
     * @param string $query
     * @param ICurrencyRateProvider $provider
     */
    public function __construct($query, ICurrencyRateProvider $provider)
    {
        $this->query = $query;
        $this->provider = $provider;

        $this->message = sprintf('Could not create XML from query "%s" for provider %s', $query, $provider->getName());
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return ICurrencyRateProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
