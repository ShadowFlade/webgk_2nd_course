<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
//TODO make into a controller
//$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
\Bitrix\Main\Diag\Debug::writeToFile($_REQUEST, date("d.m.Y H:i:s"), "local/log.log");
$userService = new \Webgk\Service\UserService();
$isSucc = $userService->OnBeforeUserRegister($_REQUEST);
\Bitrix\Main\Diag\Debug::writeToFile($isSucc, date("d.m.Y H:i:s"), "local/log.log");

//why this yields error?
//$input = \Bitrix\Main\Web\Json::decode($request->getInput());
