<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/28/19
 * Time: 3:17 PM
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MeterResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'internalId'       => $this->getInternalId(),
            'meterCode'        => $this->getMeterCode(),
            'landlord'         => $this->getLandlord(),
            'tariff'           => $this->getTariff(),
            'tariffType'       => $this->getTariffType(),
            'contractId'       => $this->getContractId(),
            'address'          => $this->getAddress(),
            'meterType'        => $this->getMeterType(),
            'area'             => $this->getArea(),
            'lastVendingDate'  => $this->getLastVendingDate(),
            'registrationDate' => $this->getRegistrationDate(),
            'vat'              => $this->getVat(),
            'pctc'             => $this->getPtct(),
        ];
    }
}