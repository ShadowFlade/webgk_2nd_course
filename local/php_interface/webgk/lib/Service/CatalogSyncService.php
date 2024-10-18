<?php

namespace Webgk\Service;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;

class CatalogSyncService
{
    private $GOODS_IB_ID_IN;
    private $GOODS_TP_IB_ID_IN;
    private $GOODS_IB_ID_OUT;
    private $GOODS_TP_IB_ID_OUT;
    private $app;

    private $PROP_TYPES = [
        'LIST' => ['PROPERTY_TYPE' => 'L', 'USER_TYPE' => ''],
        'CATALOGUE' => ['PROPERTY_TYPE' => 'S', 'USER_TYPE' => 'directory'],
        'STRING' => ['PROPERTY_TYPE' => 'S', 'USER_TYPE' => ''],
        'FILE' => ['PROPERTY_TYPE' => 'F', 'USER_TYPE' => ''],
    ];//those are the types we handle 

    public function __construct($GOODS_IB_ID_IN, $GOODS_TP_IB_ID_IN, $GOODS_IB_ID_OUT, $GOODS_TP_IB_ID_OUT)
    {
        $this->GOODS_IB_ID_IN = $GOODS_IB_ID_IN;
        $this->GOODS_TP_IB_ID_IN = $GOODS_TP_IB_ID_IN;
        $this->GOODS_IB_ID_OUT = $GOODS_IB_ID_OUT;
        $this->GOODS_TP_IB_ID_OUT = $GOODS_TP_IB_ID_OUT;
        \CModule::IncludeModule("iblock");
        global $APPLICATION;
        $this->app = $APPLICATION;
    }

    public function init()
    {
        $els = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_IN],
            'select' => ['ID', 'XML_ID', 'CODE']
        ])->fetchAll();
        $existingElsDB = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_OUT],
            'select' => ['ID', 'XML_ID', 'CODE']
        ]);

        while ($existingEl = $existingElsDB->fetch()) {
            $existingEls[$existingEl['CODE']] = $existingEl;
        }
        \Bitrix\Main\Diag\Debug::writeToFile($existingEls, date("d.m.Y H:i:s"), "local/log.log");


