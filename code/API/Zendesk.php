<?php

namespace API;
class Zendesk
{
    private $ApiClient;
    public function __construct($subdomain, $email, $apiToken)
    {
        $this->ApiClient = new ApiClient($subdomain, $email, $apiToken);
    }

    public function getTickets($page = 1)
    {
            $ticketsInfo = $this->ApiClient->guzzleQuery("GET","tickets", ["page"=>$page]);
            foreach ($ticketsInfo['tickets'] as &$ticket) {
                $ticket = $this->ApiClient->guzzleQuery("GET","tickets/".$ticket['id'], ["include"=>'users']);
                $commentsData = $this->ApiClient->guzzleQuery("GET", "tickets/{$ticket['ticket']['id']}/comments.json");
                $ticket['comments'] = $commentsData;

            }

            return $ticketsInfo['tickets'];
    }
    public function organisationList(){
        $data = $this->ApiClient->guzzleQuery("GET", "organizations");
        return $data['organizations'];
    }

    public function fieldInfo($fieldId)
    {
        $data = $this->ApiClient->guzzleQuery("GET", "ticket_fields/$fieldId");
        return $data;
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
    public function getField($ticket){
        $zendeskFields = [];
        foreach ($ticket['custom_fields'] as $field) {
            $zendeskFields[] = $this->fieldInfo($field['id'])['ticket_field'];
        }
        return $zendeskFields;

    }



}