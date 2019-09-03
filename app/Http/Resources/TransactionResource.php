<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/28/19
 * Time: 3:17 PM
 */

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'meterCode'   => $this->meter_code,
            'internalId'  => $this->internal_id,
            'externalId'  => $this->external_id,
            'status'      => $this->status,
            'energy'      => $this->energy,
            'amount'      => $this->amount,
            'token'       => $this->token,
            'createdAt'   => $this->created_at->toDateTimeString(),
            'updatedAt'   => $this->updated_at->toDateTimeString(),
        ];
    }
}