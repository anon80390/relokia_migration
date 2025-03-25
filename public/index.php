<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class ZendeskAPI
{
    private $client;
    private $subdomain;
    private $apiToken;
    private $email;

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
        ]);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getTickets($page = 1)
    {
        try {
            $response = $this->client->get("tickets.json?page=$page");
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['tickets'];
        } catch (\Exception $e) {
            echo "Error fetching tickets: " . $e->getMessage();
            return [];
        }
    }
}

class TicketCSVExporter
{
    private $file;

    public function __construct($filename)
    {
        $this->file = fopen($filename, 'w');
    }

    public function writeHeaders()
    {
        $headers = [
            'Ticket ID', 'Description', 'Status', 'Priority', 'Agent ID', 'Agent Name', 'Agent Email',
            'Contact ID', 'Contact Name', 'Contact Email', 'Group ID', 'Group Name', 'Company ID', 'Company Name', 'Comments'
        ];
        fputcsv($this->file, $headers);
    }

    public function writeTicket($ticket, $comments, $agent, $contact)
    {
        $commentsText = '';
        if (is_array($comments)) {
            $commentsText = implode('; ', array_map(function ($comment) {
                return $comment['body'] ?? '';
            }, $comments));
        }

        $data = [
            $ticket['id'] ?? '',
            $ticket['description'] ?? '',
            $ticket['status'] ?? '',
            $ticket['priority'] ?? '',
            $agent['id'] ?? '',
            $agent['name'] ?? '',
            $agent['email'] ?? '',
            $contact['id'] ?? '',
            $contact['name'] ?? '',
            $contact['email'] ?? '',
            $ticket['group_id'] ?? '',
            $ticket['group_name'] ?? '',
            $ticket['company_id'] ?? '',
            $ticket['company_name'] ?? '',
            $commentsText
        ];

        fputcsv($this->file, $data);
    }

    public function close()
    {
        fclose($this->file);
    }
}

$subdomain = 'relokia9386';
$email = 'anon80390@gmail.com';
$apiToken = 'WaKtJRW7b0cNYdL5zRl26ggLdXsFR4fz6RVnRoLs';

$zendesk = new ZendeskAPI($subdomain, $email, $apiToken);
$csvExporter = new TicketCSVExporter('tickets.csv');

$startTime = microtime(true);
$csvExporter->writeHeaders();

$page = 1;
do {
    $tickets = $zendesk->getTickets($page);
    if (empty($tickets)) break;

    $requests = function ($tickets) use ($zendesk) {
        foreach ($tickets as $ticket) {
            yield function () use ($zendesk, $ticket) {
                return $zendesk->getClient()->getAsync("tickets/{$ticket['id']}/comments.json");
            };
            yield function () use ($zendesk, $ticket) {
                return $zendesk->getClient()->getAsync("users/{$ticket['assignee_id']}.json");
            };
            yield function () use ($zendesk, $ticket) {
                return $zendesk->getClient()->getAsync("users/{$ticket['requester_id']}.json");
            };
        }
    };

    $results = [];
    $pool = new Pool($zendesk->getClient(), $requests($tickets), [
        'concurrency' => 50,
        'fulfilled' => function ($response, $index) use (&$results, $tickets) {
            $ticketIndex = intdiv($index, 3);
            $type = $index % 3;

            if ($type === 0) {
                $results[$ticketIndex]['comments'] = json_decode($response->getBody()->getContents(), true)['comments'] ?? [];
            } elseif ($type === 1) {
                $results[$ticketIndex]['agent'] = json_decode($response->getBody()->getContents(), true)['user'] ?? [];
            } elseif ($type === 2) {
                $results[$ticketIndex]['contact'] = json_decode($response->getBody()->getContents(), true)['user'] ?? [];
            }
        },
        'rejected' => function ($reason, $index) {
            echo "Request failed: " . $reason->getMessage() . "\n";
        },
    ]);

    $pool->promise()->wait();

    foreach ($tickets as $index => $ticket) {
        $csvExporter->writeTicket($ticket, $results[$index]['comments'] ?? [], $results[$index]['agent'] ?? [], $results[$index]['contact'] ?? []);
    }

    $page++;
} while (true);

$csvExporter->close();

$endTime = microtime(true);
$executionTime = $endTime - $startTime;
echo "Tickets have been exported to 'tickets.csv'.\n";
echo "Execution time: " . number_format($executionTime, 4) . " seconds.\n";