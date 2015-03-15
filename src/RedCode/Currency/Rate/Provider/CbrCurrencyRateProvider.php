<?php

namespace RedCode\Currency\Rate\Provider;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\ICurrencyRate;
use RedCode\Currency\Rate\ICurrencyRateManager;

/**
 * @author maZahaca
 */
class CbrCurrencyRateProvider implements ICurrencyRateProvider
{
    const PROVIDER_NAME = 'cbr';

    /**
     * @var ICurrencyRateManager
     */
    private $currencyRateManager;

    /**
     * @var ICurrencyManager
     */
    private $currencyManager;

    public function __construct(ICurrencyRateManager $currencyRateManager, ICurrencyManager $currencyManager)
    {
        $this->currencyRateManager  = $currencyRateManager;
        $this->currencyManager      = $currencyManager;
    }

    /**
     * Load rates by date
     *
     * @param ICurrency[] $currencies
     * @param \DateTime $date
     * @return ICurrencyRate[]
     */
    public function getRates($currencies, \DateTime $date = null)
    {
        if ($date === null) {
            $date = new \DateTime('now');
        }

        $client     = new \SoapClient("http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL");
        $curs       = $client->GetCursOnDate(array("On_date" => $date->format('Y-m-d')));
        $ratesXml   = new \SimpleXMLElement($curs->GetCursOnDateResult->any);

        $result = array();
        foreach ($currencies as $currency) {
            $rateCbr = $ratesXml->xpath('ValuteData/ValuteCursOnDate/VchCode[.="'.$currency->getCode().'"]/parent::*');
            if (!$rateCbr) {
                continue;
            }

            $rate = $this->currencyRateManager->getNewInstance(
                $this->currencyManager->getCurrency($currency->getCode()),
                $this,
                $date,
                (float)$rateCbr[0]->Vcurs,
                (int)$rateCbr[0]->Vnom
            );

            $result[$currency->getCode()] = $rate;
        }
        return $result;
    }

    /**
     * Get base currency
     * @return ICurrency
     */
    public function getBaseCurrency()
    {
        return $this->currencyManager->getCurrency('RUB');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::PROVIDER_NAME;
    }

    /**
     * @inheritdoc
     */
    public function isInversed()
    {
        return true;
    }
}
