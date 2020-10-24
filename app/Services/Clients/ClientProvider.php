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
use App\Services\Constants\ErrorCodesConstants;

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
                $config['code'] = config('app.services.iat.code');
                $config['key']  = config('app.services.iat.key');
                $config['url']  = config('app.services.iat.url');
                return new IATClient($config);
                break;
            case config('app.services.eneo.code'):
                return new EneoClient();
                break;
            default:
                throw new GeneralException(ErrorCodesConstants::SERVICE_NOT_FOUND, 'Unknown Micro Service');
        }
    }
}