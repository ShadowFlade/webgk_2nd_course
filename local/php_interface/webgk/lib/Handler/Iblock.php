<?php

namespace Webgk\Handler;

class Iblock
{
    public static function onAfterIblockElementSynced(&$arFields)
    {

        if(($arFields['IBLOCK_ID'] != CATALOG_1C_IBLOCK_ID && $arFields['IBLOCK_ID'] != CATALOG_1C_TP_IBLOCK_ID)
            || empty($arFields['RESULT'])) {
            return true;
        }

        $catalogSyncService = new \Webgk\Service\CatalogSync\CatalogSyncService(
            CATALOG_1C_IBLOCK_ID,
            CATALOG_1C_TP_IBLOCK_ID,
            NEW_CATALOG_IBLOCK_ID,
            NEW_CATALOG_TP_IBLOCK_ID
        );
        $catalogSyncService->init($arFields['ID']);


    }
}