<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PythonService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('python.base_url'),
            'timeout'  => config('python.timeout'),
        ]);
    }

    public function call($method, $endpoint, $data = [])
    {
        try {
            $options = [];

            if (strtoupper($method) === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }

            $response = $this->client->request($method, $endpoint, $options);

            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true),
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'status' => $e->getCode(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
