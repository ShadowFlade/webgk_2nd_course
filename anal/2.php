<?
//ajax обработчик


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>

<?
$filter = Array (
    "PERSONAL_PHONE" => $_REQUEST["phone"]
);
$rsUsers = CUser::GetList(($by="id"), ($order="desc"), $filter); // выбираем пользователей
if($arUser = $rsUsers->Fetch()) {
    $arResult = [
        "result" => "ok",
        "email" => $arUser["EMAIL"],
        "login" => $arUser["LOGIN"]
    ];
} else {
    $arResult = [
        "result" => "error"
    ];
};

echo json_encode($arResult);
?>