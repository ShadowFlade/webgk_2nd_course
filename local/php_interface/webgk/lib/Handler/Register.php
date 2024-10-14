<?php
namespace Webgk\Handler;

class Register
{
    private static $eventManager;

    public static function initHandlers()
    {
        self::$eventManager = \Bitrix\Main\EventManager::getInstance();
        self::initIblockHandlers();
        self::initMainHandlers();
        self::initSaleHandlers();
        self::initCatalogHandlers();
        self::initUserHandlers();

    }

    private static function initIblockHandlers()
    {

    }

    private static function initMainHandlers()
    {

    }

    private static function initSaleHandlers()
    {

    }

    private static function initCatalogHandlers()
    {

    }

    private static function initUserHandlers()
    {
        \Bitrix\Main\Diag\Debug::writeToFile('user handlers', date("d.m.Y H:i:s"), "local/log.log");

        self::$eventManager->addEventHandler('main', 'OnBeforeUserRegister', ['\Webgk\Handler\User', 'OnBeforeUserRegister']);
    }
}

