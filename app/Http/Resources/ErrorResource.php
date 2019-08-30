<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/28/19
 * Time: 3:15 PM
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
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
            'msg'    => $this->getMessage(),
            'devMsg' => $this->getDevMessage(),
            'code'   => $this->getCode(),
            'debug'  => $this->when(config('app.debug'), $this->getDebug()),
        ];
    }
}