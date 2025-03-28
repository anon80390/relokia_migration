<?php

namespace API;
use GuzzleHttp\Client;
class Zendesk
{
    public $client;
    public $subdomain;
    public $apiToken;
    public $email;

    public function __construct($subdomain, $email, $apiToken)
    {
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

    public function getClient()
    {
        return $this->client;
    }

    public function getTickets($page = 1)
    {
        try {
            $response = $this->client->get("tickets", ["page"=>$page]);
            $data = json_decode($response->getBody()->getContents(), true);
            foreach ($data['tickets'] as &$ticket) {

                $comments = $this->client->get("tickets/{$ticket['id']}/comments.json");
                $commentsData = json_decode($comments->getBody()->getContents(), true);

                $assignee = $this->client->get("users/{$ticket['assignee_id']}.json");
                $assigneeData = json_decode($assignee->getBody()->getContents(), true);

                $requester = $this->client->get("users/{$ticket['requester_id']}.json");
                $requesterData = json_decode($requester->getBody()->getContents(), true);

                $ticket['comments'] = $commentsData;
                $ticket['assignee'] = $assigneeData;
                $ticket['requester'] = $requesterData;

            }

            return $data['tickets'];

        } catch (\Exception $e) {
            echo "Error fetching tickets: " . $e->getMessage();
            return [];
        }
    }
    public function organisationList(){
        $response = $this->client->get("organizations.json");
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['organizations'];
    }

    public function fieldInfo($fieldId)
    {

        $response = $this->client->get("ticket_fields/$fieldId.json");
        $data = json_decode($response->getBody()->getContents(), true);
        return $data;
    }

    public function mapPriority($ticket)
    {
        if($ticket['priority'] == 'low'){
            $responce = 1;
        }else if($ticket['priority'] == 'normal'){
            $responce= 2;
        }else if($ticket['priority'] == 'high'){
            $responce = 3;
        }else if($ticket['priority'] == 'urgent'){
            $responce = 4;
        }
        return $responce;
    }

    public function mapStatus($ticket){
        if($ticket['status'] == 'open'){
            $responce = 2;
        }elseif($ticket['status'] == 'pending'){
            $responce = 3;
        }else if($ticket['status'] == 'solved'){
            $responce = 4;
        }
        return $responce;
    }

    public function mapFieldType($zendeskField){
        if($zendeskField['type'] == 'tagger'){
            $result = "custom_dropdown";
        }elseif($zendeskField['type'] == 'checkbox'){
            $result = "custom_checkbox";
        }elseif($zendeskField['type'] == 'text'){
            $result = "custom_text";
        }elseif($zendeskField['type'] == 'date'){
            $result = "custom_date";
        }
        return $result;
    }
public function mapSelectValues($ticketZendeskField)
{
    if($ticketZendeskField['value'] == 'delivery'){
        $result = "Delivery1";
    }  elseif($ticketZendeskField['value'] == 'order'){
    $result = "Order1";
    }elseif($ticketZendeskField['value'] == 'other'){
        $result = "Other1";
    }

    return $result;

}
public function mapUser($userId){
    $response = $this->client->get("users/$userId.json");
    $data = json_decode($response->getBody()->getContents(), true);
    return $data['user'];

    //	"name": "The Customer",
    //		"email": "customer@example.com",
//    		"organization_id": null,
}
public function mapOrganisation($organisationId){
//        if($organisationId == 25594811442578){
//            $result = 203000296430; //relokia
//        }else{
//            $result = null;
//        }
//        return $result;
    $response = $this->client->get("organizations/$organisationId.json");
    $data = json_decode($response->getBody()->getContents(), true);
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