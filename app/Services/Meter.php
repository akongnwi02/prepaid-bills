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
    private $meterCode;

    private $tariff;

    private $tariffType;

    private $contractId;

    private $address;

    private $meterType;

    private $area;

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
     * @return mixed
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param mixed $tariff
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
}