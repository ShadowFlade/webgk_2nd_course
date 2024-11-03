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
}
