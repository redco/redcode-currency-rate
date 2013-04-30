<?php

namespace RedCode\Currency\Rate;

use RedCode\Currency\ICurrency;
use RedCode\Currency\Rate\Exception\RateNotFoundException;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\Provider\ICurrencyRateProvider;

/**
 * @author maZahaca
 */
class CurrencyRateManager implements ICurrencyRateManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $currencyRateClassName;

    public function __construct(EntityManager $em, $currencyRateClassName)
    {
        $this->em = $em;
        $this->currencyRateClassName = $currencyRateClassName;
    }

    private static $reflection;

    /**
     * Get reflection class
     *
     * @return \ReflectionClass
     */
    public function reflectionClass()
    {
        return self::$reflection ?: self::$reflection = new \ReflectionClass($this->currencyRateClassName);
    }

    /**
     * @param \RedCode\Currency\ICurrency $currency
     * @param Provider\ICurrencyRateProvider $provider
     * @param \DateTime $date
     * @param float $rate
     * @param float $nominal
     * @return ICurrencyRate
     */
    public function getNewInstance(ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $date, $rate, $nominal)
    {
        return $this->reflectionClass()->newInstance($currency, $provider, $date, $rate, $nominal);
    }

    /**
     * @param \RedCode\Currency\ICurrency $currency
     * @param Provider\ICurrencyRateProvider $provider
     * @param \DateTime $rateDate
     * @return ICurrencyRate
     *
     * @throws RateNotFoundException
     */
    public function getRate(ICurrency $currency, ICurrencyRateProvider $provider, \DateTime $rateDate = null)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('r')
            ->from($this->currencyRateClassName, 'r')
            ->leftJoin('r.currency', 'c')
            ->where($qb->expr()->eq('c.code', ':currency'))
            ->andWhere($qb->expr()->eq('r.providerName', ':provider'))
            ->setParameters(array('currency'=>$currency->getCode(), 'provider'=>$provider->getName()))
            ->orderBy('r.date', 'DESC')
        ;
        if(isset($rateDate)) {
            $qb
                ->andWhere($qb->expr()->eq('r.date', ':date'))
                ->setParameter('date', $rateDate->format('Y-m-d 00:00:00'));
        }
        $result = $qb->getQuery()->getResult();
        $result = reset($result);
        if(!isset($result)) {
            throw new RateNotFoundException($currency, $provider, $rateDate);
        }
        
        return $result;
    }
}
