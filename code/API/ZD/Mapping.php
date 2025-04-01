<?php

namespace API\ZD;

class Mapping
{

    private $ApiClient;

    public function __construct($subdomain, $email, $apiToken)
    {
        $this->ApiClient = new ApiClient($subdomain, $email, $apiToken);
    }
    public function mapPriority($ticket)
    {
        switch($ticket['priority']){
            case 'low':
                return 1;
            case 'normal':
                return 2;
            case 'high':
                return 3;
            case 'urgent':
                return 4;
            default:
                return null;
        }
    }

    public function mapStatus($ticket){
        switch ($ticket['status']) {
            case 'open':
                return 2;
            case 'pending':
                return 3;
            case 'solved':
                return 4;
            default:
                return null;
        }
    }

    public function mapFieldType($zendeskField){

        switch ($zendeskField['type']) {
            case 'text':
                return "custom_text";
            case 'date':
                return "custom_date";
            case 'checkbox':
                return "custom_checkbox";
            case 'tagger':
                return "custom_dropdown";
            default:
                return null;
        }
    }
    public function mapSelectValues($ticketZendeskField)
    {

        switch ($ticketZendeskField['value']) {
            case 'delivery':
                return "Delivery1";
            case 'order':
                return "Order1";
            case 'other':
                return "Other1";
            default:
                return null;
        }

    }
    public function mapGroup($groupId){
        switch ($groupId) {
            case 33728684707601:
                return 203000091151; //support
            case 33733257031953:
                return 203000090776; //finance
            default:
                return null;

        }
    }

    public function mapAgent($agentId){
        switch ($agentId) {
            case 33728697582353:
                return 203004425114; //v.tylnyi@relokia.com
            default:
                return null;

        }
    }
    public function mapUser($userId){
        $data = $this->ApiClient->guzzleQuery("GET", "users/$userId");
        return $data['user'];
    }
    public function mapOrganisation($organisationId){

        $data = $this->ApiClient->guzzleQuery("GET", "organizations/$organisationId");
        return $data['organization'];

    }
}