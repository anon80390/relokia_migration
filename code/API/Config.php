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
    $this->zendeskSubdomain = 'relokia4065';
    $this->zendeskEmail = 'v.tylnyi@relokia.com';
    $this->zendeskApiToken = 'PpdtFPzA7D5IrlJqwb2hsbwDxglT4aM7SzMoEWBZ';

    $this->freshdeskSubdomain = 'relokia-support';
    $this->freshdeskToken = 'EEMTALdkVEQSrsKq2VJ';
}
}