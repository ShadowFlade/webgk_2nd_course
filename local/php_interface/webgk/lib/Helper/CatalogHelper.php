<?php

namespace Webgk\Helper;

use Bitrix\Main\Loader;
use Bitrix\Sale;

class CatalogHelper
{
    public static function add2Basket($productId)
    {
        Loader::includeModule('sale');
        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
        $fields = [
            'PRODUCT_ID' => $productId, // ID товара, обязательно
            'QUANTITY' => 1, // количество, обязательно
        ];
        $r = \Bitrix\Catalog\Product\Basket::addProduct($fields);
        if (!$r->isSuccess()) {
            var_dump($r->getErrorMessages());
            return false;
        }
        $data = [
            'ID' => $r->getData(),
        ];
        $basketPrice = $basket->getPrice();
        $diff = MINIMAL_PRICE_FOR_FREE_DELIVERY_BASKET - $basketPrice;

        if ($diff > 0) {
            $data['REMAINING_SUM'] = $diff;
            $data['WARNINGS'][] = "До бесплатной доставки осталось добавить в корзину товаров еще на сумму $diff";
        }

        $result = json_encode(
            [
                'data' => $data,
                'STATUS' => 'OK',
                'MESSAGE' => 'Товар успешно добавлен в корзину)'
            ]);

        \Bitrix\Main\Engine\Response\AjaxJson::createSuccess($result);

        return $result;

    }
}