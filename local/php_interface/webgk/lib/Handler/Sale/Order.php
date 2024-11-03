<?php

namespace Webgk\Handler\Sale;

use Bitrix\Sale;

class Order
{
    public static function onSaleDeliveryServiceCalculate(\Bitrix\Main\Event $event)
    {
        $calcResult = $event->getParameter('RESULT');
        $shipment = $event->getParameter('SHIPMENT');
        $deliveryId = $shipment->getDeliveryId();
        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
        $price = $basket->getPrice();
        if ($price >= MINIMAL_PRICE_FOR_FREE_DELIVERY_BASKET
            && $deliveryId == DELIVERY_COURIER_CUSTOM_ID) {
            $calcResult->setDeliveryPrice(0);
        }
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                "RESULT" => $calcResult,
            )
        );
    }

//    public static function onOrderChange(\Bitrix\Sale\Order $order, &$arUserResult, \Bitrix\Main\HttpRequest $request, &$arParams, &$arResult)
//    {
//        \Bitrix\Main\Diag\Debug::writeToFile([$order], date("d.m.Y H:i:s"), "local/realorder.log");
//        \Bitrix\Main\Diag\Debug::writeToFile($arResult, date("d.m.Y H:i:s"), "local/arResultReal.log");
//
//
//        global $USER;
//
//        $arUser = $USER->GetByID(intval($USER->GetID()))
//            ->Fetch();
//
//        if (is_array($arUser)) {
//            $fio = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
//            $fio = trim($fio);
//            $arUser['FIO'] = $fio;
//        }
//
//        $props = $order->getPropertyCollection();
//
//        if ($arResult['PAYER_TYPE_ID'] == PAYER_TYPE_PHYS) {
//            self::handlePhysFields($props);
//        } else if ($arResult['PAYER_TYPE_ID'] == PAYER_TYPE_JUR) {
//
//        }
//        foreach ($props as $prop) {
//            /** @var \Bitrix\Sale\PropertyValue $prop */
//            $value = '';
//            \Bitrix\Main\Diag\Debug::writeToFile(['code' => $prop->getField('CODE'), 'value' => $prop->getValue()], date("d.m.Y H:i:s"), "local/prop2.log");
//
//            switch ($prop->getField('CODE')) {
//                case 'FIO':
//                    $value = $request['contact']['family'];
//                    $value .= ' ' . $request['contact']['name'];
//                    $value .= ' ' . $request['contact']['second_name'];
//
//                    $value = trim($value);
//                    if (empty($value)) {
//                        $value = $arUser['FIO'];
//                    }
//                    break;
//
//                default:
//            }
//
//            if (empty($value)) {
//                foreach ($request as $key => $val) {
//                    if (strtolower($key) == strtolower($prop->getField('CODE'))) {
//                        $value = $val;
//                    }
//                }
//            }
//
//            if (empty($value)) {
//                $value = $prop->getProperty()['DEFAULT_VALUE'];
//            }
//
//            if (!empty($value)) {
//                $prop->setValue($value);
//            }
//        }
//        \Bitrix\Main\Diag\Debug::writeToFile($arUserResult, date("d.m.Y H:i:s"), "local/aruserresult.log");
//        \Bitrix\Main\Diag\Debug::writeToFile([$request], date("d.m.Y H:i:s"), "local/request.log");
//        \Bitrix\Main\Diag\Debug::writeToFile($arParams, date("d.m.Y H:i:s"), "local/arparam.log");
//
//    }
//
//    private static function handlePhysFields($props)
//    {
//
//    }
//
//    private static function handleJurFields($props)
//    {
//
//    }
}
