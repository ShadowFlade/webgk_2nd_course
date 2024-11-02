<?php

namespace Webgk\Helper;

use Bitrix\Main\Loader;
use Bitrix\Sale;

class CatalogHelper
{
    public static function add2Basket($productId, $quantity)
    {
        Loader::includeModule('sale');
        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
        $fields = [
            'PRODUCT_ID' => $productId,
            'QUANTITY' => $quantity,
        ];
        $r = \Bitrix\Catalog\Product\Basket::addProduct($fields);
        if (!$r->isSuccess()) {
            var_dump($r->getErrorMessages());
            return false;
        }
        $result = Sale\Internals\BasketTable::getList(array(
            'filter' => array(
                'FUSER_ID' => Sale\Fuser::getId(),
                'ORDER_ID' => null,
                'LID' => SITE_ID,
                'CAN_BUY' => 'Y',
            ),
            'select' => array('BASKET_COUNT', 'BASKET_SUM'),
            'runtime' => array(
                new \Bitrix\Main\Entity\ExpressionField('BASKET_COUNT', 'COUNT(*)'),
                new \Bitrix\Main\Entity\ExpressionField('BASKET_SUM', 'SUM(PRICE*QUANTITY)'),
            )
        ))->fetch();

        $data = [
            'ID' => $r->getData(),
        ];
        $basketPrice = $result['BASKET_SUM'];///еще есть BASKET_COUNT

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