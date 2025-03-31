<?php
include __DIR__ . '/vendor/autoload.php';

use API\Zendesk;
use API\Freshdesk;
use API\Config;

$config = new Config();

$freshdesk = new Freshdesk($config->freshdeskSubdomain, $config->freshdeskToken);
$zendesk = new Zendesk($config->zendeskSubdomain, $config->zendeskEmail, $config->zendeskApiToken);

$zendeskTickets = $zendesk->getTickets();

foreach ($zendeskTickets as $ticket) { //ticket

    $priority = $zendesk->mapPriority($ticket['ticket']);
    $status = $zendesk->mapStatus($ticket['ticket']);

    $ticketUsers = array_column($ticket['users'], null, 'id');
    $requester = $ticketUsers[$ticket['ticket']['requester_id']];

    if($ticket['ticket']['organization_id'] != null){
        $zendeskUserCompany = $zendesk->mapOrganisation($ticket['ticket']['organization_id'])['name'];
    }else{
        $zendeskUserCompany = null;
    }

    $freshdeskUserData = [
        'name' => $requester['name'],
        'email' => $requester['email'],
    ];

    $freshdeskUser = $freshdesk->createUser($freshdeskUserData, $zendeskUserCompany);

    $zendeskFields = $zendesk->getField($ticket['ticket']);

    $freshdeskFields = $freshdesk->fieldInfo();

    $custom_fields = [];


    foreach ($freshdeskFields as $freshdeskField) {
        foreach ($zendeskFields as $zendeskField) {
            if ($freshdeskField['label'] == $zendeskField['title']) {
                foreach ($ticket['ticket']['custom_fields'] as $ticketZendeskField) {
                    if ($ticketZendeskField['id'] == $zendeskField['id']) {


                        if ($zendesk->mapFieldType($zendeskField) == "custom_dropdown") {
                            $value = $zendesk->mapSelectValues($ticketZendeskField);
                        }else{
                            $value = $ticketZendeskField['value'];
                        }
                        if($zendesk->mapFieldType($zendeskField) == "custom_text" && $ticketZendeskField['value'] == null){
                            $value = "";
                        }

                        $custom_fields[$freshdeskField['name']] = $value;
                    }
                }
            }
        }
    }

    $groupId = $zendesk->mapGroup($ticket['ticket']['group_id']);
    $agentId = $zendesk->mapAgent($ticket['ticket']['assignee_id']);
    $ticketData = [
        "description" => $ticket['ticket']['description'],
        "subject" => $ticket['ticket']['subject'],
        "email" => $requester['email'],
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
        if(array_key_exists("body", $comment)){

          $zendeskAuthor = $ticketUsers[$comment['author_id']];

            $queryParams = [
                'body' => $comment['body'],
//                'from_email' => $zendeskAuthor['email']
            ];

            if($comment['public'] == true){
                $freshdesk->setReply($ticketId['id'], $queryParams);
            }else{
//                unset($queryParams['user_id']);
                $queryParams['private'] = true;
//                $queryParams['notify_emails'] =  ;
                $freshdesk->setNote($ticketId['id'], $queryParams);

            }


        }

    }



}


$let = '';

