<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/27/19
 * Time: 9:34 PM
 */

namespace App\Services;

use App\Exceptions\ResourceNotFoundException;
use App\Models\Transaction;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;

class HexcellClient extends WebDriverHelper
{
    protected $meter;

    /**
     * HexcellClient constructor.
     * @param Meter $meter
     */
    public function __construct(Meter $meter)
    {
        $this->meter = $meter;
        return parent::__construct();
    }

    /**
     * @param $meterCode
     * @param bool $return
     * @return Meter|bool
     * @throws NoSuchElementException
     * @throws ResourceNotFoundException
     * @throws TimeOutException
     */
    public function searchMeter($meterCode, $return = true)
    {
        $this->openPage(config('app.hexcell_credentials.url'));
        $this->waitForPage(HtmlSelectors::$LoginPageTitle);
        $this->fillField(HtmlSelectors::$UsernameField, config('app.hexcell_credentials.username'));
        $this->fillField(HtmlSelectors::$PasswordField, config('app.hexcell_credentials.password'));
        $this->click(HtmlSelectors::$LoginButton);
        $this->waitForPage(HtmlSelectors::$HomePageTitle);

        $this->openPage(config('app.hexcell_credentials.url') . HtmlSelectors::$VendingPageUrl);
        $this->waitForPage(HtmlSelectors::$VendingPageTitle);

        $this->executeJs(sprintf("$('#txtMeterCode').searchbox('setValue', %s);", $meterCode));

        $this->executeJs(HtmlSelectors::$SearchMeterCodeScript);

        try {

            $this->waitForResult($meterCode);

        } catch (TimeOutException $e) {
            // check if we have the no record found pop up
            if ($this->pageContainsText(HtmlSelectors::$NoRecordFoundText)) {
                throw new ResourceNotFoundException(Meter::class, $meterCode);
            }
        }

        // build the meter object form the input fields
        $this->meter->setMeterCode($meterCode);
        $this->meter->setInternalId(md5(uniqid()));
        $this->meter->setAddress($this->getValue(HtmlSelectors::$AddressField));
        $this->meter->setContractId($this->getValue(HtmlSelectors::$ContractIdField));
        $this->meter->setTariff($this->getValue(HtmlSelectors::$TariffField));
        $this->meter->setTariffType($this->getValue(HtmlSelectors::$TariffTypeField));
        $this->meter->setArea($this->getValue(HtmlSelectors::$AreaField));
        $this->meter->setLastVendingDate($this->getValue(HtmlSelectors::$LastVendingDateField));
        $this->meter->setRegistrationDate($this->getValue(HtmlSelectors::$RegistrationDateField));
        $this->meter->setVat($this->getValue(HtmlSelectors::$VATField));
        $this->meter->setMeterType($this->getValue(HtmlSelectors::$MeterTypeField));

        if ($return) {
            $this->webDriver->quit();
            return $this->meter;
        }
        return true;
    }

    /**
     * @param $internalId
     * @param $amount
     * @return Transaction
     */
    public function generateToken($internalId, $amount) : Transaction
    {

    }

}