//        $this->createProps();
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($els as $el) {
            $element = \CIBlockElement::GetByID($el['ID'])->GetNextElement();
            $fields = $element->GetFields();

            $props = $element->GetProperties();
            $newEl = new \CIBlockElement();
            $isThisElExists = isset($existingEls[$el['CODE']]);
            $allProps = $this->formatPropsForAddingUpdating($props);
//            $newFields = $fields;
            $newFields = [
                'IBLOCK_ID' => $this->GOODS_IB_ID_OUT,
                'NAME' => $fields['NAME'],
                'CODE' => $fields['CODE'],
                'PROPERTY_VALUES' => $allProps
            ];
//            $newProps['IBLOCK_ID'] = $this->GOODS_IB_ID_OUT;
//            unset(
//                $newProps['~IBLOCK_ID'], $newProps['DATE_CREATE_UNIX'], $newProps['~DATE_CREATE_UNIX'],
//                $newProps['TIMESTAMP_X'], $newProps['~TIMESTAMP_X'], $newProps['TIMESTAMP_X_UNIX'], $newProps['~TIMESTAMP_X_UNIX'],
//                $newProps['IBLOCK_SECTION_ID'], //TODO for now - later change to needed group id
//                $newProps['IBLOCK_EXTERNAL_ID'],
//                $newProps['~IBLOCK_EXTERNAL_ID'],
//                $newProps['CREATED_DATE'],
//                $newProps['~CREATED_DATE'],
//            );

            if ($isThisElExists) {


                $isUpdated = $newEl->Update(
                    $existingEls[$el['CODE']]['ID'],
                    $newFields
                );
                if (!$isUpdated) {
                    \Bitrix\Main\Diag\Debug::writeToFile(['not updated' => $newEl->LAST_ERROR], date("d.m.Y H:i:s"), "local/log.log");

                } else {
                    $updatedCount++;

                }
            } else {
                $id = $newEl->Add(
                    $newFields
                );
                if ($id === false) {
                    \Bitrix\Main\Diag\Debug::writeToFile(['not created ' => $newEl->LAST_ERROR], date("d.m.Y H:i:s"), "local/log.log");

                } else {
                    $createdCount++;

                }
            }
        }
        \Bitrix\Main\Diag\Debug::writeToFile(['created' => $createdCount, ' updated' => $updatedCount], date("d.m.Y H:i:s"), "local/log.log");

    }

    public function createProps()
    {
        $propsCount = 0;

        $propsDB = \CIBlockProperty::GetList([], ["IBLOCK_ID" => $this->GOODS_IB_ID_IN]);
        $existingPropsInNewCatalogDB = PropertyTable::getList(['filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_OUT], 'select' => ['NAME', 'ID', 'CODE']]);

        while ($existingProp = $existingPropsInNewCatalogDB->fetch()) {
            $existingProps[$existingProp['CODE']] = $existingProp;
        }


        while ($prop = $propsDB->GetNext()) {
            $props[] = $prop;
        }

        if (empty($props)) {
            $this->app->ThrowException('В исходном ИБ свойств не обнаружено');
        }


        foreach ($props as $prop) {

            if (isset($existingProps[$prop['CODE']])) {//if prop with such code already exists skip it - maybe change it to configurable later
                continue;
            }

            $newProp = new \CIBlockProperty();
            unset($prop['ID']);
            unset($prop['TMP_ID']);


            if ($prop['PROPERTY_TYPE'] == 'L' && empty($prop['USER_TYPE'])) {//creating property of type list
                $listProp = $this->createListProp($prop);
                $id = $newProp->Add($listProp);


                if (!$id) {
                    global $APPLICATION;
                    $APPLICATION->ThrowException($id->LAST_ERROR, ' Ошибка при создании свойства типа список');
                }

            } else { //creating property of type string or file or catalogue
                $newPropFields = [
                    'NAME' => $prop['NAME'],
                    'ACTIVE' => $prop['ACTIVE'],
                    'SORT' => $prop['PROPERTY_SORT'] ?? $prop['SORT'],
                    'CODE' => $prop['CODE'],
                    'IBLOCK_ID' => $this->GOODS_IB_ID_OUT,
                    'PROPERTY_TYPE' => $prop['PROPERTY_TYPE'],
                    'USER_TYPE' => $prop['USER_TYPE'],
                    'USER_TYPE_SETTINGS' => $prop['USER_TYPE_SETTINGS'],
                    'LIST_TYPE' => 'L',
                    'MULTIPLE' => $prop['MULTIPLE'],
                    'DEF' => $prop['DEFAULT_VALUE']
                ];


                $id = $newProp->Add($newPropFields);

                if (!$id) {
                    global $APPLICATION;
                    $APPLICATION->ThrowException($id->LAST_ERROR . '; [custom webgk message] Ошибка при создании свойства типа строка, файл или справочник');
                }
            }
            $propsCount++;

        }
        \Bitrix\Main\Diag\Debug::writeToFile(['props created' => $propsCount], date("d.m.Y H:i:s"), "local/log.log");

        return $propsCount;
    }

    public function deleteAllProps($iblockID)
    {
        $propertyRes = \CIBlockProperty::GetList([], ["IBLOCK_ID" => $iblockID]);
        $propsDeleted = 0;
        $propsErrors = 0;

        while ($property = $propertyRes->Fetch()) {
            $propertyId = $property['ID'];

            if (\CIBlockProperty::Delete($propertyId)) {
                $propsDeleted++;
            } else {
                $propsErrors++;
            }
        }
        return [$propsDeleted, $propsErrors];
    }

    private function formatPropsForAddingUpdating($props)
    {
        $allProps = [];
        foreach ($props as $prop) {

            $propertyValues = [];
            if (is_array($prop['VALUE'])) {
                $propValNew = [];
                foreach ($prop['VALUE'] as $key => $propVal) {
                    $propValNew['VALUE'] = $propVal;
                    $propValNew['DESCRIPTION'] = $prop['DESCRIPTION'][$key];
                    $propertyValues[] = $propValNew;
                }

            } else {
                $propertyValues = $prop['VALUE'];
            }
            $propVals = $propertyValues;
            $allProps[$prop['CODE']] = $propVals;
        }
        return $allProps;
    }

    private function syncTpCatalog()
    {

    }

    private function createListProp($fields)
    {
        $newFields = [
            'NAME' => $fields['NAME'],
            'ACTIVE' => $fields['ACTIVE'],
            'SORT' => $fields['PROPERTY_SORT'] ?? $fields['SORT'],
            'CODE' => $fields['CODE'],
            'IBLOCK_ID' => $this->GOODS_IB_ID_OUT,
            'PROPERTY_TYPE' => $fields['PROPERTY_TYPE'],
            'USER_TYPE' => $fields['USER_TYPE'],
            'USER_TYPE_SETTINGS' => $fields['USER_TYPE_SETTINGS'],
            'MULTIPLE' => $fields['MULTIPLE'],
        ];
        $propVariants = \CIBlockPropertyEnum::GetList([], ["IBLOCK_ID" => $fields['IBLOCK_ID'], "CODE" => $fields['CODE']]);
        while ($variant = $propVariants->GetNext()) {
            $propVar = [];
            $propVar['VALUE'] = $variant['VALUE'];
            $propVar['DEF'] = $variant['DEF'];
            $propVar['EXTERNAL_ID'] = $variant['EXTERNAL_ID'];
            $propVar['SORT'] = $variant['PROPERTY_SORT'];
            $newFields['VALUES'][] = $propVar;
        }
        return $newFields;

    }

    private function createCatalogueProp($fields)
    {
        $newFields = $fields;
    }

}

//TODO
//1. bug in the created element it creates Костюм Футболка/шорты Smaillook &amp;amp;amp;quot;Зажигаю солнце&amp;amp;amp;quot; малодетский like this (with &amp;)