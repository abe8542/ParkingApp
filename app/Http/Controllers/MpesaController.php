<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    public function stkPush(Request $request)
    {
        $amount = $request->amount; // e.g., 1
        $phoneNumber = "2547XXXXXXXX"; // Use your test number

        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode(env('MPESA_STK_SHORTCODE') . env('MPESA_STK_PASSKEY') . $timestamp);

        $curl_post_data = [
            'BusinessShortCode' => env('MPESA_STK_SHORTCODE'),
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => env('MPESA_STK_SHORTCODE'),
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => env('MPESA_CALLBACK_URL'),
            'AccountReference' => 'SmartPark_Nairobi',
            'TransactionDesc' => 'Parking Fee Payment'
        ];

        // This sends the request to Safaricom
        $response = $this->makePostRequest($url, $curl_post_data);
        return response()->json($response);
    }

    private function makePostRequest($url, $data) {
        // Logic to get Access Token and send CURL request
        // (Full code for this is about 2 pages long for your thesis!)
    }
}
