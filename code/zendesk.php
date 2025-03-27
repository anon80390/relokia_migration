<?php


include __DIR__ . '/vendor/autoload.php';


use GuzzleHttp\Client;

use GuzzleHttp\Pool;


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

$subdomain = 'relokia1396';
$email = 'v.tylnyi@relokia.com';
$apiToken = 'AD8iVJSZK0e7SYeJm743OPoWXfvyIoI5opEhQLUK';

$zendesk = new ZendeskAPI($subdomain, $email, $apiToken);

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

    $zendeskRes = [];
    $pool = new Pool($zendesk->getClient(), $requests($tickets), [
        'concurrency' => 50,
        'fulfilled' => function ($response, $index) use (&$zendeskRes, $tickets) {
            $ticketIndex = intdiv($index, 3);
            $type = $index % 3;

            if ($type === 0) {
                $zendeskRes[$ticketIndex]['comments'] = json_decode($response->getBody()->getContents(), true)['comments'] ?? [];
            } elseif ($type === 1) {
                $zendeskRes[$ticketIndex]['agent'] = json_decode($response->getBody()->getContents(), true)['user'] ?? [];
            } elseif ($type === 2) {
                $zendeskRes[$ticketIndex]['contact'] = json_decode($response->getBody()->getContents(), true)['user'] ?? [];
            }
        },
        'rejected' => function ($reason, $index) {
            echo "Request failed: " . $reason->getMessage() . "\n";
        },
    ]);

    $pool->promise()->wait();

    $page++;
} while (true);
print_r($zendeskRes);
echo "ok";
