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

    public static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName)
    {
        $max_n = 0;
        $values = array();
        if (is_array($value)) {
            foreach ($value as $property_value_id => $arValue) {
                if (is_array($arValue))
                    $values[$property_value_id] = $arValue["VALUE"];
                else
                    $values[$property_value_id] = $arValue;

                if (preg_match("/^n(\\d+)$/", $property_value_id, $match)) {
                    if ($match[1] > $max_n)
                        $max_n = intval($match[1]);
                }
            }
        }

        $settings = ProductStoreBind::PrepareSettings($arProperty);
        if ($settings["size"] > 1)
            $size = ' size="' . $settings["size"] . '"';
        else
            $size = '';

        if ($settings["width"] > 0)
            $width = ' style="width:' . $settings["width"] . 'px"';
        else
            $width = '';

        if ($settings["multiple"] == "Y") {
            $bWasSelect = false;
            $options = ProductStoreBind::GetOptionsHtml($arProperty, $values, $bWasSelect);

            $html = '<input type="hidden" name="' . $strHTMLControlName["VALUE"] . '[]" value="">';
            $html .= '<select multiple name="' . $strHTMLControlName["VALUE"] . '[]"' . $size . $width . '>';
            if ($arProperty["IS_REQUIRED"] != "Y")
                $html .= '<option value=""' . (!$bWasSelect ? ' selected' : '') . '>' . Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_NO_VALUE") . '</option>';
            $html .= $options;
            $html .= '</select>';
        } else {
            if (end($values) != "" || mb_substr((string)key($values), 0, 1) != "n")
                $values["n" . ($max_n + 1)] = "";

            $name = $strHTMLControlName["VALUE"] . "VALUE";

            $html = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb' . md5($name) . '">';
            foreach ($values as $property_value_id => $value) {
                $html .= '<tr><td>';

                $bWasSelect = false;
                $options = ProductStoreBind::GetOptionsHtml($arProperty, array($value), $bWasSelect);

                $html .= '<select name="' . $strHTMLControlName["VALUE"] . '[' . $property_value_id . '][VALUE]"' . $size . $width . '>';
                $html .= '<option value=""' . (!$bWasSelect ? ' selected' : '') . '>' . Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_NO_VALUE") . '</option>';
                $html .= $options;
                $html .= '</select>';

                $html .= '</td></tr>';
            }
            $html .= '</table>';

            $html .= '<input type="button" value="' . Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_ADD") . '" onClick="BX.IBlock.Tools.addNewRow(\'tb' . md5($name) . '\', -1)">';
        }
        return $html;
    }

    public static function GetAdminFilterHTML($arProperty, $strHTMLControlName)
    {
        $lAdmin = new CAdminList($strHTMLControlName["TABLE_ID"]);
        $lAdmin->InitFilter(array($strHTMLControlName["VALUE"]));
        $filterValue = $GLOBALS[$strHTMLControlName["VALUE"]];

        if (isset($filterValue) && is_array($filterValue))
            $values = $filterValue;
        else
            $values = array();

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
        $options = ProductStoreBind::GetOptionsHtml($arProperty, $values, $bWasSelect);

        $html = '<select multiple name="' . $strHTMLControlName["VALUE"] . '[]"' . $size . $width . '>';
        $html .= '<option value=""' . (!$bWasSelect ? ' selected' : '') . '>' . Loc::getMessage("IBLOCK_PROP_ELEMENT_LIST_ANY_VALUE") . '</option>';
        $html .= $options;
        $html .= '</select>';
        return $html;
    }

    public static function GetUIFilterProperty($arProperty, $strHTMLControlName, &$fields)
    {
        $fields['type'] = 'list';
        $fields['items'] = self::getItemsForUiFilter($arProperty);
        $fields['operators'] = array(
            'default' => '=',
            'enum' => '@',
        );
    }

    private static function getItemsForUiFilter($arProperty)
    {
        $items = array();
        $settings = static::PrepareSettings($arProperty);

        foreach (ProductStoreBind::GetElements() as $arItem) {
            $items[$arItem["ID"]] = $arItem["TITLE"];
        }

        return $items;
    }

    public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        static $cache = array();

        $strResult = '';
        $arValue['VALUE'] = intval($arValue['VALUE']);
        if (0 < $arValue['VALUE']) {
            $viewMode = '';
            $resultKey = '';
            if (!empty($strHTMLControlName['MODE'])) {
                switch ($strHTMLControlName['MODE']) {
                    case 'CSV_EXPORT':
                        $viewMode = 'CSV_EXPORT';
                        $resultKey = 'ID';
                        break;
                    case 'EXTERNAL_ID':
                        $viewMode = 'EXTERNAL_ID';
                        $resultKey = '~XML_ID';
                        break;
                    case 'SIMPLE_TEXT':
                        $viewMode = 'SIMPLE_TEXT';
                        $resultKey = '~NAME';
                        break;
                    case 'ELEMENT_TEMPLATE':
                        $viewMode = 'ELEMENT_TEMPLATE';
                        $resultKey = '~NAME';
                        break;
                }
            }

            if (!isset($cache[$arValue['VALUE']])) {
                $arFilter = [];
                $intIBlockID = (int)$arProperty['LINK_IBLOCK_ID'];
                if ($intIBlockID > 0)
                    $arFilter['IBLOCK_ID'] = $intIBlockID;
                $arFilter['ID'] = $arValue['VALUE'];
                if ($viewMode === '') {
                    $arFilter['ACTIVE'] = 'Y';
                    $arFilter['ACTIVE_DATE'] = 'Y';
                    $arFilter['CHECK_PERMISSIONS'] = 'Y';
                    $arFilter['MIN_PERMISSION'] = 'R';
                }
                $rsElements = CIBlockElement::GetList(
                    array(),
                    $arFilter,
                    false,
                    false,
                    array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL")
                );
                if (isset($strHTMLControlName['DETAIL_URL'])) {
                    $rsElements->SetUrlTemplates($strHTMLControlName['DETAIL_URL']);
                }
                $cache[$arValue['VALUE']] = $rsElements->GetNext(true, true);
                unset($rsElements);
            }
            if (!empty($cache[$arValue['VALUE']]) && is_array($cache[$arValue['VALUE']])) {
                if ($viewMode !== '' && $resultKey !== '') {
                    $strResult = $cache[$arValue['VALUE']][$resultKey];
                } else {
                    $strResult = '<a href="' . $cache[$arValue['VALUE']]['DETAIL_PAGE_URL'] . '">' . $cache[$arValue['VALUE']]['NAME'] . '</a>';
                }
            }
        }
        return $strResult;
    }

    public static function GetOptionsHtml($arProperty, $values, &$bWasSelect)
    {
        $options = "";
        $settings = ProductStoreBind::PrepareSettings($arProperty);
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

    /**     * Returns data for smart filter.
     *
     * @param array $arProperty Property description.
     * @param array $value Current value.
     * @return false|array
     */
    public static function GetExtendedValue($arProperty, $value)
    {
        $html = self::GetPublicViewHTML($arProperty, $value, array('MODE' => 'SIMPLE_TEXT'));
        if ($html <> '') {
            $text = htmlspecialcharsback($html);
            return array(
                'VALUE' => $text,
                'UF_XML_ID' => $text,
            );
        }
        return false;
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
        \Bitrix\Main\Diag\Debug::writeToFile($arProperty, date("d.m.Y H:i:s"), "local/property.log");

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
            return 'whoopsie daisy';
        }
    }


    public static function GetUIEntityEditorProperty($settings, $value)
    {
        return [
            'type' => 'custom',
        ];
    }
}
