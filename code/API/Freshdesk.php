<?php

namespace API;

class Freshdesk
{
    private $ApiClientFD;

    public $queryBody;
    public function __construct($subdomain, $apiToken)
    {
        $this->ApiClientFD = new ApiClientFD($subdomain, $apiToken);
    }



    public function setNote(int $ticketId, $queryParams)
    {

        $this->ApiClientFD->guzzleQuery("POST","tickets/$ticketId/notes", $queryParams);


    }

    public function setReply($ticketId, $queryParams)
    {

        $this->queryBody = $queryParams;
        $this->ApiClientFD->guzzleQuery("POST", "tickets/$ticketId/reply", $this->queryBody );

    }
    public function crateTicket(array $ticketData)
    {

        $this->queryBody = $ticketData;

        $data = $this->ApiClientFD->guzzleQuery("POST", "tickets/", $this->queryBody );

        return [
            "id" => $data['id'],
            "custom_fields" => $data['custom_fields'],
        ];



    }

    public function crateCompany($companyName)
    {

        $this->queryBody = ["name" => $companyName];
        $data = $this->ApiClientFD->guzzleQuery("POST", "companies/", $this->queryBody );
        return $data['id'];


    }
    public function searchContact($email){

        $data = $this->ApiClientFD->guzzleQuery("GET", "contacts/autocomplete", ["term"=>$email]);
        return [
            "id" => $data[0]['id'],

        ];
    }
    public function searchCompany($companyName){

        $data = $this->ApiClientFD->guzzleQuery("GET", "companies/autocomplete", ["name"=>$companyName]);
        $companyArr = array_column($data['companies'], 'id', 'name');

        if(!array_key_exists($companyName, $companyArr)){
            return $this->crateCompany($companyName);
        }else {
            return $companyArr[$companyName];
        }

    }
    public function updateUser($userId, $queryParams){
      $this->ApiClientFD->guzzleQuery("PUT", "contacts/$userId", $queryParams);
    }

    public function createUser($userData, $companyName ){

        $this->queryBody = $userData;

        if(!$this->searchContact($userData['email'])){
            if($companyName != null && !$this->searchCompany($companyName)){
                $companyId = $this->crateCompany($companyName);
                $userData['company_id'] = $companyId;
            }else{
                $userData['company_id'] = null;
            }

            $data = $this->ApiClientFD->guzzleQuery("POST", "contacts/", $this->queryBody );

            return $data;
        }else{
            $userId = $this->searchContact($userData['email'])['id'];
            if($companyName != null){
                $companyId = $this->searchCompany($companyName);
                $this->updateUser($userId, ["company_id" => $companyId]);
            }else{
                $companyId = null;
            }
            return [
                "id" => $userId,
                "company_id" => $companyId
            ];
        }


    }
    public function getCompanies(){

        $data = $this->ApiClientFD->guzzleQuery("GET", "contacts/");
        return $data;
    }
    public function fieldInfo(){

        $data = $this->ApiClientFD->guzzleQuery("GET", "ticket_fields/");

        return $data;
    }
    public function updateFieldVal(int $ticketId, string $fieldName, string $value){

        $this->queryBody = [
            "custom_fields" => [$fieldName => $value]
        ];

        $data = $this->ApiClientFD->guzzleQuery("POST", "tickets/$ticketId", $this->queryBody );

    }
}