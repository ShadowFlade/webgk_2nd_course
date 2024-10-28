<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__DIR__));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$catalogSyncService = new \Webgk\Service\CatalogSync\CatalogSyncService(CATALOG_1C_IBLOCK_ID, CATALOG_1C_TP_IBLOCK_ID, NEW_CATALOG_IBLOCK_ID, NEW_CATALOG_TP_IBLOCK_ID);
$catalogSyncService->init();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");//пока что докер на что-то ругается тут и не дает выполнить скрипт, извне тоже не выполняется
