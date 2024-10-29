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
        self::$eventManager->addEventHandler('main', 'OnBuildGlobalMenu', ['\Webgk\Singletons\Exchange1C', 'ModifyAdminMenu']);
    }

    private static function initSaleHandlers()
    {

    }

    private static function initCatalogHandlers()
    {
        self::$eventManager->addEventHandler("catalog", "OnGetOptimalPrice", ['\Webgk\Handler\Catalog', 'GetOptimalPrice']);
    }

    private static function initUserHandlers()
    {

        self::$eventManager->addEventHandler('main', 'OnBeforeUserRegister', ['\Webgk\Handler\User', 'OnBeforeUserRegister']);
    }
}

