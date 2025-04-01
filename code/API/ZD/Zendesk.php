<?php

namespace API\ZD;

class Zendesk
{
    private $ApiClient;
    public $Mapping;
    public function __construct($subdomain, $email, $apiToken)
    {
        $this->ApiClient = new ApiClient($subdomain, $email, $apiToken);
        $this->Mapping = new Mapping($subdomain, $email, $apiToken);
    }

    public function getTickets($page = 1)
    {
            $ticketsInfo = $this->ApiClient->guzzleQuery("GET","tickets", ["page"=>$page]);
            foreach ($ticketsInfo['tickets'] as &$ticket) {
                $ticket = $this->ApiClient->guzzleQuery("GET","tickets/".$ticket['id'], ["include"=>'users,organisations']);
                $ticket['comments'] = $this->ApiClient->guzzleQuery("GET", "tickets/{$ticket['ticket']['id']}/comments.json");
            }

            return $ticketsInfo['tickets'];
    }

    public function fieldInfo($fieldId)
    {
        $data = $this->ApiClient->guzzleQuery("GET", "ticket_fields/$fieldId");
        return $data;
    }


    public function getField($ticket){
        $zendeskFields = [];
        foreach ($ticket['custom_fields'] as $field) {
            $zendeskFields[] = $this->fieldInfo($field['id'])['ticket_field'];
        }
        return $zendeskFields;

    }



}