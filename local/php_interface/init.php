<?php

use Bitrix\Main\Loader;

//автозагрузка классов, подключение констант и обработчиков
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/webgk/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/const.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/functions.php';

Loader::includeModule('iblock');
Loader::includeModule('catalog');

//инициализация всех обработчиков
\Webgk\Handler\Register::initHandlers();