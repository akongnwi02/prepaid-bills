<?php

namespace App\Http\Controllers;

use App\Exceptions\GeneralException;
use App\Services\Clients\IATClient;
use Laravel\Lumen\Http\Request;

class TransactionController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function search(Request $request)
    {
        $this->validate($request, [
            'destination' => ['required', 'string', 'min:7'],
            'service__code' => ['required', 'string', 'min:3',],
            'amount' => ['sometimes', 'nullable', 'regex:/^(?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d+)?$/'],
        ]);
        
        $client = $this->getClient($request->input('destination_code'));
        $meter = $client->search();
    
        return response()->json([
            ''
        ]);
        
    }
    
    public function getClient($serviceCode)
    {
        switch ($serviceCode) {
            case config('app.services.codes.iat'):
                return new IATClient();
                break;
                
            default:
                throw new GeneralException('Unknown Micro Service');
        }
    }
}
