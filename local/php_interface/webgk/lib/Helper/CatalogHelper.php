<?php

namespace Webgk\Helper;


class CatalogHelper
{
    public static function add2Basket($productId)
    {
        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
        \Bitrix\Main\Diag\Debug::writeToFile($basket, date("d.m.Y H:i:s"), "local/log.log");
        $fields = [
            'PRODUCT_ID' => $productId, // ID товара, обязательно
            'QUANTITY' => 1, // количество, обязательно
        ];
        $r = Bitrix\Catalog\Product\Basket::addProduct($fields);
        if (!$r->isSuccess()) {
            var_dump($r->getErrorMessages());
            return false;
        }
        $result = json_encode(
            [
                'data' => $r->getData(),
                'STATUS' => 'OK',
                'MESSAGE' => 'Товар успешно добавлен в корзину)'
            ]);
        echo $result;
        return $result;

    }
}