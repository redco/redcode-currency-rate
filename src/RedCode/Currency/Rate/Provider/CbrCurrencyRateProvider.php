<?php

namespace RedCode\Currency\Rate\Provider;

use RedCode\Currency\ICurrency;
use RedCode\Currency\ICurrencyManager;
use RedCode\Currency\Rate\ICurrencyRate;
use RedCode\Currency\Rate\ICurrencyRateManager;
use RedCode\Currency\Rate\SOAP\SOAPLoader;
use RedCode\Currency\Rate\XML\XMLParser;

/**
 * @author maZahaca
 */
class CbrCurrencyRateProvider implements ICurrencyRateProvider
{
    const PROVIDER_NAME = 'cbr';
    const BASE_URL = 'http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL';

    /**@var ICurrencyRateManager */
    private $currencyRateManager;

    /**@var ICurrencyManager */
    private $currencyManager;

    /** @var SOAPLoader */
    private $soapLoader;

    /** @var XMLParser */
    private $xmlParser;

    /**
     * @param ICurrencyRateManager $currencyRateManager
     * @param ICurrencyManager $currencyManager
     * @param SOAPLoader $soapLoader
     * @param XMLParser $xmlParser
     */
    public function __construct(
        ICurrencyRateManager $currencyRateManager,
        ICurrencyManager $currencyManager,
        SOAPLoader $soapLoader,
        XMLParser $xmlParser)
    {
        $this->currencyRateManager = $currencyRateManager;
        $this->currencyManager = $currencyManager;
        $this->soapLoader = $soapLoader;
        $this->xmlParser = $xmlParser;
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

        $rawXml = $this->soapLoader->load(self::BASE_URL, $date);
        $ratesXml = $this->xmlParser->parse($rawXml);

        $result = array();
        foreach ($currencies as $currency) {
            $rateCbr = $ratesXml->xpath('ValuteData/ValuteCursOnDate/VchCode[.="' . $currency->getCode() . '"]/parent::*');

            if (null !== $rateCbr) {
                $rate = $this->currencyRateManager->getNewInstance(
                    $this->currencyManager->getCurrency($currency->getCode()),
                    $this,
                    $date,
                    (float)$rateCbr[0]->Vcurs,
                    (int)$rateCbr[0]->Vnom
                );

                $result[$currency->getCode()] = $rate;
            }
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
