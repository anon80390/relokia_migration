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
    public function crateTicket(string $description, string $subject, string $email, int $priority, int $status,
                                array $cc_emails = [])
    {

        $response =  $this->client->request('POST', "tickets/", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' =>  "Basic " . base64_encode("$this->token:X"),
            ],
            'json' => [
                "description" => $description,
                "subject" => $subject,
                "email" => $email,
                "priority" => $priority,
                "status" => $status,
                "cc_emails" => $cc_emails,

            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['id'];


    }
}