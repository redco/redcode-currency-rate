<?php

namespace RedCode\Currency;

use Doctrine\ORM\EntityManager;

/**
 * @author maZahaca
 */
class CurrencyManager implements ICurrencyManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $currencyClassName;

    public function __construct(EntityManager $em, $currencyClassName)
    {
        $this->em = $em;
        $this->currencyClassName = $currencyClassName;
    }

    /** @inheritdoc */
    public function getCurrency($code)
    {
        return $this->em->getRepository($this->currencyClassName)->findOneBy(array('code' => $code));
    }
}
