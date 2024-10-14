<?php

namespace Webgk\Helper;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\UrlManager;

class AjaxHelper
{
    /**
     * Генерация url для Ajax запросов на основании компонента
     * @param \CBitrixComponent $componentClass
     * @param string $action
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public static function getComponentActionUrl(\CBitrixComponent $componentClass, string $action): string
    {
        return UrlManager::getInstance()->createByBitrixComponent($componentClass, $action, [
            'sessid' => bitrix_sessid()
        ]);
    }

    /**
     * Генерация url для Ajax запросов на основании контроллера
     * @param Controller $controllerClass
     * @param string $action
     * @return string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public static function getControllerActionUrl(\Bitrix\Main\Engine\Controller $controllerClass, string $action): string
    {
        return UrlManager::getInstance()->createByController($controllerClass, $action, [
            'sessid' => bitrix_sessid()
        ]);
    }
}