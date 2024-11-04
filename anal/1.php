<?
//ajax обработчик


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");
$arSelect = array("ID", "IBLOCK_ID", "NAME", "PREVIEW_PICTURE", "PROPERTY_MORE_PHOTO");
$arFilter = array("IBLOCK_ID" => 16, "ID" => $_POST["id"]);
$res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
$arIDs = [];
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    if ($arFields["PREVIEW_PICTURE"]) $arIDs[$arFields["PREVIEW_PICTURE"]] = 1;
    if ($arFields["PROPERTY_MORE_PHOTO_VALUE"]) $arIDs[$arFields["PROPERTY_MORE_PHOTO_VALUE"]] = 1;
}

$arImgs = [];
foreach ($arIDs as $key => $val) {
    if (isset($_POST['size'])) {
        $arImgs[] = resizeImageByWidth($key, $_POST['size']);
    } else {
        $arImgs[] = CFile::GetPath($key);
    }

}

$result = array(
    'result' => 'OK',
    'id' => $_POST["id"],
    'imgs' => $arImgs
);

echo json_encode($result);

