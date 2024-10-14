<?php

namespace Webgk\Handler;

use Webgk\Service\UserService;

class User
{
    public static function OnBeforeUserRegister(&$arFields)
    {
        $userService = new UserService();
        $userService->OnBeforeUserRegister($arFields);
    }

}