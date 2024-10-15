<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 */

$bWasSelect = false;

?><input
        type="hidden"
        name="<?= $arParams['input_name'] ?: $arParams["arUserField"]["FIELD_NAME"] ?>"
        value="<?= $arParams["arUserField"]['DEFAULT_VALUE'] ?>"
        data-value="test"

>
<script>
    window.user = {
        properties: {
            
        }
    }
    window.user.properties.enumeration = <?=\CUtil::PhpToJSObject(array_flip($arParams["arUserField"]["USER_TYPE"]["FIELDS"])) ?>;
</script>

