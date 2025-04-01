<?php
include __DIR__ . '/vendor/autoload.php';

use API\Config;
use API\FD\Freshdesk;
use API\ZD\Zendesk;

$config = new Config();

$freshdesk = new Freshdesk($config->freshdeskSubdomain, $config->freshdeskToken);
$zendesk = new Zendesk($config->zendeskSubdomain, $config->zendeskEmail, $config->zendeskApiToken);

$zendeskTickets = $zendesk->getTickets();

foreach ($zendeskTickets as $ticket) { //ticket

    $priority = $zendesk->Mapping->mapPriority($ticket['ticket']);
    $status = $zendesk->Mapping->mapStatus($ticket['ticket']);

    $ticketUsers = array_column($ticket['users'], null, 'id');
    $requester = $ticketUsers[$ticket['ticket']['requester_id']];

    if($ticket['ticket']['organization_id'] != null){
        $zendeskUserCompany = $zendesk->Mapping->mapOrganisation($ticket['ticket']['organization_id'])['name']; //organisation
    }else{
        $zendeskUserCompany = null;
    }

    $freshdeskUserData = [
        'name' => $requester['name'],
        'email' => $requester['email'],
    ];

    $freshdeskUser = $freshdesk->createUser($freshdeskUserData, $zendeskUserCompany ?? null);

    $zendeskFields = $zendesk->getField($ticket['ticket']);

    $freshdeskFields = $freshdesk->fieldInfo();

    $custom_fields = [];


    foreach ($freshdeskFields as $freshdeskField) {
        foreach ($zendeskFields as $zendeskField) {
            if ($freshdeskField['label'] == $zendeskField['title']) {
                foreach ($ticket['ticket']['custom_fields'] as $ticketZendeskField) {
                    if ($ticketZendeskField['id'] == $zendeskField['id']) {


                        if ($zendesk->Mapping->mapFieldType($zendeskField) == "custom_dropdown") {
                            $value = $zendesk->Mapping->mapSelectValues($ticketZendeskField);
                        }else{
                            $value = $ticketZendeskField['value'];
                        }
                        if($zendesk->Mapping->mapFieldType($zendeskField) == "custom_text" && $ticketZendeskField['value'] == null){
                            $value = "";
                        }

                        $custom_fields[$freshdeskField['name']] = $value;
                    }
                }
            }
        }
    }

    $groupId = $zendesk->Mapping->mapGroup($ticket['ticket']['group_id']);
    $agentId = $zendesk->Mapping->mapAgent($ticket['ticket']['assignee_id']);

    $ticketData = [
        "description" => $ticket['ticket']['description'],
        "subject" => $ticket['ticket']['subject'],
        "requester_id" => $freshdeskUser['id'],
        "priority" => $priority,
        "status" => $status,
        "cc_emails" => $ticket['ticket']['cc_email'] ?? [],
        "custom_fields" => $custom_fields,
        "company_id" => $freshdeskUser['company_id'],
        "group_id" => $groupId,
        "responder_id" => $agentId
    ];

    $ticketId = $freshdesk->crateTicket($ticketData);
    unset($ticket['comments']['comments'][0]);


    foreach ($ticket['comments']['comments'] as $comment) {
        if($comment['attachments']){
            $freshdeskAttachment = [];
            foreach ($comment['attachments'] as $attachment) {
               $freshdeskAttachment[] = fopen($attachment['content_url'], 'r');
            }
        }
        if(array_key_exists("body", $comment)){

          $zendeskAuthor = $ticketUsers[$comment['author_id']];
          $zendeskEmail = $zendeskAuthor['email'];

            $queryParams = [
                'body' => $comment['body'],
                'private' => !$comment['public'],
                'user_id' => $freshdesk->searchContact($zendeskEmail)['id'] ?? $agentId,
                'attachments' => $freshdeskAttachment ?? []
            ];

                $freshdesk->setNote($ticketId['id'], $queryParams);

            }


        }





}


$let = '';

