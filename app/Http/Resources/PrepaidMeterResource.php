<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/16/20
 * Time: 5:12 PM
 */

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class PrepaidMeterResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'meter_code' => $this->meter_code,
            'address' => $this->address,
            'amount' => $this->amount,
            'energy' => $this->energy,
            'price' => $this->price,
            'token' => $this->token,
            'name' => $this->name,
        ];
    }
}