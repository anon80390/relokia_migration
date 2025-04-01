<?php

include __DIR__ . '/vendor/autoload.php';
use API\Config;
use API\FD\Freshdesk;
use GuzzleHttp\Psr7;


$config = new Config();

$freshdesk = new Freshdesk($config->freshdeskSubdomain, $config->freshdeskToken);

$cont = Psr7\Utils::tryFopen('./attachments/attachment_813631.jpg', 'r');
$multipartData = [
    [
        'name' => 'attachments[]',
        'contents' => Psr7\Utils::tryFopen('./attachments/attachment_813631.jpg', 'r'),
    ],
    [
        'name' => 'user_id',
        'contents' => 203004425114
    ],
    [
        'name' => 'body',
        'contents' => '123'
    ]

];
$freshdesk->setNote(214, $multipartData);

$let = '';

