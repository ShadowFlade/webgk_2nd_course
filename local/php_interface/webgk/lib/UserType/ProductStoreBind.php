<?php

namespace Webgk\UserType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;

class ProductStoreBind
{
    /** @deprecated */
    public const USER_TYPE = Iblock\PropertyTable::USER_TYPE_ELEMENT_LIST;

    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
            'USER_TYPE' => 'PRODUCT_STORE_BIND',
            'USER_TYPE_ID' => 'user_product_store_bind',
            'DESCRIPTION' => 'Привязка товара к складу',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetPropertyFieldHtmlMulty' => [__CLASS__, 'GetPropertyFieldHtmlMulty'],
            'GetPublicEditHTML' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetPublicEditHTMLMulty' => [__CLASS__, 'GetPropertyFieldHtmlMulty'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'GetUIFilterProperty' => [__CLASS__, 'GetUIFilterProperty'],
            'GetAdminFilterHTML' => [__CLASS__, 'GetAdminFilterHTML'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            'GetExtendedValue' => [__CLASS__, 'GetExtendedValue'],
            'GetUIEntityEditorProperty' => [__CLASS__, 'GetUIEntityEditorProperty'],
        ];
    }

    public static function PrepareSettings($arProperty)
    {
        $size = (int)($arProperty['USER_TYPE_SETTINGS']['size'] ?? 0);
        if ($size <= 0) {
            $size = 1;
        }

        $width = (int)($arProperty['USER_TYPE_SETTINGS']['width'] ?? 0);
        if ($width <= 0) {
            $width = 0;
        }

        $group = ($arProperty['USER_TYPE_SETTINGS']['group'] ?? 'N');
        $group = ($group === 'Y' ? 'Y' : 'N');

        $multiple = ($arProperty['USER_TYPE_SETTINGS']['multiple'] ?? 'N');
        $multiple = ($multiple === 'Y' ? 'Y' : 'N');

        return [
            'size' => $size,
            'width' => $width,
            'group' => $group,
            'multiple' => $multiple,
        ];
    }


    //PARAMETERS:
    //$arProperty - b_iblock_property.*
    //$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
    //strHTMLControlName - array("VALUE","DESCRIPTION")
    //return:
    //safe html
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $settings = ProductStoreBind::PrepareSettings($arProperty);
        if ($settings["size"] > 1)
            $size = ' size="' . $settings["size"] . '"';
        else
            $size = '';

        if ($settings["width"] > 0)
            $width = ' style="width:' . $settings["width"] . 'px"';
        else
            $width = '';

        $bWasSelect = false;
        $options = ProductStoreBind::GetOptionsHtml($arProperty, array($value["VALUE"]), $bWasSelect);

        $html = '<select name="' . $strHTMLControlName["VALUE"] . '"' . $size . $width . '>';
        $arProperty['IS_REQUIRED'] ??= 'N';
        if ($arProperty['IS_REQUIRED'] !== 'Y') {
            $html .= '<option value=""' . (!$bWasSelect ? ' selected' : '') . '>' . Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_NO_VALUE") . '</option>';
        }
        $html .= $options;
        $html .= '</select>';

        return $html;
    }

    public static function GetOptionsHtml($arProperty, $values, &$bWasSelect)
    {
        $options = "";
        $bWasSelect = false;


        foreach (ProductStoreBind::GetElements() as $arItem) {
            $options .= '<option value="' . $arItem["ID"] . '"';
            if (in_array($arItem["ID"], $values)) {
                $options .= ' selected';
                $bWasSelect = true;
            }
            $options .= '>' . $arItem["TITLE"] . ' ' . '[' . $arItem['ID'] . ']' . '</option>';
        }

        return $options;
    }


    public static function GetElements()
    {
        $stores = \Bitrix\Catalog\StoreTable::getList([
            'select' => ['TITLE', 'ID', 'DESCRIPTION']
        ])->fetchAll();
        return $stores;
    }

    public static function GetAdminListViewHTML($arProperty, $value, $_1)
    {

        static $cache = [];
        if ($value["VALUE"] <> '') {
            if (!array_key_exists($value["VALUE"], $cache)) {
                $storeRes = \Bitrix\Catalog\StoreTable::getList([
                    'filter' => ['ID' => $value["VALUE"]],
                    'select' => ["ID", "TITLE", "DESCRIPTION"]
                ])->fetch();
                if ($storeRes)
                    $cache[$value["VALUE"]] = htmlspecialcharsbx($storeRes['TITLE']);
                else
                    $cache[$value["VALUE"]] = htmlspecialcharsbx($value["VALUE"]);
            }
            return $cache[$value["VALUE"]];
        } else {
            return '&nbsp;';
        }
    }
}
