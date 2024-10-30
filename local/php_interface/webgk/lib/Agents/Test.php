<?php
namespace Webgk\Agents;

class Test {
    public static function JustSomeStuff()
    {
        \Bitrix\Main\Diag\Debug::writeToFile('im testing', date("d.m.Y H:i:s"), "local/log.log");
        return '\Webgk\Agents\Test::JustSomeStuff();';
    }
}