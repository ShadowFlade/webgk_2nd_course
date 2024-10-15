<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
//TODO make into a controller
//$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
\Bitrix\Main\Diag\Debug::writeToFile($_REQUEST, date("d.m.Y H:i:s"), "local/log.log");
$userService = new \Webgk\Service\UserService();

$isCaptchaValid = $userService->CheckCaptcha($_REQUEST);
global $APPLICATION;

if (!$isCaptchaValid) $APPLICATION->ThrowException('Капча не прошла проверку');
\Bitrix\Main\Diag\Debug::writeToFile(['request start'=> $_REQUEST], date("d.m.Y H:i:s"), "local/log.log");

$id = $userService->CreateUser($_REQUEST['REGISTER']);

global $USER;
$USER->Authorize($id);
$APPLICATION->RestartBuffer();
//LocalRedirect('/auth/', false, '302');
echo json_encode(['success' => true,'id' => $id,'redirect' => '/auth']);


//why this yields error?
//$input = \Bitrix\Main\Web\Json::decode($request->getInput());
