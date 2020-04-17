<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/19/20
 * Time: 12:02 AM
 */

namespace App\Services\Clients;

use App\Exceptions\GeneralException;
use App\Services\Clients\Providers\EneoClient;
use App\Services\Clients\Providers\TestClient;
use App\Services\Clients\Providers\IATClient;

trait ClientProvider
{
    /**
     * @param $serviceCode
     * @return ClientInterface
     * @throws GeneralException
     */
    public function client($serviceCode)
    {
        if (strtolower(config('app.env') == 'testing')) {
            return new TestClient();
        }
        switch ($serviceCode) {
            case config('app.services.iat.code'):
                return new IATClient();
                break;
            case config('app.services.eneo.code'):
                return new EneoClient();
                break;
            default:
                throw new GeneralException('Unknown Micro Service');
        }
    }
}