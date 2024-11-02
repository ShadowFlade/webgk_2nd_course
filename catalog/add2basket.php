<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")) {
    if (isset($_POST['ID'])) {
        $PRODUCT_ID = intval($_POST['ID']);
        echo \Webgk\Helper\CatalogHelper::add2Basket($PRODUCT_ID);
    } else {
        echo "Нет параметров";
  }
} else {
    echo "Не подключены модули";
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>