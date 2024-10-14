<?php

//автозагрузка классов, подключение констант и обработчиков
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/webgk/autoload.php';
//инициализация всех обработчиков
\Webgk\Handler\Register::initHandlers();