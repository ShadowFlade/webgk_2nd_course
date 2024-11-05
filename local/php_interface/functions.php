<?php

use Bitrix\Main\Loader;


if (!function_exists('array_find')) {
    function array_find($arr, $userCompareFunc) {
        foreach ($arr as $x) {
            if (call_user_func($userCompareFunc, $x) === true)
            {
                return $x;
            }
        }
        return null;
    }
}

if (!function_exists('array_wrap')) {
    function array_wrap($value)
    {
        return is_array($value) ? $value : [$value];
    }
}


if (!function_exists('include_modules')) {
    function include_modules($modulesName, $throwable = false)
    {
        $modulesName = array_wrap($modulesName);

        $errors = [];

        foreach ($modulesName as $moduleName) {
            if (!Loader::includeModule($moduleName)) {
                $errors[] = sprintf('Ошибка при подключении модуля "%s"', $moduleName);
            }
        }

        if ($throwable && !empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        return $errors;
    }
}


use Bitrix\Iblock\IblockTable;

if (!function_exists('session_get')) {
    function session_get($code)
    {
        return $_SESSION[SESSION_DATA_CONTAINER][$code];
    }
}

if (!function_exists('session_set')) {
    function session_set($code, $value)
    {
        if (!array_key_exists(SESSION_DATA_CONTAINER, $_SESSION)) {
            $_SESSION[SESSION_DATA_CONTAINER] = [];
        }

        $_SESSION[SESSION_DATA_CONTAINER][$code] = $value;
    }
}


if (!function_exists('get_iblock_id')) {
    function get_iblock_id($code, $noSession = false)
    {
        $iblocksId = !$noSession ? session_get('iblock_id') : false;

        if ($iblocksId && array_key_exists($code, $iblocksId) && $iblocksId[$code]) {
            return $iblocksId[$code];
        }

        include_modules('iblock');

        $iBlock = IblockTable::getRow([
            'filter' => ['=CODE' => $code],
            'select' => ['ID'],
        ]);

        $iblockId = $iBlock['ID'];

        $iblocksId[$code] = $iblockId;
        session_set('iblock_id', $iblocksId);

        return $iblockId;
    }
}