<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/19/20
 * Time: 12:02 AM
 */

namespace App\Services\Clients;

use App\Exceptions\GeneralException;

trait ClientTrait
{
    public $iatClient;
    public $eneoClient;
    
    public function __construct(IATClient $iatClient, EneoClient $eneoClient)
    {
        $this->iatClient = $iatClient;
        $this->eneoClient = $eneoClient;
    }
    
    /**
     * @param $serviceCode
     * @return EneoClient|IATClient
     * @throws GeneralException
     */
    public function client($serviceCode)
    {
        switch ($serviceCode) {
            case config('app.services.iat.code'):
                return $this->iatClient;
                break;
            case config('app.services.eneo.code'):
                return $this->eneoClient;
                break;
            default:
                throw new GeneralException('Unknown Micro Service');
        }
    }
}