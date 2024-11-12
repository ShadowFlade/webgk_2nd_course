<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
//TODO make into a controller
//$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$userService = new \Webgk\Service\UserService();

$isCaptchaValid = $userService->CheckCaptcha($_REQUEST);
global $APPLICATION;

if (!$isCaptchaValid) $APPLICATION->ThrowException('Капча не прошла проверку');

$res = $userService->CreateUser($_REQUEST['REGISTER']);

global $USER;
if ($res['ID']) {
    $USER->Authorize($res['ID']);
}
$APPLICATION->RestartBuffer();
//LocalRedirect('/auth/', false, '302');
$result = [
    'success' => !empty($res['ID']),
    'id' => $res['ID'],
    'redirect' => '/auth',
    'ERROR' => $res['ERROR']

];
$response = new \Bitrix\Main\Engine\Response\Json(
    $result,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
);
if (!empty($res['ERROR'])) {
    $response->setStatus(403);
}

$response->send();


//why this yields error?
//$input = \Bitrix\Main\Web\Json::decode($request->getInput());
