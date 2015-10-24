<?php

namespace RedCode\Currency\Rate\Exception;

class BadXMLQueryException extends \Exception
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @param string $query
     * @param string $providerName
     */
    public function __construct($query, $providerName)
    {
        $this->query = $query;
        $this->providerName = $providerName;

        $this->message = sprintf('Could not create XML from query "%s" for provider %s', $query, $providerName);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }
}
