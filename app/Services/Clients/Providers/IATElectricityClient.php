<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/14/20
 * Time: 8:10 PM
 */

namespace App\Services\Clients\Providers;

use App\Exceptions\BadRequestException;
use App\Exceptions\GeneralException;
use App\Models\Authentication;
use App\Models\Transaction;
use App\Services\Clients\ClientInterface;
use App\Services\Constants\ErrorCodesConstants;
use App\Services\Objects\PrepaidMeter;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class IATElectricityClient implements ClientInterface
{
    public $config;
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    /**
     * @param $meterCode
     * @return PrepaidMeter
     * @throws BadRequestException
     * @throws GeneralException
     * @throws GuzzleException
     */
    public function search($meterCode): PrepaidMeter
    {
        $url = $this->config['url'] . "/v1/search";
        
        $query = [
            'service_number' => $meterCode,
            'service_code' => $this->config['electricity_code'],
        ];
    
        Log::debug("{$this->getClientName()}: Performing a new search operation with service provider", [
            'query' => $query,
            'url' => $url,
        ]);
    
        $accessToken = $this->getAccessToken();
    
        $httpClient = $this->getHttpClient($url);
        try {
            $response = $httpClient->request('GET', '', [
                'query' => $query,
                'headers' => ['Authorization' => "Bearer $accessToken"]

            ]);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        } catch (\Exception $exception) {
            throw new GeneralException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR, 'Error connecting to service provider: ' . $exception->getMessage());
        }
        
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'provider' => $this->config['code'],
            'response' => $content
        ]);
        
        $body = json_decode($content);
        if ($response->getStatusCode() == 200) {
            $meter = new PrepaidMeter();
            $meter->setServiceCode($this->config['code'])
                ->setMeterCode($meterCode)
                ->setName($body->customer)
                ->setAddress(implode(', ', array_filter([$body->address, $body->city])));
            return $meter;
        } else {
            return $this->handleErrorResponse($body);
        }
    }
    
    /**
     * @param PrepaidMeter $meter
     * @return string
     * @throws BadRequestException
     * @throws GeneralException
     * @throws GuzzleException
     */
    public function buy(PrepaidMeter $meter): string
    {
        $url = $this->config['url'] . "/v1/buy";
        
        $json = [
            'service_number' => $meter->getMeterCode(),
            'service_code'   => $this->config['electricity_code'],
            'currency_code'  => $this->config['currency_code'],
            'amount'         => $meter->getAmount(),
            'external_id'    => $meter->getIntId(),
            'phone'          => $meter->getPhone(),
        ];
    
        Log::debug("{$this->getClientName()}: Performing a new purchase operation with service provider",[
            'json' => $json,
            'url' => $url,
        ]);
    
        $accessToken = $this->getAccessToken();
        
        $httpClient = $this->getHttpClient($url);
        
        try {
            $response = $httpClient->request('POST', '', [
                'json' => $json,
                'headers' => ['Authorization' => "Bearer $accessToken"]
            ]);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
            
        } catch (\Exception $exception) {
            throw new GeneralException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR,
                'Error connecting to service provider: ' . $exception->getMessage()
            );
        }
        
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'provider' => $this->config['code'],
            'meter code' => $meter->getMeterCode(),
            'body' => json_decode($content),
            'response' => $content
        ]);
        
        $body = json_decode($content);
    
        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            
            if (!empty($body->token)) {
                return $body->token;
            }
        } else {
            $this->handleErrorResponse($body);
        }
    }
    
    /**
     * @param $transaction
     * @return Transaction
     * @throws GeneralException
     * @throws GuzzleException
     * @throws BadRequestException
     */
    public function status($transaction): string
    {
        $url = $this->config['url'] . "/v1/status/$transaction->internal_id";
    
        Log::info("{$this->getClientName()}: Sending status check request to service provider", [
            'meter code' => $transaction->destination,
            'provider' => $this->config['code'],
            'transaction.id' => $transaction->id,
            'transaction.status' => $transaction->status,
            'url' => $url
        ]);
    
        $accessToken = $this->getAccessToken();
        
        $httpClient = $this->getHttpClient($url);
    
        try {
            $response = $httpClient->request('GET', '', [
                'headers' => ['Authorization' => "Bearer $accessToken"]
            ]);
        } catch (\Exception $exception) {
            Log::error("{$this->getClientName()}: Error Response from service provider", [
                'provider' => $this->config['code'],
                'meter code' => $transaction->destination,
                'error message' => $exception->getMessage(),
            ]);
            throw new GeneralException(ErrorCodesConstants::GENERAL_CODE, $exception->getMessage());
        }
    
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'provider' => $this->config['code'],
            'meter code' => $transaction->destination,
            'body' => json_decode($content),
            'response' => $content,
        ]);
    
        $body = json_decode($content);
    
        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            
            if (!empty($body->token)) {
                return $body->token;
            }
        } else {
            throw new GeneralException(ErrorCodesConstants::GENERAL_CODE, 'Token could not be extracted from response response');
        }
    }
    
    /**
     * @param $body
     * @throws BadRequestException
     * @throws GeneralException
     */
    public function handleErrorResponse($body)
    {
        switch (@$body->error_code) {
            case '10048':
            case '10020':
            case '10032':
                throw new BadRequestException(ErrorCodesConstants::METER_CODE_NOT_FOUND, 'Meter does not exist');
            case '10034':
                throw new BadRequestException(ErrorCodesConstants::MINIMUM_AMOUNT_ERROR, 'The amount provided is less than the minimum amount');
            case '10044':
                throw new BadRequestException(ErrorCodesConstants::MAXIMUM_AMOUNT_ERROR, 'The amount provided is more than the maximum amount');
            case '10047':
                throw new BadRequestException(ErrorCodesConstants::STEP_AMOUNT_ERROR, 'The amount is not a multiple of the configured amount');
            case '10031':
                throw new BadRequestException(ErrorCodesConstants::DEACTIVATED_METER, 'The meter is not active');
            default:
                throw new GeneralException(ErrorCodesConstants::GENERAL_CODE, 'Unknown error');
        }
    }
    
    /**
     * @param $url
     * @return Client
     */
    public function getHttpClient($url)
    {
        return new Client([
            'base_uri'        => $url,
            'timeout'         => 120,
            'connect_timeout' => 120,
            'allow_redirects' => true,
            'headers'         => [
                'x-api-key' => $this->config['key'],
                'accept'    => 'application/json'
            ],
        ]);
    }
    
    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws BadRequestException
     */
    public function getAccessToken()
    {
        $auth = Authentication::where('service_code', $this->config['code'])->latest()->get()->first();
        
        if ($auth && (Carbon::now()->diffInSeconds($auth->created_at) < $auth->expires_in)) {
            Log::debug("{$this->getClientName()}: Valid OAuth token found for service: {$this->config['code']}", [
                'expires_in' => $auth->expires_in,
                'created_at' => $auth->created_at->toDateTimeString(),
                'current_time' => Carbon::now()->toDateTimeString(),
            ]);
            return $auth->access_token;
        }
        
        Log::debug("{$this->getClientName()}: No valid access token found locally. Connecting to service provider to generate new one", [
            'service' => $this->config['code']
        ]);
        
        return $this->generateNewAccessToken();
    }
    
    
    /**
     * @return mixed
     * @throws BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function generateNewAccessToken()
    {
        $url = $this->config['url'] . "/token";
        
        $basicAuth = base64_encode($this->config['key'] . ':' . $this->config['secret']);
        
        Log::debug("{$this->getClientName()}: Generating new authorization token for {$this->config['code']} api", [
            'url' => $url
        ]);
        
        $httpClient = $this->getHttpClient($url);
        try {
            $response = $httpClient->request('POST', '', [
                'headers' => ['Authorization' => "Basic $basicAuth"]
            ]);
        } catch (\Exception $exception) {
            
            Log::emergency("{$this->getClientName()}: Could not authenticate with service provider", [
                'code' => $this->config['code'],
                'error' => $exception->getMessage()
            ]);
            
            throw new BadRequestException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR,
                'Error connecting to service provider to generate token: ' . $exception->getMessage());
        }
        
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'response' => config('app.env') != 'production' ? $content : 'hidden for security',
        ]);
        
        $body = json_decode($content);
        
        if (isset($body->access_token)) {
            Authentication::create([
                'expires_in' => $body->expires_in,
                'access_token' => $body->access_token,
                'refresh_token' => null,
                'service_code' => $this->config['code'],
                'type' => 'Bearer'
            ]);
            
            Log::info("{$this->getClientName()}: Token Retrieved successfully");
            return $body->access_token;
        }
        
        Log::emergency("{$this->getClientName()}: Cannot authenticate with service provider unable to retrieve token from response", ['service' => $this->config['code']]);
        
        throw new BadRequestException(ErrorCodesConstants::GENERAL_CODE, 'Cannot get token from response');
    }
    
    public function getClientName(): string
    {
        return class_basename($this);
    }
}
