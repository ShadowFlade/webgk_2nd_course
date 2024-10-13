<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("registration"); ?>

<? $APPLICATION->IncludeComponent(
    "bitrix:main.register",
    "",
    [
        "AUTH" => "Y",
        "REQUIRED_FIELDS" => [],
        "SET_TITLE" => "Y",
        "SHOW_FIELDS" => ["EMAIL", "NAME", "LAST_NAME", "WORK_COMPANY"],
        "SUCCESS_PAGE" => "",
        "USER_PROPERTY" => ["UF_INN", "UF_KPP", "UF_TYPE"],
        "USER_PROPERTY_NAME" => "",
        "USE_BACKURL" => "Y"
    ]
); ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>