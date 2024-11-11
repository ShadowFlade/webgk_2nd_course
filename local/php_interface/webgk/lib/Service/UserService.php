<?php

namespace Webgk\Service;

use Bitrix\Main\UserFieldTable;
use Bitrix\Main\Entity\Query;


class UserService
{
    public $PHYS_REQ_FIELDS;
    public $JUR_REQ_FIELDS;

    public $JUR_TYPE = 'JURIDICAL';
    public $PHYS_TYPE = 'PHYSICAL';


    public function __construct()
    {
        $this->PHYS_REQ_FIELDS = ['NAME', 'LAST_NAME', 'PASSWORD', 'EMAIL', 'CONFIRM_PASSWORD'];
        $this->JUR_REQ_FIELDS = array_merge($this->PHYS_REQ_FIELDS, ['UF_INN', 'UF_KPP', 'UF_TYPE', 'WORK_COMPANY']);
    }

    public function CheckCaptcha($data)
    {
        $captchaService = new CaptchaService($data);
        return $captchaService->IS_VALID;
    }

    public function OnBeforeUserRegister(&$arFields)
    {
        \Bitrix\Main\Diag\Debug::writeToFile(['on before'], date("d.m.Y H:i:s"), "local/log.log");

        global $APPLICATION;

        $isOk = true;
        if ($arFields['UF_TYPE'] == 'JURIDICAL') {
            $isOk = self::CheckReqFields($this->JUR_REQ_FIELDS, $arFields);
            $arFields['IS_JUR'] = true;
        } else if ($arFields['UF_TYPE'] == 'PHYSICAL') {
            $arFields['IS_PHYS'] = true;
            $isOk = self::CheckReqFields($this->PHYS_REQ_FIELDS, $arFields);
        } else {
            $APPLICATION->ThrowException('Тип пользователя (физическое лицо или юридическое лицо) не выбран');
        }


        if (!$isOk) {
            $exceptionMessage = 'Не заполнено поля:  ' . array_map(fn($item) => $item['LANG_ERROR_MESSAGE']
                    ?: $item['LANG_NAME'], $arFields['EMPTY_FIELDS']);
            \Bitrix\Main\Diag\Debug::writeToFile(['not ok' => $arFields], date("d.m.Y H:i:s"), "local/log.log");
            $APPLICATION->ThrowException($exceptionMessage);
            return false;
        } else {
            $arFields['OK'] = true;
            return $arFields;
        }


    }

    private function CheckReqFields($reqFields, $arFields)
    {
        $emptyFields = [];
        $hasEmptyFields = false;
        foreach ($reqFields as $key => $reqField) {
            \Bitrix\Main\Diag\Debug::writeToFile('is it empty', date("d.m.Y H:i:s"), "local/log.log");
            if (!empty($arFields[$reqField])) return true;
            $hasEmptyFields = true;
            $userFields = $this->GetCustomUserField($key);
            \Bitrix\Main\Diag\Debug::writeToFile(['user fields  custom' => $userFields], date("d.m.Y H:i:s"), "local/log.log");

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

    public function CreateUser($REQUEST)
    {

        \Bitrix\Main\Diag\Debug::writeToFile(['create user request' => $REQUEST], date("d.m.Y H:i:s"), "local/log.log");

        $fields = $this->OnBeforeUserRegister($REQUEST);;
        if (empty($fields['OK'])) return;
        $user = new \CUser;
        $newFields = [
            'EMAIL' => $fields['EMAIL'],
            'LOGIN' => $fields['EMAIL'],
            'PASSWORD' => $fields['PASSWORD'],
            'CONFIRM_PASSWORD' => $fields['CONFIRM_PASSWORD'],
            'NAME' => $fields['NAME'],
            'LAST_NAME' => $fields['LAST_NAME'],
            'WORK_COMPANY' => $fields['WORK_COMPANY'],
            'UF_INN' => (int)$fields['UF_INN'],
            'UF_KPP' => $fields['UF_KPP'],
            'GROUP_ID' => [
                USER_GROUP_CAN_VOTE_RESPECT_ID,
                USER_GROUP_CAN_VOTE_RATING_ID,
                USER_GROUP_REGISTERED_ID
            ]
        ];
        \Bitrix\Main\Diag\Debug::writeToFile(['create user fields' => $fields], date("d.m.Y H:i:s"), "local/log.log");

        if (!empty($fields['UF_TYPE'])) {
            $ufType = $this->GetCustomUserField('UF_TYPE', true);
            $sec1 = array_filter($ufType['VARIANTS'], fn($variant) => $variant['XML_ID'] == $REQUEST['UF_TYPE']);
            $selectedVariant = $sec1[$fields['UF_TYPE']]['ID'];
            \Bitrix\Main\Diag\Debug::writeToFile(['variant' => $selectedVariant], date("d.m.Y H:i:s"), "local/log.log");

            $newFields['UF_TYPE'] = $selectedVariant;
        }

        if ($REQUEST['UF_TYPE'] == $this->JUR_TYPE) {
            $filter =
                [
                    "STRING_ID" => USER_GROUP_CODE_PARTNERS,
                    "ACTIVE" => "Y",
                ];
            $rsGroups = \CGroup::GetList(false, $order = "desc", $filter);
            $partnerGroup = $rsGroups->Fetch();

            if (!empty($partnerGroup['ID'])) {
                $newFields['GROUP_ID'][] = $partnerGroup['ID'];
            }
        }
        $userId = $user->Add($newFields);

        return ['ID' => $userId, 'ERROR' => $user->LAST_ERROR];
    }

    public function GetCustomUserField($fieldName, $isGetVariants = false)
    {
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
            '=FIELD_NAME' => $fieldName
        ]);
        $query->registerRuntimeField(
            'LANG',
            [
                'data_type' => 'Bitrix\Main\UserFieldLangTable',
                'reference' => [
                    '=this.ID' => 'ref.USER_FIELD_ID'
                ],
            ]
        );
        $userFields = $query->exec()->fetch();


        if ($isGetVariants && !empty($userFields) && $userFields['USER_TYPE_ID'] == 'enumeration') {
            $userFields['VARIANTS'] = [];
            $enum = new \CUserFieldEnum();
            $rsEnum = $enum->GetList([], ['USER_FIELD_ID' => $userFields['ID']]);

            while ($enumValue = $rsEnum->Fetch()) {
                $userFields['VARIANTS'][$enumValue['XML_ID']] = $enumValue;
            }
        }
        \Bitrix\Main\Diag\Debug::writeToFile(['user fields from custom' => $userFields], date("d.m.Y H:i:s"), "local/log.log");

        return $userFields;
    }
}