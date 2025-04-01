<?php

namespace API\FD;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
class ApiClientFD
{
    private $client;

    public function __construct($subdomain, $apiToken){
        $this->subdomain = $subdomain;
        $this->apiToken = $apiToken;

        $this->client = new Client([
            'base_uri' => "https://$this->subdomain.freshdesk.com/api/v2/",

        ]);
    }
    public function guzzleQuery($method, $endpoint, $queryBody = [], $multipart = null){
        $options = [];
        $contentType = 'application/json';
        if ($method === 'GET' && !empty($queryBody)) {
            $options['query'] = $queryBody;

        }elseif(in_array($method, ['POST', 'PUT', 'DELETE'])){

            if($multipart){
                $contentType = 'multipart/form-data';
                $options['multipart'] = $multipart;
            }else{
                $options['json'] = $queryBody;
            }
        }
        $options['headers'] = [

                'Content-Type' => $contentType,
                'Authorization' =>  "Basic " . base64_encode("$this->apiToken:X"),

        ];
        try {
            $res = $this->client->request($method, $endpoint, $options);
//            return json_decode($res->getBody()->getContents(), true);
        }  catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
        }
    }
}