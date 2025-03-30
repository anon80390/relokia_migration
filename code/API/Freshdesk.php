<?php

namespace API;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Freshdesk
{
    public $client;
    private $token;
    private $subdomain;

    public $queryBody;

    public function __construct($token, $subdomain){

        $this->subdomain = $subdomain;


        $this->token = $token;
        $this->client = new Client(
            [
                'base_uri' => "https://$this->subdomain.freshdesk.com/api/v2/",
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' =>  "Basic " . base64_encode("$this->token:X"),
                ],
            ]
        );
    }

    public function setNote(int $ticketId, string $body, bool $private, array $notifyEmails)
    {
        $this->queryBody = ['json' => [
            "body" => $body,
            "private" => $private,
            "notify_emails" => $notifyEmails
        ]];
        $this->client->post("tickets/$ticketId/notes", $this->queryBody );


    }

    public function setReply(int $ticketId, string $body)
    {

        $this->queryBody = [
            'json' => [
                "body" => $body,
            ]
        ];
        $this->client->post("tickets/$ticketId/reply", $this->queryBody );

    }
    public function crateTicket(array $ticketData)
    {

        $this->queryBody = [
            'json' => $ticketData
        ];
        $response = $this->client->post("tickets/", $this->queryBody );
        $data = json_decode($response->getBody()->getContents(), true);
        return [
            "id" => $data['id'],
            "custom_fields" => $data['custom_fields'],
        ];



    }

    public function crateCompany(array $companytData)
    {

        $this->queryBody = [
            'json' => $companytData
        ];
        $response = $this->client->post("companies/", $this->queryBody );
        $data = json_decode($response->getBody()->getContents(), true);
        return [
            "id" => $data['id'],
        ];



    }
    public function searchContact($email){
        $response = $this->client->get("contacts/autocomplete?term=$email");
        $data = json_decode($response->getBody()->getContents(), true);
        return $data[0]['id']; //data вертає вкладені масиви
    }
    public function searchCompany($companyName){
        $response = $this->client->get("companies/autocomplete?name=$companyName");
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['id'];
    }
    public function createUser($userData, $companyName ){
        $this->queryBody = [
            'json' => $userData
        ];
        if(!$this->searchContact($userData['email'])){
            if($companyName != null && !$this->searchCompany($companyName)){
                $companyId = $this->crateCompany($companyName);
                $userData['company_id'] = $companyId;
            }else{
                $userData['company_id'] = null;
            }

            $response = $this->client->post("contacts/", $this->queryBody );

            $data = json_decode($response->getBody()->getContents(), true);
            return $data;
        }else{
            return $this->searchContact($userData['email']);
        }


    }
    public function getCompanies(){
        $response = $this->client->get("companies");
        $data = json_decode($response->getBody()->getContents(), true);
        return $data;
    }
    public function fieldInfo(){

        $response = $this->client->get("ticket_fields/");

        $data = json_decode($response->getBody()->getContents(), true);

        return $data;
    }
    public function updateFieldVal(int $ticketId, string $fieldName, string $value){

        $this->queryBody = [
            "json" => [
                "custom_fields" => [
                    $fieldName => $value
                ]
            ]
        ];
        $response = $this->client->post("tickets/$ticketId", $this->queryBody );
        $data = json_decode($response->getBody()->getContents(), true);

    }
}