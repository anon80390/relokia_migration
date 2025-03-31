<?php

namespace API;
use GuzzleHttp\Client;
class ApiClientFD
{
    private $client;
    public function __construct($subdomain, $apiToken){
        $this->subdomain = $subdomain;
        $this->apiToken = $apiToken;

        $this->client = new Client([
            'base_uri' => "https://$this->subdomain.freshdesk.com/api/v2/",
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' =>  "Basic " . base64_encode("$this->apiToken:X"),
                    ]
        ]);
    }
    public function guzzleQuery($method, $endpoint, $queryBody = []){
        $options = [];
        if ($method === 'GET' && !empty($queryBody)) {
            $options['query'] = $queryBody;
        }elseif(in_array($method, ['POST', 'PUT', 'DELETE'])){
            $options['json'] = $queryBody;
        }
        $res = $this->client->request($method, $endpoint, $options);
        return json_decode($res->getBody()->getContents(), true);
    }
}