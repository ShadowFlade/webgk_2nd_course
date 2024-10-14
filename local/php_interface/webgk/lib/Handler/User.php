<?php

namespace Webgk\Handler;

use Bitrix\Main\UserFieldTable;
use Webgk\Service\UserService;

class User
{
    public static function OnBeforeUserRegister(&$arFields)
    {
        $userService = new UserService();
        $isOk = true;
        if ($arFields['UF_TYPE'] == 'JURIDICAL') {
            $isOk = self::CheckReqFields($userService->JUR_REQ_FIELDS, $arFields);
        } else if ($arFields['UF_TYPE'] == 'PHYSICAL') {
            $isOk = self::CheckReqFields($userService->PHYS_REQ_FIELDS, $arFields);
        }
        global $APPLICATION;
        $exceptionMessage = 'Не заполнено поля  ' . array_map(fn($item) => $item['LANG_ERROR_MESSAGE'] ?: $item['LANG_NAME'],$arFields['EMPTY_FIELDS']);

        if (!$isOk) {
            \Bitrix\Main\Diag\Debug::writeToFile(['not ok' => $arFields], date("d.m.Y H:i:s"), "local/log.log");
            $APPLICATION->ThrowException($exceptionMessage);
            return false;
        }

    }

    private static function CheckReqFields($reqFields, $arFields)
    {
        $emptyFields = [];
        $hasEmptyFields = false;
        foreach ($reqFields as $key => $reqField) {
            \Bitrix\Main\Diag\Debug::writeToFile('its empty', date("d.m.Y H:i:s"), "local/log.log");
            if (!empty($arFields[$reqField])) return true;
            $hasEmptyFields = true;
            $query = new Query(UserFieldTable::getEntity());
            $query->setSelect([
                'ID',
                'FIELD_NAME',
                'USER_TYPE_ID',
                'MULTIPLE',
                'LANG_NAME' => 'LANG.EDIT_FORM_LABEL',
                'LANG_ERROR' => 'LANG.ERROR_MESSAGE'
            ]);
            $query->setFilter([
                '=ENTITY_ID' => 'USER',
                '=LANG.LANGUAGE_ID' => 'ru',
                '=FIELD_NAME' => $key
            ]);
            $query->registerRuntimeField(
                'LANG',
                [
                    'data_type' => 'Bitrix\Main\UserFieldLangTable',
                    'reference' => [
                        '=this.USER_FIELD_ID' => 'ref.ID'
                    ],
                ]
            );
            $userFields = $query->exec()->fetch();
            $error = $userFields['LANG_NAME'] ?: $userFields['LANG_ERROR_MESSAGE'];
            $userFields['ERROR_MESSAGE'] = "Не заполнено поле $error";;
            $emptyFields[] = $userFields;
            \Bitrix\Main\Diag\Debug::writeToFile(['error' => $error, $userFields], date("d.m.Y H:i:s"), "local/log.log");
        }

        if ($hasEmptyFields) {
            $arFields['EMPTY_FIELDS'] = $emptyFields;
            return false;
        }
        return true;
    }
}