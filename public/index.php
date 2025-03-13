<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;

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
            'auth' => ["$email/token", $apiToken]
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

    public function getTicketComments($ticketId)
    {
        try {
            $response = $this->client->get("tickets/$ticketId/comments.json");
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['comments'];
        } catch (\Exception $e) {
            echo "Error fetching comments for ticket $ticketId: " . $e->getMessage();
            return [];
        }
    }

    public function getUserInfo($userId)
    {
        try {
            $response = $this->client->get("users/$userId.json");
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['user'];
        } catch (\Exception $e) {
            echo "Error fetching user info for ID $userId: " . $e->getMessage();
            return null;
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
        $data = [
            $ticket['id'],
            $ticket['description'],
            $ticket['status'],
            $ticket['priority'],
            $agent ? $agent['id'] : '',
            $agent ? $agent['name'] : '',
            $agent ? $agent['email'] : '',
            $contact ? $contact['id'] : '',
            $contact ? $contact['name'] : '',
            $contact ? $contact['email'] : '',
            $ticket['group_id'],
            $ticket['group_name'],
            $ticket['company_id'],
            $ticket['company_name'],
            implode('; ', array_map(function ($comment) {
                return $comment['body'];
            }, $comments))
        ];

        fputcsv($this->file, $data);
    }

    public function close()
    {
        fclose($this->file);
    }
}

$subdomain = 'relokia9386'; // Replace with your Zendesk subdomain
$email = 'anon80390@gmail.com'; // Your email
$apiToken = 'WaKtJRW7b0cNYdL5zRl26ggLdXsFR4fz6RVnRoLs'; // Your API token

$zendesk = new ZendeskAPI($subdomain, $email, $apiToken);
$csvExporter = new TicketCSVExporter('tickets.csv');

// Start time
$startTime = microtime(true);

// Write headers to CSV
$csvExporter->writeHeaders();

$page = 1;
do {
    $tickets = $zendesk->getTickets($page);
    $promises = [];

    foreach ($tickets as $ticket) {
        // Асинхронный запрос для комментариев
        $commentsPromise = $zendesk->getClient()->getAsync("tickets/{$ticket['id']}/comments.json")
            ->then(function ($response) use ($ticket) {
                return json_decode($response->getBody()->getContents(), true)['comments'];
            });

        // Асинхронные запросы для информации о пользователях (агент и контакт)
        $agentPromise = $zendesk->getClient()->getAsync("users/{$ticket['assignee_id']}.json")
            ->then(function ($response) {
                return json_decode($response->getBody()->getContents(), true)['user'];
            });

        $contactPromise = $zendesk->getClient()->getAsync("users/{$ticket['requester_id']}.json")
            ->then(function ($response) {
                return json_decode($response->getBody()->getContents(), true)['user'];
            });

        // Ожидание всех запросов для текущего тикета
        $promises[] = Utils::all([$commentsPromise, $agentPromise, $contactPromise])
            ->then(function ($results) use ($ticket, $csvExporter) {
                list($comments, $agent, $contact) = $results;
                $csvExporter->writeTicket($ticket, $comments, $agent, $contact);
            });
    }

    // Ожидание всех промисов для текущей страницы
    Utils::settle($promises)->wait();

    $page++;
} while (count($tickets) > 0); // Продолжать, пока есть тикеты

$csvExporter->close();

// End time
$endTime = microtime(true);

// Calculate and display execution time
$executionTime = $endTime - $startTime;
echo "Tickets have been exported to 'tickets.csv'.\n";
echo "Execution time: " . number_format($executionTime, 4) . " seconds.\n";