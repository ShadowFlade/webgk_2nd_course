<?php
namespace Webgk\Controller;


use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class User extends \Bitrix\Main\Engine\Controller
{
    public function configureActions()
    {
        return [
            'registerUser' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                ],

                '-prefilters' => [
                    ActionFilter\Csrf::class
                ],

            ],
        ];
    }

    public function registerUserAction()
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $input = \Bitrix\Main\Web\Json::decode($request->getInput());

        if (is_array($input)) {
            \Bitrix\Main\Diag\Debug::writeToFile(['input '=> $input], date("d.m.Y H:i:s"), "local/log.log");
        }
    }
}