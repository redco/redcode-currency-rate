<?php

namespace RedCode\Currency\Rate\Exception;

/**
 * @author maZahaca
 */
class ProviderNotFoundException extends \Exception
{
    public function __construct($providerName)
    {
        $this->message = sprintf('Provider for name %s not found', $providerName);
    }
}
