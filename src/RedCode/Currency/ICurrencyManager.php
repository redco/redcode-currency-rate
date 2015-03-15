<?php

namespace RedCode\Currency;

/**
 * @author maZahaca
 */
interface ICurrencyManager
{
    /**
     * Get currency by 3 symbol code
     * @param string $code
     * @return ICurrency
     */
    public function getCurrency($code);

    /**
     * Get active currency
     * @return ICurrency[]
     */
    public function getAll();
}
