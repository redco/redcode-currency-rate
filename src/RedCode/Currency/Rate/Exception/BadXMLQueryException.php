<?php

namespace RedCode\Currency\Rate\Exception;

use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

class BadXMLQueryException extends BaseProviderException
{
    /**
     * @var string
     */
    private $query;

    /**
     * @param string $query
     * @param ICurrencyRateProvider $provider
     */
    public function __construct($query, ICurrencyRateProvider $provider)
    {
        parent::__construct($provider);
        $this->query = $query;

        $this->message = sprintf('Could not create XML from query "%s" for provider %s', $query, $provider->getName());
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }
}
