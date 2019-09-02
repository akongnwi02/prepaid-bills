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
     * @return Meter
     * @throws ResourceNotFoundException
     */
    public function searchMeter($meterCode)
    {
        $hexcellUrl = config('app.hexcell_credentials.url');
        $username = config('app.hexcell_credentials.username');
        $password = config('app.hexcell_credentials.password');

        $this->openPage($hexcellUrl . HtmlSelectors::$LoginUrl . "?id=$username&pwd=$password");

        $this->openPage($hexcellUrl . HtmlSelectors::$MeterSearchUrl . "?id=$meterCode&nflag=1");
        $this->webDriver->takeScreenshot('abc.jpg');
        $response = json_decode($this->getText(HtmlSelectors::$BodyElement));

        if (empty($response)) {
            throw new ResourceNotFoundException(Meter::class, $meterCode);
        }

        $searchResult = $response[0];
        // build the meter object form the input fields
        $this->meter->setMeterCode($searchResult->FIDCode);
        $this->meter->setInternalId(md5(uniqid()));
        $this->meter->setAddress($searchResult->FAddr);
        $this->meter->setContractId($searchResult->FID);
        $this->meter->setTariff($searchResult->FPrice);
        $this->meter->setTariffType($searchResult->FUseTypeName);
        $this->meter->setArea($searchResult->FAreaName);
        $this->meter->setLastVendingDate($searchResult->FLastVendingDT);
        $this->meter->setRegistrationDate($searchResult->FRegDatetime);
        $this->meter->setVat($searchResult->FVAT);
        $this->meter->setMeterType($searchResult->FMeterTypeName);

        return $this->meter;
    }

    /**
     * @param Transaction $transaction
     * @return Transaction
     */
    public function generateToken(Transaction $transaction) : Transaction
    {
        $hexcellUrl = config('app.hexcell_credentials.url');
        $username = config('app.hexcell_credentials.username');
        $password = config('app.hexcell_credentials.password');

        $this->openPage($hexcellUrl . HtmlSelectors::$LoginUrl . "?id=$username&pwd=$password");


    }

}