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
use App\Services\Clients\Providers\IATElectricityClient;
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
            case config('app.services.iat.electricity_code'):
                $config['code'] = config('app.services.iat.code');
                $config['url']  = config('app.services.iat.url');
                $config['key']  = config('app.services.iat.key');
                $config['secret']  = config('app.services.iat.secret');
                $config['electricity_code']  = config('app.services.iat.electricity_code');
                $config['currency_code']  = config('app.services.iat.currency_code');
                return new IATElectricityClient($config);
                break;
            
            case config('app.services.eneo.code'):
                $config['service_code'] = config('app.services.eneo.code');
                $config['url']          = config('app.services.eneo.url');
                $config['username']     = config('app.services.eneo.username');
                $config['password']     = config('app.services.eneo.password');
                $config['counter_code'] = config('app.services.eneo.counter_code');
                $config['client_id'] = config('app.services.eneo.client_id');
                $config['terminal_id'] = config('app.services.eneo.terminal_id');
                $config['operator_name'] = config('app.services.eneo.operator_name');
                $config['operator_password'] = config('app.services.eneo.operator_password');
                $config['auth_url'] = config('app.services.eneo.auth_url');
                $config['auth_key'] = config('app.services.eneo.auth_key');
                return new EneoClient($config);
                break;
            default:
                throw new GeneralException(ErrorCodesConstants::SERVICE_NOT_FOUND, 'Unknown Micro Service');
        }
    }
}