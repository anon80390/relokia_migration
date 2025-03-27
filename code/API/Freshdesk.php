<?php

namespace API;
use GuzzleHttp\Client;


class Freshdesk
{
    public $client;
    private $token;
    private $subdomain;

    public function __construct($token, $subdomain){

        $this->subdomain = $subdomain;

        $this->client = new Client(['base_uri' => "https://$this->subdomain.freshdesk.com/api/v2/",]);

        $this->token = $token;

    }

    public function setNote(int $ticketId, string $body, bool $private, array $notifyEmails)
    {

        $this->client->request('POST', "tickets/$ticketId/notes", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' =>  "Basic " . base64_encode("$this->token:X"),
            ],
            'json' => [
                "body" => $body,
                "private" => $private,
                "notify_emails" => $notifyEmails
            ]
        ]);

    }

    public function setReply(int $ticketId, string $body)
    {

        $this->client->request('POST', "tickets/$ticketId/reply", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' =>  "Basic " . base64_encode("$this->token:X"),
            ],
            'json' => [
                "body" => $body,
            ]
        ]);

    }
    public function crateTicket(array $ticketData)
    {

        $response =  $this->client->request('POST', "tickets/", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' =>  "Basic " . base64_encode("$this->token:X"),
            ],
            'json' => $ticketData
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        return [
            "id" => $data['id'],
            "custom_fields" => $data['custom_fields'],
        ];



    }

    public function fieldInfo(){

        $response = $this->client->request('GET', "ticket_fields", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' =>  "Basic " . base64_encode("$this->token:X"),
            ],

        ]);

        $data = json_decode($response->getBody()->getContents(), true);

//        return [
//            "title" => $data['label'],
//            "type" => $data['type'],
//        ];
        return $data;
    }
    public function updateFieldVal(int $ticketId, string $fieldName, string $value){
        $response = $this->client->request('PUT', "tickets/$ticketId", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' =>  "Basic " . base64_encode("$this->token:X"),
            ],
            "json" => [
                "custom_fields" => [
                    $fieldName => $value
                ]
            ]

        ]);

        $data = json_decode($response->getBody()->getContents(), true);
    }
}