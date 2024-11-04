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

//1. лучше использовать d7 вместо старого ядра (тк тут нет пользовательских свойств)
//2. нет проверки $_REQUEST["phone"] на пустоту
//3. лучше еще добавить select, тк нам нужны всего 2 поля из юзера, нет смысла тащить все остальное
//4. тк нет тз, неясно, что именно нужно сделать, но таким образом мы получим одного юзера,
// а их может найтись несколько (по дефолту юзеры не проверяются на уникальность по personal_phone)
//5. вроде как еще принято после завершения die прописывать

