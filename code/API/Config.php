<?php

namespace API;

class Config
{
public $zendeskSubdomain;
    public $zendeskEmail;
    public $zendeskApiToken;
    public $freshdeskSubdomain;
    public $freshdeskToken;

public function __construct(){
    $this->zendeskSubdomain = 'relokia1396';
    $this->zendeskEmail = 'v.tylnyi@relokia.com';
    $this->zendeskApiToken = 'AD8iVJSZK0e7SYeJm743OPoWXfvyIoI5opEhQLUK';

    $this->freshdeskSubdomain = 'relokia-support';
    $this->freshdeskToken = 'EEMTALdkVEQSrsKq2VJ';
}
}