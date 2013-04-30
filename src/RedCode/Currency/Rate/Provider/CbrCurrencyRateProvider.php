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
    CONST PROVIDER_NAME = 'cbr';

    /**
     * @var \RedCode\Currency\Rate\ICurrencyRateManager
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

        $client = new \SoapClient("http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL");
        $date = $date->format('Y-m-d');
        $curs = $client->GetCursOnDate(array("On_date" => $date));
        $ratesXml = new \SimpleXMLElement($curs->GetCursOnDateResult->any);

        $result = array();
        foreach($currencies as $currency) {
            foreach($ratesXml as $xmlItem) {
                if($currency->getCode() == $xmlItem['code']) {
                    $rate = $this->currencyRateManager->getNewInstance(
                        $this->currencyManager->getCurrency($xmlItem['code']),
                        $this,
                        new \DateTime($xmlItem['date']),
                        (float)$xmlItem['rate'],
                        (float)$xmlItem['nom']
                    );

                    $result[$currency->getCode()] = $rate;
                    break;
                }
            }
        }
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
}
