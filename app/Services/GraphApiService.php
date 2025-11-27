<?php
namespace App\Services;

use Microsoft\Graph\Core\GraphClientFactory;
use Microsoft\Graph\Core\Authentication\TokenCredentialAuthenticationProvider;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GraphApiService
{
    private $clientId;
    private $clientSecret;
    private $tenantId;
    private $graphClient;
    private $baseUrl = 'https://graph.microsoft.com/v1.0';

    public function __construct()
    {
        $this->clientId = config('services.microsoft.client_id');
        $this->clientSecret = config('services.microsoft.client_secret');
        $this->tenantId = config('services.microsoft.tenant_id');
    }

    /**
     * Get access token for Microsoft Graph API
     *
     * @return string
     */
    public function getAccessToken()
    {
        if (Cache::has('ms_graph_token')) {
            return Cache::get('ms_graph_token');
        }

        try {
            // Use Laravel's HTTP client for the token request
            $response = Http::asForm()->timeout(10)->post(
                "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", 
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default'
                ]
            );
            
            $tokenData = $response->json();
            
            if (!isset($tokenData['access_token'])) {
                throw new \Exception("Failed to get access token: " . json_encode($tokenData));
            }
            
            $token = $tokenData['access_token'];
            
            // Cache token, but subtract 5 minutes for safety
            $expiresIn = isset($tokenData['expires_in']) ? (int)$tokenData['expires_in'] - 300 : 3300;
            Cache::put('ms_graph_token', $token, now()->addSeconds($expiresIn));
            
            return $token;
        } catch (\Exception $e) {
            Log::error("Failed to get Microsoft Graph token: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make a request to Microsoft Graph API using Laravel's HTTP facade
     * 
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param string $endpoint API endpoint (relative to the base URL)
     * @param array $params Query parameters or request body
     * @param array $headers Additional headers
     * @return array|string Response data
     */
    public function makeRequest($method, $endpoint, $params = [], $headers = [])
    {
        // Performance optimization: direct API call for user searches
        if (strtoupper($method) === 'GET' && $endpoint === '/users') {
            return $this->searchUsersOptimized($params);
        }
        
        try {
            $token = $this->getAccessToken();
            $url = $this->baseUrl . $endpoint;
            
            // Create HTTP request with Laravel's HTTP facade
            $request = Http::withToken($token)
                ->withHeaders(array_merge([
                    'ConsistencyLevel' => 'eventual',
                ], $headers))
                ->timeout(15); // Increased timeout for better reliability
            
            // Execute the appropriate method
            $method = strtolower($method);
            if ($method === 'get') {
                $response = $request->get($url, $params);
            } elseif ($method === 'post') {
                $response = $request->post($url, $params);
            } elseif ($method === 'patch') {
                $response = $request->patch($url, $params);
            } elseif ($method === 'put') {
                $response = $request->put($url, $params);
            } elseif ($method === 'delete') {
                $response = $request->delete($url, $params);
            } else {
                throw new \InvalidArgumentException("Unsupported HTTP method: $method");
            }
            
            // Handle rate limiting
            if ($response->status() === 429) {
                $retryAfter = $response->header('Retry-After', 1);
                Log::warning("Rate limited by Graph API, waiting {$retryAfter} seconds");
                sleep(intval($retryAfter));
                return $this->makeRequest($method, $endpoint, $params, $headers);
            }
            
            // Handle token expiration
            if ($response->status() === 401) {
                Cache::forget('ms_graph_token');
                $token = $this->getAccessToken();
                return $this->makeRequest($method, $endpoint, $params, $headers);
            }
            
            // Check for binary content (like images)
            $contentType = $response->header('Content-Type');
            if ($contentType && strpos($contentType, 'image/') === 0) {
                return $response->body();
            }
            
            return $response->json();
            
        } catch (\Exception $e) {
            Log::error("Graph API request failed", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Optimized implementation for user search
     * Uses Laravel's HTTP facade with optimized settings
     */
    private function searchUsersOptimized($params)
    {
        $token = $this->getAccessToken();
        
        // Use Laravel's HTTP facade with optimized settings
        $response = Http::withToken($token)
            ->withHeaders(['ConsistencyLevel' => 'eventual'])
            ->timeout(20) // Increased timeout for search operations
            ->retry(2, 1000) // Retry up to 2 times with 1 second delay
            ->get("{$this->baseUrl}/users", $params);
            
        return $response->json();
    }

    /**
     * Search for users
     *
     * @param string $query Search query
     * @return array Users matching the query
     */
    public function searchUsers($query)
    {
        $params = [
            '$orderby' => 'displayName',
            '$top' => 50,
            '$filter' => "endswith(mail, 'boltoncc.ac.uk')",
            '$select' => "id,displayName,jobTitle,mail,officeLocation,Department,mobilePhone,businessPhones"
        ];

        if ($query) {
            $params['$search'] = "\"displayName:{$query}\" OR \"mail:{$query}\"";
        }

        return $this->makeRequest('GET', '/users', $params);
    }

    /**
     * Execute batch requests
     *
     * @param array $requests Array of request objects
     * @return array Batch response
     */
    public function batchRequest($requests)
    {
        return $this->makeRequest('POST', '/$batch', [
            'requests' => $requests
        ]);
    }

    /**
     * Get user by username
     *
     * @param string $username
     * @return array User data
     */
    public function getUser($username)
    {
        $params = [
            '$select' => "id,displayName,jobTitle,mail,officeLocation,Department,mobilePhone,businessPhones,userPrincipalName,mailNickname,mySite,onPremisesExtensionAttributes,onPremisesSamAccountName"
        ];

        return $this->makeRequest('GET', "/users/{$username}@boltoncollege365.ac.uk", $params);
    }
    
    /**
     * Get user profile photo
     *
     * @param string $userId
     * @return string|null Binary data of photo or null if not found
     */
    public function getUserPhoto($userId)
    {
        try {
            return $this->makeRequest('GET', "/users/{$userId}/photo/\$value");
        } catch (\Exception $e) {
            // Photo might not exist
            if (strpos($e->getMessage(), '404') !== false) {
                return null;
            }
            
            Log::error("Failed to get user photo: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get a user's manager
     *
     * @param string $userId
     * @return array|null Manager data or null if not found
     */
    public function getUserManager($userId) 
    {
        try {
            return $this->makeRequest('GET', "/users/{$userId}/manager");
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                return null; // No manager found
            }
            throw $e;
        }
    }
}