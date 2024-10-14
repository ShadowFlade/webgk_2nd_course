<?php
namespace Webgk\Handler;

use Webgk\Service\UserService;

class User {
    public static function OnBeforeUserRegister(&$arFields)
    {
        $userService = new UserService();
        $userService->
        \Bitrix\Main\Diag\Debug::writeToFile($arFields, date("d.m.Y H:i:s"), "local/log.log");
    }
}