<?php

namespace Webgk\Service;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\IO;
use Bitrix\Main\Application;

class CatalogSyncService
{
    private $GOODS_IB_ID_IN;
    private $GOODS_TP_IB_ID_IN;
    private $GOODS_IB_ID_OUT;
    private $GOODS_TP_IB_ID_OUT;
    private $app;
    private $oldElsMain;

    private $existingElsMain;
    private $existingElsOffers;
    private $PROCESS_LOG = '/local/logs/catalog_syn/process.log';
    private $ERROR_LOG = '/local/logs/catalog_syn/error.log';
    private $OFFERS_LOG = '/local/logs/catalog_syn/offers.log';

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
        $newEls = $this->syncMainCatalog();//TODO mb create a temp table for this
        $this->syncOffers($newEls);
    }

    public function createProps($ibIn, $ibOut)
    {
        $propsCount = [];

        $propsDB = \CIBlockProperty::GetList([], ["IBLOCK_ID" => $ibIn]);
        $existingPropsInNewCatalogDB = PropertyTable::getList(['filter' => ['IBLOCK_ID' => $ibOut], 'select' => ['NAME', 'ID', 'CODE']]);

        while ($existingProp = $existingPropsInNewCatalogDB->fetch()) {
            $existingProps[$existingProp['CODE']] = $existingProp;
        }
        \Bitrix\Main\Diag\Debug::writeToFile($existingProps, date("d.m.Y H:i:s"), "local/log1.log");


        while ($prop = $propsDB->GetNext()) {
            $props[] = $prop;
        }
        \Bitrix\Main\Diag\Debug::writeToFile(['all props' => $props], date("d.m.Y H:i:s"), "local/log1.log");


        if (empty($props)) {
            $this->app->ThrowException('В исходном ИБ свойств не обнаружено');
        }

        $propsSkipped = [];
        foreach ($props as $prop) {

            if (isset($existingProps[$prop['CODE']])) {//if prop with such code already exists skip it - maybe change it to configurable later
                $propsSkipped[] = [$prop['CODE']];
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
                $propsCount[] = [$prop['CODE']];

            } else if ($prop['PROPERTY_TYPE'] == 'N') {//скипаем создание свойства типа integer потому что в нашем каталоге не будет такого типа и мне лень проверять работает он так или нет
                $propsSkipped[] = [$prop['CODE']];

                continue;
            } else { //creating property of type string or file or catalogue
                $newPropFields = [
                    'NAME' => $prop['NAME'],
                    'ACTIVE' => $prop['ACTIVE'],
                    'SORT' => $prop['PROPERTY_SORT'] ?? $prop['SORT'],
                    'CODE' => $prop['CODE'],
                    'IBLOCK_ID' => $ibOut,
                    'PROPERTY_TYPE' => $prop['PROPERTY_TYPE'],
                    'USER_TYPE' => $prop['USER_TYPE'],
                    'USER_TYPE_SETTINGS' => $prop['USER_TYPE_SETTINGS'],
                    'LIST_TYPE' => 'L',
                    'MULTIPLE' => $prop['MULTIPLE'],
                    'DEF' => $prop['DEFAULT_VALUE']
                ];

                if (isset($prop['LINK_IBLOCK_ID'])) {//
                    $newPropFields['LINK_IBLOCK_ID'] = $this->GOODS_TP_IB_ID_OUT;
                }


                $id = $newProp->Add($newPropFields);

                if (!$id) {
                    global $APPLICATION;
                    $APPLICATION->ThrowException($id->LAST_ERROR . '; [custom webgk message] Ошибка при создании свойства типа строка, файл или справочник');
                }
                $propsCount[] = $prop['CODE'];

            }

            $this->logProcess(
                [
                    'props created count' => $propsCount,
                    'props created' => count($propsCount),
                    'props skipped count' => count($propsSkipped),
                    'props skipped' => $propsSkipped
                ],
                true,
            );
            \Bitrix\Main\Diag\Debug::writeToFile(['props created' => $propsCount, 'props skipped' => $propsSkipped], date("d.m.Y H:i:s"), "local/log1.log");

            return $propsCount;
        }
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

    private function log($message, $filePath, $isStart)
    {
        if ($isStart) {
            $file = new IO\File(Application::getDocumentRoot() . $filePath);
            $file->delete();
        }

        \Bitrix\Main\Diag\Debug::writeToFile($message, date("d.m.Y H:i:s"), $filePath);
    }

    private function logProcess($message, $isStart = false)
    {
        $this->log($message, $this->PROCESS_LOG, $isStart);
    }

    private function logError($message, $isStart = false)
    {
        $this->log($message, $this->ERROR_LOG, $isStart);

    }

    private function logOffers($message, $isStart)
    {
        $this->log($message, $this->OFFERS_LOG, $isStart);
    }

    private function syncMainCatalog()
    {
        $elsDB = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_IN],
            'select' => ['ID', 'XML_ID', 'CODE']
        ]);
        while ($el = $elsDB->fetch()) {
            $els[$el['ID']] = $el;
        }
        \Bitrix\Main\Diag\Debug::writeToFile(['olds els in ' => $els], date("d.m.Y H:i:s"), "local/log.log");

        $existingElsDB = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_OUT],
            'select' => ['ID', 'XML_ID', 'CODE']
        ]);

        while ($existingEl = $existingElsDB->fetch()) {
            $existingEls[$existingEl['CODE']] = $existingEl;
        }
        $this->oldElsMain = $els;
        $this->existingElsMain = $existingEls;
        \Bitrix\Main\Diag\Debug::writeToFile($existingEls, date("d.m.Y H:i:s"), "local/log.log");


        $this->createProps($this->GOODS_IB_ID_IN, $this->GOODS_IB_ID_OUT);
        $createdCount = 0;
        $updatedCount = 0;

        $newEls = [];

        foreach ($els as $el) {
            $element = \CIBlockElement::GetByID($el['ID'])->GetNextElement();
            $fields = $element->GetFields();

            $props = $element->GetProperties();
            $newEl = new \CIBlockElement();
            $isThisElExists = isset($existingEls[$el['CODE']]);
            $allProps = $this->formatPropsForAddingUpdating($props);
            $newFields = [
                'IBLOCK_ID' => $this->GOODS_IB_ID_OUT,
                'NAME' => $fields['NAME'],
                'CODE' => $fields['CODE'],
                'PROPERTY_VALUES' => $allProps
            ];


            if ($isThisElExists) {

                $isUpdated = $newEl->Update(
                    $existingEls[$el['CODE']]['ID'],
                    $newFields
                );
                if (!$isUpdated) {
                    $this->logError(['not updated' => $newEl->LAST_ERROR]);
                } else {
                    $newEls[$el['ID']] = $existingEls[$el['CODE']]['ID'];
                    $updatedCount++;
                }
            } else {
                $id = $newEl->Add(
                    $newFields
                );
                if ($id === false) {
                    $this->logError(['not created ' => $newEl->LAST_ERROR]);

                } else {
                    $newEls[$el['CODE']] = $id;
                    $createdCount++;

                }
            }
        }

        $this->logProcess(['created' => $createdCount, ' updated' => $updatedCount]);
        \Bitrix\Main\Diag\Debug::writeToFile(['new els' => $newEls], date("d.m.Y H:i:s"), "local/log.log");

        return $newEls;
    }

    private function syncoffers($newEls)
    {
        $elsDB = \CIBlockElement::GetList(
            false,
            ['IBLOCK_ID' => $this->GOODS_TP_IB_ID_IN],
            false,
            false,
            ['ID', 'NAME', 'IBLOCK_ID', 'XML_ID', 'PROPERTY_CML2_LINK', 'CODE']
        );
        $offersIds = [];

        while ($el = $elsDB->fetch()) {
            $els[$el['CODE']] = $el;
            $offersIds[] = $el['ID'];
        }

        $products = $this->getProducts($offersIds);
        $prices = $this->getPrices($offersIds);

        $amountUpdaterCounter = 0;
        $amountAddedCounter = 0;


        $existingElsDB = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_TP_IB_ID_OUT],
            'select' => ['ID', 'XML_ID', 'CODE']
        ]);

        while ($existingEl = $existingElsDB->fetch()) {
            $existingEls[$existingEl['CODE']] = $existingEl;
        }

        $this->existingElsOffers = $existingEls;
        $this->createProps($this->GOODS_TP_IB_ID_IN, $this->GOODS_TP_IB_ID_OUT);
        $createdCount = 0;
        $updatedCount = 0;
        \Bitrix\Main\Diag\Debug::writeToFile(['new els main' => $this->existingElsMain], date("d.m.Y H:i:s"), "local/log.log");
        \Bitrix\Main\Diag\Debug::writeToFile(['old els main' => $this->oldElsMain], date("d.m.Y H:i:s"), "local/log.log");
        $count = 0;

        foreach ($els as $key => $el) {
            \Bitrix\Main\Diag\Debug::writeToFile(['el id id' => $el['ID']], date("d.m.Y H:i:s"), "local/log.log");

            $element = \CIBlockElement::GetByID($el['ID'])->GetNextElement();

            $fields = $element->GetFields();

            $props = $element->GetProperties();
            $newEl = new \CIBlockElement();
            $isThisElExists = isset($existingEls[$el['CODE']]);
            $allProps = $this->formatPropsForAddingUpdating($props);

            \Bitrix\Main\Diag\Debug::writeToFile(['code 11' => $el['CODE']], date("d.m.Y H:i:s"), "local/log.log");

            if (isset($allProps['CML2_LINK']) && $newEls[$el['PROPERTY_CML2_LINK_VALUE']]) {
                \Bitrix\Main\Diag\Debug::writeToFile(['cml 2 link slkdjflskdjf' => $allProps['CML2_LINK']], date("d.m.Y H:i:s"), "local/log.log");
                $allProps['CML2_LINK'] = $newEls[$el['PROPERTY_CML2_LINK_VALUE']];
            } else if (isset($allProps['CML2_LINK']) && $this->existingElsMain[$el['CODE']]) {
                \Bitrix\Main\Diag\Debug::writeToFile(['cml 2 link 123123' => $allProps['CML2_LINK']], date("d.m.Y H:i:s"), "local/log.log");
                $allProps['CML2_LINK'] = $this->existingElsMain[$el['CODE']]['ID'];
            }

            $newFields = [
                'IBLOCK_ID' => $this->GOODS_TP_IB_ID_OUT,
                'NAME' => $fields['NAME'],
                'CODE' => $fields['CODE'],
                'PROPERTY_VALUES' => $allProps
            ];


            if ($el['CODE'] == 'strap-rough-skin-kr-s') {
                \Bitrix\Main\Diag\Debug::writeToFile(
                    [
                        'el ' => $el,
                        'new fields' => $newFields,
                        ' el id ' => $existingEls[$el['CODE']]['ID'],
                        'el main ' => $this->existingElsMain[$el['CODE']],
                        'new el id' => $newEls[$el['CODE']],
                        'el cml2 link' => $newEls[$el['PROPERTY_CML2_LINK_VALUE']],
                        'el this is it' => $this->oldElsMain[$el['PROPERTY_CML2_LINK_VALUE']]['CODE']
                    ],
                    date("d.m.Y H:i:s"),
                    "local/log.log"
                );
            }
            if ($isThisElExists) {
                $isUpdated = $newEl->Update(
                    $existingEls[$el['CODE']]['ID'],
                    $newFields
                );

                if (!$isUpdated) {
                    $this->logError(['not updated' => $newEl->LAST_ERROR]);
                    if ($el['CODE'] == 'strap-rough-skin-kr-s') {
                        \Bitrix\Main\Diag\Debug::writeToFile(['could not update existed el' => $newFields, ' el id ' => $existingEls[$el['CODE']]['ID']], date("d.m.Y H:i:s"), "local/log.log");
                    }
                } else {
                    $updatedCount++;
                }
                if ($el['CODE'] == 'strap-rough-skin-kr-s') {
                    \Bitrix\Main\Diag\Debug::writeToFile(['updated existed el' => $newFields, ' el id ' => $existingEls[$el['CODE']]['ID']], date("d.m.Y H:i:s"), "local/log.log");
                }
            } else {
                $id = $newEl->Add(
                    $newFields
                );
                if ($id === false) {
                    $this->logError(['not created ' => $newEl->LAST_ERROR]);
                    if ($el['CODE'] == 'strap-rough-skin-kr-s') {
                        \Bitrix\Main\Diag\Debug::writeToFile(['could not add existed el' => $newFields, ' el id ' => $existingEls[$el['CODE']]['ID']], date("d.m.Y H:i:s"), "local/log.log");
                    }
                } else {
                    if ($el['CODE'] == 'strap-rough-skin-kr-s') {
                        \Bitrix\Main\Diag\Debug::writeToFile(['added existed el' => $newFields, ' el id ' => $existingEls[$el['CODE']]['ID']], date("d.m.Y H:i:s"), "local/log.log");
                    }
                    $createdCount++;

                }
            }
            $this->addUpdateProduct($el['ID'], $products);

            if ($price = $prices[$el["ID"]]) {
                $this->updatePrice($price["ID"], $el["NEW_PRICE"]);
            } else {
                $this->addPrice($el["ID"], $el["NEW_PRICE"]);
            }
        }


        $this->logProcess(['created' => $createdCount, ' updated' => $updatedCount]);
    }

    private function addUpdateProduct($elementId, $products, $existingProducts)
    {

        $errCollection = [];

        if ($product = $existingProducts[$elementId]) {
            $newProduct = $products[$elementId];
            $storeResult = \Bitrix\Catalog\StoreProductTable::update($product['ID'], $newProduct);

            if (!$storeResult->isSuccess()) {
                $errCollection = $storeResult->getErrors();
            }

        } else {
            $newProduct = $products[$elementId];
            $storeResult = \Bitrix\Catalog\StoreProductTable::add(
                array_merge($newProduct, ['PRODUCT_ID' => $elementId])
            );

            if (!$storeResult->isSuccess()) {
                $errCollection = $storeResult->getErrors();
            }

        }
        return ['ERRORS' => $errCollection];
    }

    private function getProducts($offersIds)
    {
        $storeFilter = [
            "PRODUCT_ID" => $offersIds,
        ];
        $storeSelect = ["ID", "PRODUCT_ID", "AMOUNT", "STORE_ID"];
        $storeIterator = \Bitrix\Catalog\StoreProductTable::getList([
            "filter" => $storeFilter,
            "select" => $storeSelect
        ]);

        $products = [];
        while ($product = $storeIterator->fetch()) {
            $products[$product['PRODUCT_ID']] = $product;
        }
        return $products;
    }

    private function updatePrice($priceId, $newPrice)
    {
        $errCollection = [];
        $priceResult = \Bitrix\Catalog\PriceTable::update($priceId, [
            'PRICE' => $newPrice,
        ]);
        if (!$priceResult->isSuccess()) {
            $errCollection = $priceResult->getErrors();
            return ['ERRORS' => $errCollection];
        }
    }

    private function addPrice($priceId, $newPrice)
    {
        $errCollection = [];
        $priceResult = \Bitrix\Catalog\PriceTable::add([
            'CATALOG_GROUP_ID' => self::PRICE_ID,
            'PRODUCT_ID' => $productId,
            'PRICE' => $price,
            'PRICE_SCALE' => $price,
            'CURRENCY' => "RUB",
        ]);

        if(!$priceResult->isSuccess()) {
            $errCollection = $priceResult->getErrors();
        }
    }

    public static function getPrices($offersIds)
    {
        $priceFilter = ["PRODUCT_ID" => $offersIds];
        $priceSelect = ["ID" ,"PRODUCT_ID"];
        $priceIterator = \Bitrix\Catalog\PriceTable::getList([
            "filter"=> $priceFilter,
            "select" => $priceSelect
        ]);
        //получаем наличие на складах товаров
        $productPrices = [];
        while ($store = $priceIterator->fetch()) {
            $productPrices[$store["PRODUCT_ID"]] = $store;
        }

        return $productPrices;
    }

}

//TODO
//1. bug in the created element it creates Костюм Футболка/шорты Smaillook &amp;amp;amp;quot;Зажигаю солнце&amp;amp;amp;quot; малодетский like this (with &amp;)