<?php

namespace Webgk\Handler;

class Catalog
{
    public static function GetOptimalPrice(
        $productId,
        $quantity,
        $arUserGroups,
        $renewal,
        $arPrices,
        $siteId,
        $arDiscountCoupons
    )
    {

        $opt1PriceCatalogGroupId = 2;
        $price = (new \Bitrix\Main\ORM\Query\Query(\Bitrix\Catalog\PriceTable::getEntity()))
            ->setSelect(['PRODUCT.IBLOCK_ID', 'ID', 'CATALOG_GROUP_ID', 'CURRENCY', 'PRICE'])
            ->where("PRODUCT_ID", $productId)
            ->where("CATALOG_GROUP_ID", $opt1PriceCatalogGroupId)
            ->registerRuntimeField(
                new \Bitrix\Main\Entity\ReferenceField(
                    'PRODUCT',
                    '\Bitrix\Iblock\ElementTable',
                    ['=this.PRODUCT_ID' => 'ref.ID'],
                    ["join_type" => "left"]
                )
            )
            ->exec()->fetch();

        return [
            'PRICE' => [
                'ID' => $price['ID'],
                'CATALOG_GROUP_ID' => $price['CATALOG_GROUP_ID'],
                'PRICE' => $price['PRICE'],
                'CURRENCY' => $price['CURRENCY'],
            ],
            'REUSLT_PRICE' => [
                'BASE_PRICE' => $price['PRICE'],
                'DISCOUNT_PRICE' => $price['PRICE'],
                'DISCOUNT' => 0,
                'PERCENT' => 0,
                'CURRENCY' => $price['CURRENCY'],
            ],
            'DISCOUNT_PRICE' => $price['PRICE'],
            'DISCOUNT' => [],
            'DISCOUNT_LIST' => []
        ];
    }
}