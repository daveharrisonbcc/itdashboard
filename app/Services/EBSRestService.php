<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class EBSRestService
{
    private $token;
    private $EBSUrl;
    private $EBSUser;
    private $EBSPass;
    private $EBSTokenUrl;

    public function __construct()
    {

        $this->token = config('app.ebs_token');
        $this->EBSUrl = config('app.ebs_rest_url');
        $this->EBSUser = config('app.ebs_rest_user');
        $this->EBSPass = config('app.ebs_rest_pass');
        $this->EBSTokenUrl = config('app.ebs_token_url');

    }

    public function getAccessToken()
    {

        if (Cache::has('ebs_token')) {
            return Cache::get('ebs_token');
        }

        $url = $this->EBSTokenUrl;

        $headers = array_merge([
            'Accept' => 'application/xml',
            'Content-Type' => 'application/xml',
        ]);

        $response = Http::withBasicAuth($this->EBSUser, $this->EBSPass)
        ->withHeaders($headers)
        ->get($url);

        if ($response->successful()) {
            $xmlContent = $response->body();        

            $xml = new SimpleXMLElement($xmlContent);

            $token = (string)$xml->Token;

            Cache::put('ebs_token', $token, now()->addHour());
       
            return $token;

        } else {
            return null;
        }
    }

    public function getStudentPhoto($personCode)
    {
        $token = $this->getAccessToken();

        $url = $this->EBSUrl .$personCode. "/PERSON_PICTURE";
  

        $headers = array_merge([
            'Accept' => 'application/xml',
            'Content-Type' => 'application/xml',
            'Authorization' => $token
        ]);

        $response = Http::withHeaders($headers)
        ->get($url);

   

        if($response->status() === 401) {
            Cache::forget('ebs_token');
            $token = $this->getAccessToken();
            $headers['Authorization'] = $token;
            $response = Http::withHeaders($headers)
        ->get($url);
            // return $this->getStudentPhoto($personCode);
        }
        
        if($response->successful()) {
            $xmlContent = $response->body();        

            $xml = new SimpleXMLElement($xmlContent);
            $imageData = (string)$xml->ImageData;
            // $imageData = null;

            if($imageData) {
                return $imageData;
            } else {
                return null;
            }
        } else {
            return null;
        }

        // $xmlContent = $response->body();        

        // $xml = new SimpleXMLElement($xmlContent);
        // $imageData = (string)$xml->ImageData;
        // // $imageData = null;

        // if($imageData) {
        //     return $imageData;
        // } else {
        //     return null;
        // }

      
    }



}