<?php

namespace RedCode\Currency;

/**
 * @author maZahaca
 */
interface ICurrency
{
    /**
     * Get 3 symbols currency code
     * @return string
     */
    public function getCode();

    /**
     * Id of currency
     * @return mixed
     */
    public function getId();
}