<?php

namespace App\Services;

use App\Models\EBSPeople;
use App\Models\ProMonitorStudent;
use App\Services\GraphApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UserService
{
    protected $username;
    protected $fullname;
    protected $graphApiService;
  

    public function __construct()
    {
        //Local
        if(config('app.env') == 'local') {
            $_SERVER['HTTP_SAMACCOUNTNAME'] = 'DaveHa';
            $_SERVER['HTTP_SN'] = 'Harrison';
            $_SERVER['HTTP_GIVENNAME'] = 'Dave';
        }

        // if(config('app.env') == 'local') {
        //     $_SERVER['HTTP_SAMACCOUNTNAME'] = '264731';
        //     $_SERVER['HTTP_SN'] = 'Iftikhar';
        //     $_SERVER['HTTP_GIVENNAME'] = 'Ahsan';
        // }
        

        // $this->username = $_SERVER['HTTP_SAMACCOUNTNAME'] ?? NULL;
        // $this->fullname = $_SERVER['HTTP_GIVENNAME'].' '.$_SERVER['HTTP_SN'] ?? NULL;
    }

    public function username() : String {
    
        return $this->username;
    }

    public function fullname() : String  {

        return $this->fullname;
    }



    public function initials() : String {

        $fullName = $this->fullname ?: '';
        $words = explode(' ', trim($fullName));
        return implode('', array_map(function($word) {
            return strtoupper($word[0]);
        }, $words));

    }

    public function userPhoto()
    {
        $photoUrl = null;
        $userEmail = $this->username.'@boltoncollege365.ac.uk';
        $cacheKey = 'user_photo_' . $this->username;

        try {
            // Try to get from cache first
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $this->graphApiService = app(GraphApiService::class);
            
            // Add specific headers for photo request
            $headers = [
                'Accept' => 'image/jpeg,image/png',
                'Cache-Control' => 'no-cache',
                'Prefer' => 'originalContent'
            ];

            $photo = $this->graphApiService->makeRequest(
                'get', 
                "/users/{$userEmail}/photos/96x96/\$value",
                [],
                $headers
            );
            
            if (is_string($photo)) {
                // Detect image type from content
                $contentType = 'image/jpeg';
                if (substr($photo, 0, 8) === "\x89PNG\r\n\x1a\n") {
                    $contentType = 'image/png';
                }
                
                $base64Image = base64_encode($photo);
                $photoUrl = "data:{$contentType};base64,{$base64Image}";
                
                // Cache the photo URL for 24 hours
                Cache::put($cacheKey, $photoUrl, now()->addHours(24));
            }

            return $photoUrl;

        } catch (\Exception $e) {
            Log::error("Error loading photo for user {$this->username}", [
                'error' => $e->getMessage(),
                'user' => $this->username
            ]);
            
            // Cache the failure to prevent repeated attempts
            Cache::put($cacheKey . '_failed', true, now()->addMinutes(30));
            
            return null;
        }
    }

    public function userDetails($username)
    {
        $userDetails = EBSPeople::select("FORENAME", "SURNAME", "PERSON_CODE", "NETWORK_USERID")
            ->where(function($query) use ($username) {
                $query->where('NETWORK_USERID', $username)
                    ->orWhere('COLLEGE_LOGIN', $username);

                if (is_numeric($username)) {
                    $query->orWhere('PERSON_CODE', $username);
                }
            })
            ->first();
        

        // Handle case where no user details are found
        if (!$userDetails) {
            Log::warning("No user details found for username: {$username}");
            return [
                'displayName' => $username, // Fallback to username
                'promonStudent' => null,   // No photo available
            ];
        }


        $details = [
            'displayName' => $userDetails->FORENAME . ' ' . $userDetails->SURNAME,
        ];

        return $details;
    }


    public function isStaff()
    {
        return !is_numeric($this->username);
    }
}
