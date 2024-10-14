<?php

namespace Webgk\Service;

class UserService
{
    public $PHYS_REQ_FIELDS;
    public $JUR_REQ_FIELDS;

    public function __construct()
    {
        $this->PHYS_REQ_FIELDS = ['UF_NAME', 'UF_LAST_NAME', 'UF_PASSWORD', 'UF_EMAIL', 'UF_CONFIRM_PASSWORD', 'UF_TYPE'];
        $this->JUR_REQ_FIELDS = array_merge($this->PHYS_REQ_FIELDS, ['UF_INN', 'UF_KPP', 'UF_TYPE']);
    }
}