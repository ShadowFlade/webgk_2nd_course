<?php

namespace Webgk\Service;

class UserService
{
    public $PHYS_REQ_FIELDS;
    public $JUR_REQ_FIELDS;


    public function __construct()
    {
        $this->PHYS_REQ_FIELDS = ['NAME', 'LAST_NAME', 'PASSWORD', 'EMAIL', 'CONFIRM_PASSWORD'];
        $this->JUR_REQ_FIELDS = array_merge($this->PHYS_REQ_FIELDS, ['UF_INN', 'UF_KPP', 'UF_TYPE', 'WORK_COMPANY']);
    }
}