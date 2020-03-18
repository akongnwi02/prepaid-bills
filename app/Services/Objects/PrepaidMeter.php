<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/16/20
 * Time: 5:14 PM
 */

namespace App\Services\Objects;


class PrepaidMeter
{
    public $meter_code;
    public $address;
    public $name;
    public $phone;
    public $email;
    public $city;
    public $state;
    public $price;
    public $amount;
    public $energy;
    public $token;
    
    /**
     * @return mixed
     */
    public function getMeterCode()
    {
        return $this->meter_code;
    }
    
    /**
     * @param mixed $meter_code
     */
    public function setMeterCode($meter_code): void
    {
        $this->meter_code = $meter_code;
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
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
    
    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }
    
    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }
    
    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }
    
    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }
    
    /**
     * @return mixed
     */
    public function getEnergy()
    {
        return $this->energy;
    }
    
    /**
     * @param mixed $energy
     */
    public function setEnergy($energy): void
    {
        $this->energy = $energy;
    }
    
    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }
}