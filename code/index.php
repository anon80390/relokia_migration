<?php
include __DIR__ . '/vendor/autoload.php';

use API\Zendesk;
use API\Freshdesk;
use API\Config;

$config = new Config();

$freshdesk = new Freshdesk($config->freshdeskToken, $config->freshdeskSubdomain);

$zendesk = new Zendesk($config->zendeskSubdomain, $config->zendeskEmail, $config->zendeskApiToken);
$zendeskTickets = $zendesk->getTickets();

foreach ($zendeskTickets as $ticket) {

    $priority = $zendesk->mapPriority($ticket);
    $status = $zendesk->mapStatus($ticket);

foreach ($ticket['custom_fields'] as $field) {
    $zendeskFields[] = $zendesk->fieldInfo($field['id'])['ticket_field']; //zendesk fields with title & type
}
    $freshdeskFields = $freshdesk->fieldInfo(); // all fields freshdesk

    foreach ($freshdeskFields as $freshdeskField) { //label=Topic name=cf_topic type=custom_dropdown

     foreach ($zendeskFields as $zendeskField) { //title=Topic type=target id=25594841813138
         if($freshdeskField['label'] == $zendeskField['title']) {
             foreach ($ticket['fields'] as $ticketZendeskField) {
                 if($ticketZendeskField['id'] == $zendeskField['id']) {

                     $custom_fields = [
                         $freshdeskField['name'] => $ticketZendeskField['value'] ,
                     ];
                     $ticketId = $freshdesk->crateTicket($ticket['description'], $ticket['subject'], $ticket['requester']['user']['email'],
                         $priority, $status, $ticket['cc_email'] ?? [], $custom_fields);

                 }
             }
         }

     }
    }



//    $fields = $zendesk->fieldInfo()



//    $custom_fields = [
//      'cf_topic' => 'Delivery'
//    ];
//    $ticketId = $freshdesk->crateTicket($ticket['description'], $ticket['subject'], $ticket['requester']['user']['email'],
//        $priority, $status, $ticket['cc_email'] ?? [], $custom_fields);




    foreach ($ticket['comments']['comments'] as $comment) {

        $freshdesk->setReply($ticketId['id'], $comment['body']);

    }


}


$let = '';

