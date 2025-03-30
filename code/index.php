<?php
include __DIR__ . '/vendor/autoload.php';

use API\Zendesk;
use API\Freshdesk;
use API\Config;

$config = new Config();

$freshdesk = new Freshdesk($config->freshdeskToken, $config->freshdeskSubdomain);
$zendesk = new Zendesk($config->zendeskSubdomain, $config->zendeskEmail, $config->zendeskApiToken);

$zendeskTickets = $zendesk->getTickets();

foreach ($zendeskTickets as $ticket) { //ticket

    $priority = $zendesk->mapPriority($ticket['ticket']);
    $status = $zendesk->mapStatus($ticket['ticket']);

    $ticketNamesUsers = array_column($ticket['users'], 'name', 'id');
    $requesterName = $ticketNamesUsers[$ticket['ticket']['requester_id']];

    $ticketEmailsUsers = array_column($ticket['users'], 'email', 'id');
    $requesterEmail = $ticketEmailsUsers[$ticket['ticket']['requester_id']];


    if($ticket['ticket']['organization_id'] != null){
        $zendeskUserCompany = $zendesk->mapOrganisation($ticket['organization_id'])['name'];
    }else{
        $zendeskUserCompany = null;
    }

    $freshdeskUserData = [
        'name' => $requesterName,
        'email' => $requesterEmail,
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

    $ticketData = [
        "description" => $ticket['ticket']['description'],
        "subject" => $ticket['ticket']['subject'],
        "email" => $requesterEmail,
        "priority" => $priority,
        "status" => $status,
        "cc_emails" => $ticket['ticket']['cc_email'] ?? [],
        "custom_fields" => $custom_fields,
        "company_id" => $zendesk->mapOrganisation($ticket['ticket']['organization_id']),
        //organization_id
    ];

    $ticketId = $freshdesk->crateTicket($ticketData);

    foreach ($ticket['comments']['comments'] as $comment) {
        $freshdesk->setReply($ticketId['id'], $comment['body']);
    }



}


$let = '';

