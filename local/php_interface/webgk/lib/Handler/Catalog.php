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

    public static function OnPrice1cSynced($id, &$arFields)
    {
        $info = (new \Bitrix\Main\ORM\Query\Query(\Bitrix\Catalog\PriceTable::getEntity()))
            ->setSelect(['PRODUCT_ID', 'IBLOCK_ID' => 'PRODUCT.IBLOCK_ID'])
            ->where("PRODUCT_ID", $arFields['PRODUCT_ID'])
            ->registerRuntimeField(
                new \Bitrix\Main\Entity\ReferenceField(
                    'PRODUCT',
                    '\Bitrix\Iblock\ElementTable',
                    ['=this.PRODUCT_ID' => 'ref.ID'],
                    ["join_type" => "left"]
                )
            )
            ->exec()->fetch();

        if (empty($info) || ($info['IBLOCK_ID'] != CATALOG_1C_IBLOCK_ID && $info['IBLOCK_ID'] != CATALOG_1C_TP_IBLOCK_ID)) {
            return true;
        }

        $catalogSyncService = new \Webgk\Service\CatalogSync\CatalogSyncService(
            CATALOG_1C_IBLOCK_ID,
            CATALOG_1C_TP_IBLOCK_ID,
            NEW_CATALOG_IBLOCK_ID,
            NEW_CATALOG_TP_IBLOCK_ID
        );
        $catalogSyncService->init($arFields['PRODUCT_ID']);
    }

    public static function onStoreProduct1cSynced($id, &$arFields)
    {
        \Bitrix\Main\Diag\Debug::writeToFile([$id,$arFields], date("d.m.Y H:i:s"), "local/storeproductupdate.log");

        $info = (new \Bitrix\Main\ORM\Query\Query(\Bitrix\Catalog\StoreProductTable::getEntity()))
            ->setSelect(['PRODUCT_ID', 'IBLOCK_ID' => 'PRODUCT.IBLOCK_ID'])
            ->where("PRODUCT_ID", $arFields['PRODUCT_ID'])
            ->registerRuntimeField(
                new \Bitrix\Main\Entity\ReferenceField(
                    'PRODUCT',
                    '\Bitrix\Iblock\ElementTable',
                    ['=this.PRODUCT_ID' => 'ref.ID'],
                    ["join_type" => "left"]
                )
            )
            ->exec()->fetch();

        if (empty($info) || ($info['IBLOCK_ID'] != CATALOG_1C_IBLOCK_ID && $info['IBLOCK_ID'] != CATALOG_1C_TP_IBLOCK_ID)) {
            return true;
        }

        $catalogSyncService = new \Webgk\Service\CatalogSync\CatalogSyncService(
            CATALOG_1C_IBLOCK_ID,
            CATALOG_1C_TP_IBLOCK_ID,
            NEW_CATALOG_IBLOCK_ID,
            NEW_CATALOG_TP_IBLOCK_ID
        );
        $catalogSyncService->init($arFields['PRODUCT_ID']);
        \Bitrix\Main\Diag\Debug::writeToFile('success', date("d.m.Y H:i:s"), "local/storeproductupdate_succ.log");

    }


}