<?php

namespace Webgk\Service\Sale;

class Pay
{
    public static function initPaySystemRestrictionByGroup()
    {
        \Bitrix\Main\Diag\Debug::writeToFile('im working', date("d.m.Y H:i:s"), "local/imworking.log");
        $result = new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                '\Webgk\Restriction\Sale\Payment\paymentrestriction' =>
                    '/local/php_interface/webgk/lib/Restriction/Sale/Payment/paymentrestriction.php'
            ]
        );

        return $result;
    }
}