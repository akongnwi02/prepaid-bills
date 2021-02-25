<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/18/20
 * Time: 8:27 PM
 */

namespace App\Services\Clients\Providers;

use App\Exceptions\BadRequestException;
use App\Exceptions\GeneralException;
use App\Exceptions\NotFoundException;
use App\Models\Authentication;
use App\Models\Transaction;
use App\Services\Clients\ClientInterface;
use App\Services\Constants\ErrorCodesConstants;
use App\Services\Objects\PrepaidMeter;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class EneoClient implements ClientInterface
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
     * @throws NotFoundException
     */
    public function search($meterCode): PrepaidMeter
    {
        $url = $this->config['url'] . "/prepaid/checkMeterNumber";
    
        $json = [
            'meterNumber' => $meterCode,
            'terminalID' => $this->config['terminal_id'],
            'clientID' => $this->config['client_id'],
            'operatorName' => $this->config['operator_name'],
            'operatorPassword' => $this->config['operator_password'],
        ];
    
        $accessToken = $this->getAccessToken();
    
        Log::debug("{$this->getClientName()}: Sending meter search request to service provider", [
            'meter code' => $meterCode,
            'query' => config('app.env') != 'production' ? $json : 'hidden',
            'url'   => $url,
        ]);
    
        $httpClient = $this->getHttpClient($url);
        
        try {
            $response = $httpClient->request('POST', '', [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Content-Type' => 'application/json'
                ],
                'json'   => $json
            ]);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        } catch (\Exception $exception) {
            Log::emergency("{$this->getClientName()}: Unexpected error occurred during prepaid meter search", [
                'exception' => $exception->getMessage()
            ]);
            throw new GeneralException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR, 'Error connecting to service provider: ' . $exception->getMessage());
        }
    
        $content = $response->getBody()->getContents();
    
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'status code' => $response->getStatusCode(),
            'response' => $content
        ]);
    
        $body = json_decode($content);
        if ($response->getStatusCode() == 200) {
            $meter = new PrepaidMeter();
            $meter->setServiceCode($this->config['service_code'])
                ->setMeterCode($meterCode)
                ->setName($body->customerName)
                ->setPhone($body->contatcNo)
                ->setAddress($body->customerAddress);
            return $meter;
        } else if($response->getStatusCode() == 400 || $response->getStatusCode() == 404){
            throw new NotFoundException(ErrorCodesConstants::METER_CODE_NOT_FOUND, 'Invalid meter code');
        }
        
        throw new BadRequestException(ErrorCodesConstants::GENERAL_CODE, 'Unexpected error occured during search');
    }
    
    /**
     * @param PrepaidMeter $meter
     * @return string
     * @throws BadRequestException
     * @throws GeneralException
     */
    public function buy(PrepaidMeter $meter): string
    {
        
        $url = $this->config['url'] . "/prepaid/purchase";
    
        $json = [
            'meterNumber' => $meter->getMeterCode(),
            'customerId' => $meter->getMeterCode(),
            'amount' => $meter->getAmount(),
            'terminalID' => $this->config['terminal_id'],
            'clientID' => $this->config['client_id'],
            'operatorName' => $this->config['operator_name'],
            'operatorPassword' => $this->config['operator_password'],
            'counterCode' => $this->config['counter_code']
        ];
    
        $accessToken = $this->getAccessToken();
    
        Log::debug("{$this->getClientName()}: Sending token generation request to service provider", [
            'meter code' => $meter->getMeterCode(),
            'query' => config('app.env') != 'production' ? $json : 'hidden',
            'url'   => $url,
        ]);
        
        $httpClient = $this->getHttpClient($url);
    
        try {
            $response = $httpClient->request('POST', '', [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Content-Type' => 'application/json'
                ],
                'json'   => $json
            ]);
        } catch (BadResponseException $exception) {
            $response = $exception->getResponse();
        } catch (\Exception $exception) {
            Log::emergency("{$this->getClientName()}: Unexpected error occurred during prepaid token generation", [
                'exception' => $exception->getMessage()
            ]);
            throw new BadRequestException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR, 'Error connecting to service provider: ' . $exception->getMessage());
        }
    
        $content = $response->getBody()->getContents();
    
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'status code' => $response->getStatusCode(),
            'response' => $content
        ]);
        
        $body = json_decode($content);
    
        if (isset($body->data->transactionId)) {
            $transaction = Transaction::where('internal_id', $meter->getIntId())->first();
            $transaction->merchant_id = $body->data->transactionId;
            $transaction->save();

            Log::debug("{$this->getClientName()}: Transaction initiated successfully. Intentionally throwing exception to trigger status job to check transaction status", [
                'status code' => $response->getStatusCode(),
                'response' => $content
            ]);
            
            throw new GeneralException(ErrorCodesConstants::GENERAL_CODE, 'Transaction initiated successfully. Throwing exception to trigger status check');
        } else {
            Log::emergency("{$this->getClientName()}: Unexpected error occurred during prepaid electricity purchase", [
                'meter code' => $meter->getMeterCode(),
                'amount' => $meter->getAmount(),
                'exception' => $content
            ]);
            // Status check not necessary. No transactionId in response
            throw new BadRequestException(ErrorCodesConstants::GENERAL_CODE, 'Unexpected error occurred during purchase');
        }
    }
    
    /**
     * @param $transaction
     * @return string
     * @throws GeneralException
     * @throws BadRequestException
     */
    public function status($transaction): string
    {
        $url = $this->config['url'] . "/prepaid/check_transaction";
        
        $accessToken = $this->getAccessToken();
        
        $query = [
            'transactionId' => $transaction->merchant_id,
        ];
        
        Log::info("{$this->getClientName()}: Sending status check request to service provider", [
            'url' => $url,
            'meter code' => $transaction->destination,
            'transaction.merchant_id' => $transaction->merchant_id,
            'transaction.id' => $transaction->id,
            'transaction.status' => $transaction->status,
        ]);
    
        $httpClient = $this->getHttpClient($url);
        try {
            $response = $httpClient->request('GET', '', [
                'headers' => ['Authorization' => "Bearer $accessToken"],
                'query' => $query
            ]);
        }
    
        catch (\Exception $exception) {
            throw new GeneralException(ErrorCodesConstants::GENERAL_CODE,
                'Unexpected error checking transaction status with service provider: ' . $exception->getMessage());
        }
    
        $content = $response->getBody()->getContents();
    
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'status code' => $response->getStatusCode(),
            'body' => $content
        ]);
        $body = json_decode($content);
    
        $status = $body->status;
    
        if (strtolower($status) == 'success') {
            return $body->token;
        }
        if (in_array(strtolower($status), ['failed', 'canceled', 'cancelled'])) {
            throw new BadRequestException(ErrorCodesConstants::GENERAL_CODE, 'Transaction failed unexpectedly');
        }
    
        Log::debug("{$this->getClientName()}: Transaction is not in a final status. Expecting failed, cancelled or success", [
            'status received' => $body->status,
        ]);
        throw new GeneralException(ErrorCodesConstants::GENERAL_CODE, "Transaction status: {$body->status} is not a final status");
    }
    
    /**
     * @return mixed
     * @throws BadRequestException
     * @throws GeneralException
     */
    public function getAccessToken()
    {
        $auth = Authentication::where('service_code', $this->config['service_code'])->latest()->get()->first();
        
        if ($auth && (Carbon::now()->diffInSeconds($auth->created_at) < $auth->expires_in)) {
            Log::debug("{$this->getClientName()}: Valid OAuth token found for service: {$this->config['service_code']}", [
                'expires_in' => $auth->expires_in,
                'created_at' => $auth->created_at->toDateTimeString(),
                'current_time' => Carbon::now()->toDateTimeString(),
            ]);
    
            if ($this->isValidToken($auth->access_token)) {
                return $auth->access_token;
            }
        }
        
        Log::debug("{$this->getClientName()}: No valid access token found locally. Connecting to service provider to generate new one", [
            'service' => $this->config['service_code']
        ]);
        
        return $this->generateNewAccessToken();
        
    }
    
    /**
     * @return mixed
     * @throws BadRequestException
     */
    public function generateNewAccessToken()
    {
        $url = $this->config['auth_url'];
        // connect to the postpaid microservice to get access token
        Log::debug("{$this->getClientName()}: Generating new authorization token for {$this->config['service_code']} from the postpaid micro service", [
            'url' => $url,
        ]);
        $httpClient = $this->getHttpClient($url);
        try {
            $response = $httpClient->request('GET', '', [
                'headers' => [
                    'x-api-key' => $this->config['auth_key']
                ],
            ]);
        } catch (\Exception $exception) {
            throw new BadRequestException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR,
                'Error connecting to service provider to generate token: ' . $exception->getMessage());
        }
        
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response received from service provider (may be hidden for security)", [
            'status code' => $response->getStatusCode(),
            'body' => config('app.env') != 'production' ? $content : null
        ]);
        
        $body = json_decode($content);
        
        if (isset($body->access_token)) {
            
            // save and return access token
            Authentication::create([
                'expires_in' => $body->expires_in,
                'access_token' => $body->access_token,
                'refresh_token' => $body->refresh_token,
                'service_code' => $this->config['service_code'],
                'type' => 'Bearer'
            ]);
            
            Log::info("{$this->getClientName()}: Token Retrieved successfully");
            return $body->access_token;
        }
        
        Log::emergency("{$this->getClientName()}: Cannot authenticate with service provider", ['service' => $this->config['service_code']]);
        
        throw new BadRequestException(ErrorCodesConstants::GENERAL_CODE, 'Cannot get token from response');
    }
    
    /**
     * @param $accessToken
     * @return bool
     * @throws GeneralException
     */
    public function isValidToken($accessToken)
    {
        $url = $this->config['url'] . '/oauth/check_token';
        
        $query = [
            'token' => $accessToken,
        ];
        
        Log::info("{$this->getClientName()}: Verifying validity of access token with service provider", [
            'service' => $this->config['service_code'],
            'url' => $url,
            'query' => config('app.env') != 'production' ? $query : 'hidden'
        ]);
        
        $httpClient = $this->getHttpClient($url);
        try {
            $response = $httpClient->request('GET', '', [
                'headers' => [
                    'Authorization' => "Bearer $accessToken"
                ],
                'query'   => $query
            ]);
        } catch (ClientException $exception){
            $response = $exception->getResponse();
        } catch (\Exception $exception) {
            throw new GeneralException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR,
                'Error connecting to service provider to generate token: ' . $exception->getMessage());
        }
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response received from service provider (may be hidden for security)", [
            'status code' => $response->getStatusCode(),
            'body' => config('app.env') != 'production' ? $content : null
        ]);
        
        $body = json_decode($content);
    
        if ($response->getStatusCode() == 200) {
            Log::info("{$this->getClientName()}: Token is valid with service provider", [
                'status code' => $response->getStatusCode(),
            ]);
            return $body->active;
        }
        
        return false;
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
        ]);
    }
    
    /**
     * @return string
     */
    public function getClientName(): string
    {
        return class_basename($this);
    }
}