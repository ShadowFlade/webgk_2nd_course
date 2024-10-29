<?php

namespace Webgk\Service\Search;

class Index
{
    public static function BeforeIndexHandler($arFields)
    {

        if (!$arFields["MODULE_ID"] == "iblock" && !$arFields["PARAM2"] == NEW_CATALOG_IBLOCK_ID) {
            return $arFields;
        }
        $artNumber = \CIBlockElement::GetProperty(                        // Запросим свойства индексируемого элемента
            $arFields["PARAM2"],         // BLOCK_ID индексируемого свойства
            $arFields["ITEM_ID"],          // ID индексируемого свойства
            ["sort" => "asc"],       // Сортировка (можно упустить)
            ["CODE" => "ARTNUMBER"])->Fetch();

        if (empty($artNumber)) {
            return $arFields;
        }
        $arFields["TITLE"] .= " " . $artNumber["VALUE"];
        return $arFields;
    }
}