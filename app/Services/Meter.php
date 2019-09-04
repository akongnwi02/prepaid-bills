<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/27/19
 * Time: 9:07 PM
 */

namespace App\Services;

class Meter
{
    /**
     * code of the meter
     */
    private $meterCode;

    /**
     * Price per unit quantity
     */
    private $tariff;

    /**
     * Price type
     */
    private $tariffType;

    /**
     * Unique identifier of meter
     */
    private $contractId;

    /**
     * Location of the meter
     */
    private $address;

    /**
     * Meter type e.g Electric
     */
    private $meterType;

    /**
     * Area location of meter
     */
    private $area;

    /**
     * Registration date of meter
     */
    private $registrationDate;

    /**
     * Last vending date in vendor system
     */
    private $lastVendingDate;

    /**
     * Tax applied
     */
    private $vat;

    /**
     * Internal Id of search operation
     */
    private $internalId;

    /**
     * Landlord of meter
     */
    private $landlord;

    /**
     * Value used for large meters
     */
    private $ptct;


    /**
     * @return mixed
     */
    public function getMeterCode()
    {
        return $this->meterCode;
    }

    /**
     * @param mixed $meterCode
     */
    public function setMeterCode($meterCode): void
    {
        $this->meterCode = $meterCode;
    }

    /**
     * @return float
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param float $tariff
     */
    public function setTariff($tariff): void
    {
        $this->tariff = $tariff;
    }

    /**
     * @return mixed
     */
    public function getTariffType()
    {
        return $this->tariffType;
    }

    /**
     * @param mixed $tariffType
     */
    public function setTariffType($tariffType): void
    {
        $this->tariffType = $tariffType;
    }

    /**
     * @return mixed
     */
    public function getContractId()
    {
        return $this->contractId;
    }

    /**
     * @param mixed $contractId
     */
    public function setContractId($contractId): void
    {
        $this->contractId = $contractId;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getMeterType()
    {
        return $this->meterType;
    }

    /**
     * @param mixed $meterType
     */
    public function setMeterType($meterType): void
    {
        $this->meterType = $meterType;
    }

    /**
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param mixed $area
     */
    public function setArea($area): void
    {
        $this->area = $area;
    }

    /**
     * @return mixed
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * @param mixed $registrationDate
     */
    public function setRegistrationDate($registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * @return mixed
     */
    public function getLastVendingDate()
    {
        return $this->lastVendingDate;
    }

    /**
     * @param mixed $lastVendingDate
     */
    public function setLastVendingDate($lastVendingDate): void
    {
        $this->lastVendingDate = $lastVendingDate;
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param float $vat
     */
    public function setVat($vat): void
    {
        $this->vat = $vat;
    }

    /**
     * @return mixed
     */
    public function getInternalId()
    {
        return $this->internalId;
    }

    /**
     * @param mixed $internalId
     */
    public function setInternalId($internalId): void
    {
        $this->internalId = $internalId;
    }

    /**
     * @return mixed
     */
    public function getLandlord()
    {
        return $this->landlord;
    }

    /**
     * @param mixed $landlord
     */
    public function setLandlord($landlord): void
    {
        $this->landlord = $landlord;
    }

    /**
     * @return mixed
     */
    public function getPtct()
    {
        return $this->ptct;
    }

    /**
     * @param mixed $ptct
     */
    public function setPtct($ptct): void
    {
        $this->ptct = $ptct;
    }
}