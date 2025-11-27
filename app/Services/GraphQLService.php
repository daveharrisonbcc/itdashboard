<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GraphQLService
{
    protected $client;

    public function __construct()
    {
        $baseOptions = [
            'verify' => false,
            'http_errors' => false,
            'allow_redirects' => true,
            'timeout' => 30,
            'connect_timeout' => 10
        ];
    
        // Pre-generate the auth token
        $authToken = config('wordpress.auth.username') . ':' . config('wordpress.auth.password');
        
        $this->client = Http::withOptions($baseOptions)
            ->withToken($authToken)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Connection' => 'keep-alive',
                'Accept-Encoding' => 'gzip'
            ]);
    }

    public function executeGraphQLQuery(string $query, array $variables = []): array
    {
        $queryType = $this->getQueryType($query);
       
        $cacheKey = "graphql_{$queryType}_" . md5($query . json_encode($variables));
    
        // Try to get from cache first
        // if ($cachedResponse = Cache::get($cacheKey)) {
        //     Log::debug("[GraphQL] Cache hit", [
        //         'type' => $queryType,
        //         'cache_key' => $cacheKey
        //     ]);
        //     return $cachedResponse;
        // }
    
        try {
            $startTime = microtime(true);
            $method = $this->determineRequestMethod($query, $variables);
           
            // Force POST for resource-heavy queries
            if (in_array($queryType, ['posts', 'bccresources'])) {
                $method = 'post';
                Log::debug("[GraphQL] Resource-heavy query detected", [
                    'type' => $queryType,
                    'method' => $method,
                    'variables' => $variables
                ]);
            }
    
            $response = $this->client
                ->withToken(config(key: 'wordpress.auth.username') . ':' . config('wordpress.auth.password'))
                ->withHeaders([
                    'Cache-Control' => $method === 'get' ? 'max-age=300' : 'no-cache',
                    'X-Query-Type' => $queryType
                ])
                ->$method(config('wordpress.graphql_endpoint'), [
                    'query' => $query,
                    'variables' => $variables
                ]);
    
            $duration = (microtime(true) - $startTime) * 1000;
            
            $response->throw();
            $jsonResponse = $response->json();
    
            // Cache successful responses without tags
            if (!isset($jsonResponse['errors'])) {
                $ttl = $this->getCacheTTL($queryType);
           
            }
    
            // Log slow queries in production
            if (app()->environment('production') && $duration > 1000) {
                Log::info("[GraphQL] Slow query", [
                    'type' => $queryType,
                    'method' => $method,
                    'duration' => round($duration, 2) . 'ms'
                ]);
            }
    
            return $jsonResponse;
    
        } catch (Exception $e) {
            Log::error("[GraphQL] Request failed", [
                'type' => $queryType,
                'method' => $method ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function determineRequestMethod(string $query, array $variables): string
    {
        // Always use POST for mutations
        if ($this->isQueryMutation($query)) {
            return 'post';
        }

        // Calculate total URL length
        $urlLength = strlen(config('wordpress.graphql_endpoint')) + 
                    strlen('query=') + 
                    strlen($query) + 
                    strlen('variables=') + 
                    strlen(json_encode($variables));

        // Use POST if URL would be too long (>2000 chars is common limit)
        if ($urlLength > 1800) {
            Log::debug("[GraphQL] Using POST due to query size", [
                'url_length' => $urlLength
            ]);
            return 'post';
        }

        return 'get';
    }

    protected function isQueryMutation(string $query): bool
    {
        return str_starts_with(trim($query), 'mutation');
    }
    protected function getQueryType(string $query): string
    {
        // Extract operation name or first query type
        if (preg_match('/(?:query|mutation)\s+(\w+)/', $query, $matches)) {
            return $matches[1];
        }
        
        // Default to first field name
        preg_match('/{[\s\n]*(\w+)/', $query, $matches);
        return $matches[1] ?? 'unknown';
    }

    protected function getCacheTTL(string $queryType): int
    {
        return match($queryType) {
            'events' => 60,      // Cache events for 1 hour
            'news' => 30,        // Cache news for 30 minutes
            'pages' => 1440,     // Cache pages for 24 hours
            'menu' => 1440,      // Cache menus for 24 hours
            default => 5         // Cache other queries for 5 minutes
        };
    }

}