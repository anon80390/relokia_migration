<?php

namespace API;
use GuzzleHttp\Client;
class ApiClient
{
private $client;
public function __construct($subdomain, $email, $apiToken){
    $this->subdomain = $subdomain;
    $this->email = $email;
    $this->apiToken = $apiToken;

    $this->client = new Client([
        'base_uri' => "https://$subdomain.zendesk.com/api/v2/",
        'auth' => ["$email/token", $apiToken],
        'timeout' => 30,
        'connect_timeout' => 5,
        'headers' => [
            "Content-Type" => "application/json",
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