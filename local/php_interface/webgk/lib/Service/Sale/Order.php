<?php

namespace Webgk\Service\Sale;

use Bitrix\Sale\PropertyValueCollectionBase;

class Order
{
    private $order;

    public function __construct(\Bitrix\Sale\Order $order)
    {
        $this->order = $order;
    }

    public function initUserFieldsByPayerType(int $payerTypeId): array
    {
        $props = $this->order->getPropertyCollection();
        if ($payerTypeId === PAYER_TYPE_PHYS) {
            $this->initPhysUserFields($props);
        } else if ($payerTypeId == PAYER_TYPE_JUR) {
            $this->initJurUserFields($props);
        }

        return [];
    }

    private function initPhysUserFields(PropertyValueCollectionBase $props)
    {
        foreach ($props as $prop) {
            /** @var \Bitrix\Sale\PropertyValue $prop */
            $arUser = null;
            switch ($prop->getField('CODE')) {//refactor it with match
                case 'FIO':
                    global $USER;

                    if (empty($arUser)) {
                        $arUser = $USER->GetByID(intval($USER->GetID()))->Fetch();
                    }

                    if (is_array($arUser)) {
                        $fio = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
                        $fio = trim($fio);
                        if (empty($fio)) {
                            $prop->setValue($prop->getProperty()['DEFAULT_VALUE']);
                        } else {
                            $prop->setValue($fio);
                        }
                    }
                    break;

                case 'EMAIL':
                    if (empty($arUser)) {
                        $arUser = $USER->GetByID(intval($USER->GetID()))->Fetch();
                    }
                    $userEmail = $arUser['EMAIL'];
                    $defEmail = $prop->getField('DEFAULT_VALUE');
                    $userEmail ? $prop->setValue($userEmail) : $prop->setValue($defEmail);
                    break;
                default:
                    break;
            }
        }
    }

    private function initJurUserFields(PropertyValueCollectionBase $props)
    {
        foreach ($props as $prop) {
            /** @var \Bitrix\Sale\PropertyValue $prop */
            $arUser = null;
            global $USER;
            switch ($prop->getField('CODE')) {//refactor it with match
                case 'COMPANY':
                    if (empty($arUser)) {
                        $arUser = $USER->GetByID(intval($USER->GetID()))->Fetch();
                    }

                    !empty($arUser['WORK_COMPANY'])
                        ? $prop->setValue($arUser['WORK_COMPANY'])
                        : $prop->setValue($prop->getField('DEFAULT_VALUE'));
                    break;

                case 'INN':
                    if (!empty($arUser) && !empty($arUser['UF_INN'])) {
                        $prop->setValue($arUser['WORK_COMPANY']);
                    } else {
                        $prop->setValue($prop->getField('DEFAULT_VALUE'));
                    }
                    break;
                case 'KPP':
                    if (!empty($arUser) && !empty($arUser['UF_KPP'])) {
                        $prop->setValue($arUser['UF_KPP']);
                    } else {
                        $prop->setValue($prop->getField('DEFAULT_VALUE'));
                    }
                    break;
                case 'CONTACT_PERSON':
                    if (is_array($arUser)) {
                        $fio = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
                        $fio = trim($fio);
                        if (empty($fio)) {
                            $prop->setValue($prop->getProperty()['DEFAULT_VALUE']);
                        } else {
                            $prop->setValue($fio);
                        }
                    }
                    break;
                default:
                    break;
            }
        }
    }


}