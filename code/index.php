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

    $ticketId = $freshdesk->crateTicket($ticket['description'], $ticket['subject'], $ticket['requester']['user']['email'],
        $priority, $status, $ticket['cc_email'] ?? []);


    foreach ($ticket['comments']['comments'] as $comment) {

        $freshdesk->setReply($ticketId, $comment['body']);

    }


}


$let = '';

