<?php

namespace Webgk\Service\CatalogSync;

use Bitrix\Catalog\PriceTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Application;
use Bitrix\Main\IO;
use Bitrix\Sale\StoreProductTable;

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
    private $logger;


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
        $this->logger = new Logger();
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

            $this->logger->logProcess(
                [
                    'props created' => $propsCount,
                    'props created count' => count($propsCount),
                    'props skipped' => $propsSkipped,
                    'props skipped count' => count($propsSkipped),
                ],
                true,
            );

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
                    $this->logger->logError(['not updated' => $newEl->LAST_ERROR], true);
                } else {
                    $newEls[$el['ID']] = $existingEls[$el['CODE']]['ID'];
                    $updatedCount++;
                }
            } else {
                $id = $newEl->Add(
                    $newFields
                );
                if ($id === false) {
                    $this->logger->logError(['not created ' => $newEl->LAST_ERROR]);

                } else {
                    $newEls[$el['CODE']] = $id;
                    $createdCount++;

                }
            }
        }

        $this->logger->logProcess(['created elements count' => $createdCount, ' updated elements count' => $updatedCount]);
        \Bitrix\Main\Diag\Debug::writeToFile(['new els' => $newEls], date("d.m.Y H:i:s"), "local/log.log");

        return $newEls;
    }

    private function syncOffers($newEls)
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
        [$prices, $productIdToPriceIdsMap] = $this->getPrices($offersIds);
        $existingElsDB = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_TP_IB_ID_OUT],
            'select' => ['ID', 'XML_ID', 'CODE']
        ]);
        $existingOfferIds = [];

        while ($existingEl = $existingElsDB->fetch()) {
            $existingEls[$existingEl['CODE']] = $existingEl;
            $existingOfferIds[] = $existingEl['ID'];
        }

        $existingProducts = $this->getProducts($existingOfferIds);
        [$existingPrices, $existingProductIdToPriceIdsMap] = $this->getPrices($existingOfferIds);

        $this->existingElsOffers = $existingEls;
        $this->createProps($this->GOODS_TP_IB_ID_IN, $this->GOODS_TP_IB_ID_OUT);

        $createdIBElementIds = [];
        $updatedIBElementIds = [];
        $updatedProductStoreIds = [];
        $addedProductStoreIds = [];
        $createdPricesIds = [];
        $updatedPricesIds = [];


        \Bitrix\Main\Diag\Debug::writeToFile(['new els main' => $this->existingElsMain], date("d.m.Y H:i:s"), "local/log.log");
        \Bitrix\Main\Diag\Debug::writeToFile(['old els main' => $this->oldElsMain], date("d.m.Y H:i:s"), "local/log.log");
        $count = 0;

        foreach ($els as $key => $el) {
            \Bitrix\Main\Diag\Debug::writeToFile(['el id id' => $el['ID']], date("d.m.Y H:i:s"), "local/log.log");

            $element = \CIBlockElement::GetByID($el['ID'])->GetNextElement();
            $fields = $element->GetFields();
            $props = $element->GetProperties();
            $newEl = new \CIBlockElement();
            $allProps = $this->formatPropsForAddingUpdating($props);
            \Bitrix\Main\Diag\Debug::writeToFile(['code 11' => $el['CODE']], date("d.m.Y H:i:s"), "local/log.log");

            if (isset($allProps['CML2_LINK']) && $newEls[$el['PROPERTY_CML2_LINK_VALUE']]) {
                \Bitrix\Main\Diag\Debug::writeToFile(['cml 2 link slkdjflskdjf' => $allProps['CML2_LINK']], date("d.m.Y H:i:s"), "local/log.log");
                $allProps['CML2_LINK'] = $newEls[$el['PROPERTY_CML2_LINK_VALUE']];
            } else if (isset($allProps['CML2_LINK']) && $this->existingElsMain[$el['CODE']]) {
                \Bitrix\Main\Diag\Debug::writeToFile(['cml 2 link 123123' => $allProps['CML2_LINK']], date("d.m.Y H:i:s"), "local/log.log");
                $allProps['CML2_LINK'] = $this->existingElsMain[$el['CODE']]['ID'];
            }

            if ($count == 0) {
                \Bitrix\Main\Diag\Debug::writeToFile($fields, date("d.m.Y H:i:s"), "local/fields.log");
            }

            $newFields = [
                'IBLOCK_ID' => $this->GOODS_TP_IB_ID_OUT,
                'NAME' => $fields['NAME'],
                'CODE' => $fields['CODE'],
                'PROPERTY_VALUES' => $allProps,
                'AVAILABLE' => $fields['AVAILABLE'],
            ];

            if ($el['CODE'] == 'pants-striped-flight-f-l') {
                \Bitrix\Main\Diag\Debug::writeToFile(
                    [
                        'el' => $el,
                        ' el id ' => $existingEls[$el['CODE']]['ID'],

                    ],
                    date("d.m.Y H:i:s"),
                    "local/elel.log"
                );
            }

            $offerResult = $this->addUpdateIBCatalogElementOffer(
                $el,
                $newEl,
                $existingEls,
                $newFields,
                $updatedIBElementIds,
                $createdIBElementIds
            );

            $productResult = $this->addUpdateProduct(
                $el['ID'],
                $products,
                $existingProducts,
                $updatedCountProducts,
                $createdCountProducts,
                $offerResult['ID']
            );

            if ($productResult['ACTION'] == 'CREATED' && empty($productResult['ERRORS'])) {//TODO move such shit to complex logging
                $createdProductsIds[] = $productResult['ID'];
                $this->addProductStore($el['ID'], $addedProductStoreIds);

            } else if ($productResult['ACTION'] == 'UPDATED' && empty($productResult['ERRORS'])) {
                $updatedProductsIds[] = $productResult['ID'];
                $this->updateProductStore(
                    $productResult['ID'],
                    $el['ID'],
                    $updatedProductStoreIds,
                );
            }

            $priceResult = $this->addUpdatePrice(
                $offerResult['ID'],
                $existingPrices,
                $existingProductIdToPriceIdsMap,
                $prices,
                $productIdToPriceIdsMap,
                $el['ID']
            );

            if ($priceResult['ACTION'] == 'CREATED' && !empty($priceResult['ID'])) {
                $createdPricesIds[] = $priceResult['ID'];
            } else if (!empty($priceResult['ID'])) {
                $updatedPricesIds[] = $priceResult['ID'];
            }
        }

        $this->logger->logOffersResults(
            [$createdIBElementIds, $updatedIBElementIds],
            [$createdProductsIds, $updatedProductsIds],
            [$createdPricesIds, $updatedPricesIds]
        );


    }

    private function updateProductStore($newProductId, $oldProductId, &$updatedProductStoreIds)
    {
        $oldElDB = \Bitrix\Catalog\StoreProductTable::getList([
            'filter' => ['PRODUCT_ID' => $oldProductId],
        ]);

        while ($oldEl = $oldElDB->fetch()) {
            $oldEls[$oldEl['STORE_ID']] = $oldEl;
        }

        $newEls = \Bitrix\Catalog\StoreProductTable::getList([
            'filter' => ['PRODUCT_ID' => $newProductId],
        ])->fetchAll();;

        while ($newEl = $newEls->fetch()) {
            $newEls[$newEl['STORE_ID']] = $newEl;
        }

        $errCollection = [];
        foreach ($oldEls as $storeId => $el) {
            if (!isset($newEls[$storeId])) continue;
            $updateRes = StoreProductTable::update($newEls[$storeId]['ID'], $newEls[$storeId]);
            if ($updateRes->isSuccess()) {
                $updatedProductStoreIds[] = $updateRes->getId();
            } else {
                $errMessage = "old id: {$el['ID']}; new id: {$newEls[$storeId]['ID']} "
                    . implode(',', $updateRes->getErrorMessages());
                $errCollection[$el['ID']][] = $errMessage;
            }
        }
        return ['ERRORS' => $errCollection];
    }

    private function addProductStore($oldProductId, &$addedProductStoreIds)
    {
        $oldEls = \Bitrix\Catalog\StoreProductTable::getList([
            'filter' => ['PRODUCT_ID' => $oldProductId],
        ])->fetchAll();

        foreach ($oldEls as $el) {
            $addRes = StoreProductTable::add($el);

            if ($addRes->isSuccess()) {
                $addedProductStoreIds[] = $addRes->getId();
            } else {
                $errMessage = implode(',', $addRes->getErrorMessages());
                $errCollection[$el['ID']][] = $errMessage;
            }
        }


        return ['ERRORS' => $errCollection];
    }

    private function addUpdateProduct(
        $elementId,
        $products,
        $existingProducts,
        &$updatedCount,
        &$createdCount,
        $newOfferId
    )
    {
        $errCollection = [];

        if ($product = $existingProducts[$newOfferId]) {
            $newProduct = $products[$elementId];
            unset($newProduct['ID']);
            $storeResult = \Bitrix\Catalog\ProductTable::update(
                $product['ID'],
                array_merge(
                    $newProduct,
                    ['TYPE' => \Bitrix\Catalog\ProductTable::TYPE_SKU]
                )
            );

            if (!$storeResult->isSuccess()) {
                $errCollection = $storeResult->getErrors();
            } else {
                $updated = $product['ID'];
                $action = 'UPDATED';
                $updatedCount++;
            }
        } else {
            $newProduct = $products[$elementId];
            if (!$newProduct) {
                $error = "Could not create product with element ID = $elementId";
                $this->logger->logError($error);
                return [
                    'ERRORS' => $error,
                    'ID' => false,
                    'ACTION' => false
                ];
            }
            unset($newProduct['ID']);

            $storeResult = \Bitrix\Catalog\ProductTable::add(
                array_merge(
                    $newProduct,
                    ['TYPE' => \Bitrix\Catalog\ProductTable::TYPE_SKU]
                )
            );


            if (!$storeResult->isSuccess()) {
                $errCollection = $storeResult->getErrors();
            } else {
                $action = 'CREATED';
                $createdCount++;
            }
        }

        $this->setQuantityByProduct($elementId);//by product id

        return [
            'ERRORS' => $errCollection,
            'ID' => $updated ?: $storeResult->getId(),
            'ACTION' => $action
        ];
    }

    private function getProducts($offersIds)
    {
        if (empty($offersIds)) {
            return [];
        }
        $storeFilter = [
            "ID" => $offersIds,
        ];

        $storeSelect = ["ID", "AMOUNT", "STORE_ID"];
        $storeIterator = \Bitrix\Catalog\ProductTable::getList([
            "filter" => $storeFilter,
            "select" => $storeSelect
        ]);

        $products = [];
        while ($product = $storeIterator->fetch()) {
            $products[$product['ID']] = $product;
        }

        return $products;
    }


    public function getProductsStores($offersIds)
    {
        if (empty($offersIds)) {
            return [];
        }
        $storeFilter = [
            "PRODUCT_ID" => $offersIds,
        ];

        $storeSelect = ["ID", "PRODUCT_ID", "AMOUNT", "STORE_ID"];
        $storeIterator = \Bitrix\Catalog\StoreProductTable::getList([
            "filter" => $storeFilter,
            "select" => $storeSelect
        ]);

        $products = [];
        $productIdToProductTableIds = [];
        while ($product = $storeIterator->fetch()) {
            $products[$product['ID']] = $product;
            $productIdToProductTableIds[$product['PRODUCT_ID']][] = $product['ID'];
        }

        return [$products, $productIdToProductTableIds];
    }

    private function getPrices($offersIds)
    {
        if (empty($offersIds)) {
            return [];
        }
        $priceFilter = ["PRODUCT_ID" => $offersIds];
        $priceSelect = ["ID", "PRODUCT_ID", "PRICE"];
        $priceIterator = \Bitrix\Catalog\PriceTable::getList([
            "filter" => $priceFilter,
            "select" => $priceSelect
        ]);
        //получаем наличие на складах товаров
        $productPrices = [];
        $productIdToPriceIdsMap = [];
        while ($price = $priceIterator->fetch()) {
            $productPrices[$price["PRODUCT_ID"]][] = $price;
            $productIdToPriceIdsMap[$price["PRODUCT_ID"]][] = $price['ID'];
        }

        return [$productPrices, $productIdToPriceIdsMap];
    }

    private function addUpdatePrice(
        int   $productId,
        array $existingPrices,
        array $existingProductIdToPriceIdsMap,
        array $newPrices,
        array $productIdToPriceIdsMap,
        int   $oldProductId

    ): array
    {
        if ($prices = $existingPrices[$productId]) {
            foreach ($prices as $price) {
                $newPrice = $newPrices[$productId];
                if (empty($newPrice)) {
                    $errMessage = 'Price not found: could not update existing price';
                    $this->logger->logError($errMessage);
                    $this->app->ThrowException($errMessage);// apparently this does not stop the runtime
                    return [];
                }
                unset($newPrice['ID']);
                $updateResult = PriceTable::update($price['ID'], array_merge($newPrice, ['PRODUCT_ID' => $price['PRODUCT_ID']]));
                if ($updateResult->isSuccess()) {
                    $id = $price['ID'];
                    $action = 'UPDATED';
                }
            }

            \Bitrix\Main\Diag\Debug::writeToFile(
                [
                    'updating',
                    '$prices' => $prices,
                    'new prices' => $newPrices,
                    '$existingPrices' => $existingPrices,
                    '$productId' => $productId
                ],
                date("d.m.Y H:i:s"),
                "local/prices.log");


        } else {
            $newPricePrices = $newPrices[$oldProductId];
            \Bitrix\Main\Diag\Debug::writeToFile(
                [
                    'adding',
                    '$prices' => $newPricePrices,
                    'new prices' => $newPrices,
                    '$newPricePrices' => $newPricePrices,
                    '$productId' => $productId,
                    '$oldProductId' => $oldProductId
                ],
                date("d.m.Y H:i:s"),
                "local/prices.log"
            );

            foreach ($newPricePrices as $newPrice) {

                if (empty($newPrice)) {
                    $errMessage = 'New price not found: could not add new price';
                    $this->logger->logError($errMessage);
                    $this->app->ThrowException($errMessage);
                    return [];
                }
                unset($newPrice['ID']);
                $addResult = PriceTable::add(array_merge($newPrice, ['PRODUCT_ID' => $productId]));

                if ($addResult->isSuccess()) {
                    $id = $addResult->getId();
                    $action = 'CREATED';
                } else {
                    return [];
                }
            }

        }
        return ['ID' => $id, 'ACTION' => $action];
    }

    private function addUpdateIBCatalogElementOffer($el, $newEl, $existingEls, $newFields, &$updatedCount, &$createdCount)
    {
        $isThisElExists = isset($existingEls[$el['CODE']]);
        $isSuccess = false;
        if ($isThisElExists) {

            $isUpdated = $newEl->Update(
                $existingEls[$el['CODE']]['ID'],
                $newFields
            );
            $id = $existingEls[$el['CODE']]['ID'];

            if (!$isUpdated) {
                $this->logger->logError(['not updated offer' => $newEl->LAST_ERROR]);
                if ($el['CODE'] == 'pants-striped-flight-f-l') {
                    \Bitrix\Main\Diag\Debug::writeToFile(['could not update existed el' => $newFields, ' el id ' => $existingEls[$el['CODE']]['ID']], date("d.m.Y H:i:s"), "local/elel.log");
                }
            } else {
                $isSuccess = true;
                $updatedCount[] = $existingEls[$el['CODE']]['ID'];
            }
            if ($el['CODE'] == 'pants-striped-flight-f-l') {
                \Bitrix\Main\Diag\Debug::writeToFile(['updated existed el' => $newFields, ' el id ' => $existingEls[$el['CODE']]['ID']], date("d.m.Y H:i:s"), "local/elel.log");
            }
        } else {
            $id = $newEl->Add(
                $newFields
            );

            if ($id === false) {
                $this->logger->logError(['not created  offer' => $newEl->LAST_ERROR]);
                if ($el['CODE'] == 'pants-striped-flight-f-l') {
                    \Bitrix\Main\Diag\Debug::writeToFile([
                        'could not add existed el' => $newFields,
                        ' el id ' => $existingEls[$el['CODE']]['ID']
                    ], date("d.m.Y H:i:s"), "local/elel.log");
                }
            } else {
                if ($el['CODE'] == 'pants-striped-flight-f-l') {
                    \Bitrix\Main\Diag\Debug::writeToFile(['added existed el' => $newFields, ' el id ' => $existingEls[$el['CODE']]['ID']], date("d.m.Y H:i:s"), "local/elel.log");
                }
                $isSuccess = true;
                $createdCount[] = $id;

            }
        }
        return ['IS_SUCCESS' => $isSuccess, 'ID' => $id];
    }

    /**
     * Устанавливаю поле QUANTITY равное значению сумме на всех складах.
     * Работает на событии обновлении складов
     *
     * @param $productId
     *
     */
    private function setQuantityByProduct($productId)
    {
        if (intval($productId) < 0) return;
        $iterator = (new \Bitrix\Main\ORM\Query\Query(\Bitrix\Catalog\ProductTable::getEntity()))
            ->setSelect(["ID", "QUANTITY", "AMOUNT"])
            ->where("ID", intval($productId))
            ->where("STORE.STORE.ACTIVE", "Y")
            ->registerRuntimeField(
                new \Bitrix\Main\Entity\ReferenceField(
                    'STORE',
                    '\Bitrix\Catalog\StoreProductTable',
                    ['=this.ID' => 'ref.PRODUCT_ID'],
                    ["join_type" => "left"]
                )
            )
            ->registerRuntimeField(
                new \Bitrix\Main\Entity\ExpressionField('AMOUNT', 'SUM(AMOUNT)', ['AMOUNT' => 'STORE.AMOUNT'])
            )
            ->exec()->fetch();
        if ($iterator && $iterator["QUANTITY"] != $iterator["AMOUNT"]) {
            $available = $iterator["AMOUNT"] > 0 ? \Bitrix\Catalog\ProductTable::STATUS_YES : \Bitrix\Catalog\ProductTable::STATUS_NO;
            \Bitrix\Catalog\Model\Product::update($iterator["ID"], [
                "QUANTITY" => $iterator["AMOUNT"],
                'AVAILABLE' => $available
            ]);
        }
    }
}

//TODO
//1. bug in the created element it creates Костюм Футболка/шорты Smaillook &amp;amp;amp;quot;Зажигаю солнце&amp;amp;amp;quot; малодетский like this (with &amp;)
//2. make logger
