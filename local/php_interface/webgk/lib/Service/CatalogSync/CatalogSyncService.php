<?php

namespace Webgk\Service\CatalogSync;

use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Iblock\PropertyTable;
use Protobuf\Exception;


class CatalogSyncService
{
    private $GOODS_IB_ID_IN;
    private $GOODS_TP_IB_ID_IN;
    private $GOODS_IB_ID_OUT;
    private $GOODS_TP_IB_ID_OUT;
    private $app;

    private $existingElsMain;
    private $existingElsOffers;
    private $logger;

    //section id in => [section out]
    private $SECTIONS_IN_IDS = [140, 141, 142];

    private $NEW_PRODUCTS_SECTION_ID_OUT = 143;


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

    public function init($isStartOver = false)
    {

        if ($isStartOver) {//creating sections structure
            $sections = Utils::createSections($this->GOODS_IB_ID_OUT);
            $newSectionIds = [];
            foreach ($sections as $code => $sectionId) {
                if ($code != 'new_products') {
                    $newSectionIds[] = $sectionId;
                } else if ($code == 'new_products') {
                    $this->NEW_PRODUCTS_SECTION_ID_OUT = $sectionId;
                }
            }
            $this->SECTIONS_IN_IDS = $newSectionIds;
        }


        $newEls = $this->syncMainCatalog();
        $this->syncOffers($newEls);
    }

    public function createProps(int $ibIn, int $ibOut, string $iblock)
    {
        $propsCount = [];
        $propsDB = \CIBlockProperty::GetList([], ["IBLOCK_ID" => $ibIn]);

        while ($prop = $propsDB->GetNext()) {
            $props[] = $prop;
        }

        $existingPropsInNewCatalogDB = PropertyTable::getList([
                'filter' => ['IBLOCK_ID' => $ibOut],
                'select' => ['NAME', 'ID', 'CODE']]
        );

        while ($existingProp = $existingPropsInNewCatalogDB->fetch()) {
            $existingProps[$existingProp['CODE']] = $existingProp;
        }


        if (empty($props)) {
            $this->app->ThrowException('В исходном ИБ свойств не обнаружено');
        }

        $propsSkipped = [];
//        if ($iblock == 'offers') {
//            \Bitrix\Main\Diag\Debug::writeToFile(
//                ['existing' => $existingProps, 'props' => $props],
//                date("d.m.Y H:i:s"),
//                "local/offersprops.log"
//            );
//
//        }

        foreach ($props as $prop) {

            if (isset($existingProps[$prop['CODE']])) {//if prop with such code already exists skip it - maybe change it to configurable later
                $propsSkipped[] = [$prop['CODE']];
                continue;
            }

            $newProp = new \CIBlockProperty();
            unset($prop['ID']);
            unset($prop['TMP_ID']);

            if ($prop['PROPERTY_TYPE'] == 'L' && empty($prop['USER_TYPE'])) {//creating property of type list
                $listProp = $this->createListProp($prop, $ibOut);

                $id = $newProp->Add($listProp);
//                if ($iblock == 'offers') {
//                    \Bitrix\Main\Diag\Debug::writeToFile(['new prop id' => $id, 'list fields' => $listProp], date("d.m.Y H:i:s"), "local/offersprops.log");
//                }


                if (!$id) {
                    global $APPLICATION;
                    $err = $id->LAST_ERROR . ' Ошибка при создании свойства типа список';
                    $this->logger->logError($err);
                    $APPLICATION->ThrowException($err);
                } else {
                    $propsCount[] = [$prop['CODE']];
                }

            } else if ($prop['PROPERTY_TYPE'] == 'N') {//скипаем создание свойства типа integer потому что в нашем каталоге не будет такого типа и мне лень проверять работает он так или нет
                $propsSkipped[] = [$prop['CODE']];
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
                    $err = $id->LAST_ERROR . '; [custom webgk message] Ошибка при создании свойства типа строка, файл или справочник';
                    $this->logger->logError($err);
                    $APPLICATION->ThrowException($err);
                }
                $propsCount[] = $prop['CODE'];

            }


        }
        $this->logger->logProcess(
            [
                "props created $iblock" => $propsCount,
                "props created count $iblock" => count($propsCount),
                "props skipped $iblock" => $propsSkipped,
                "props skipped count $iblock" => count($propsSkipped),
            ],
            true,
        );

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

    private function formatPropsForAddingUpdating($props, $ibOut, $newElID = false)
    {

        $allProps = [];
        foreach ($props as $prop) {
            $propertyValues = [];

            if ($prop['PROPERTY_TYPE'] == 'F') {
                $filesArr = [];

                foreach ($prop['VALUE'] as $key => $propVal) {
                    $fileArr = \CFile::MakeFileArray(\CFile::GetPath($propVal));
                    $fileArr["MODULE_ID"] = "iblock";
                    $filesArr[] = $fileArr;
                }

                \CIBlockElement::SetPropertyValuesEx(
                    $newElID,
                    $ibOut,
                    [$prop['CODE'] => $filesArr]
                );

                continue;

            } else if ($prop['PROPERTY_TYPE'] == 'L') {
                [$_, $enumVariants] = $this->getListPropVariants($prop['CODE'], $ibOut);
                $propValNew = $enumVariants[$prop['VALUE']]['VALUE_ID'];
                $propertyValues = $propValNew;
            } else {
                $propertyValues = $prop['VALUE'];
            }
            $propVals = $propertyValues;
            $allProps[$prop['CODE']] = $propVals;
        }
        return $allProps;

    }

    private function createListProp($fields, $ibOut)
    {
        $newFields = [
            'NAME' => $fields['NAME'],
            'ACTIVE' => $fields['ACTIVE'],
            'SORT' => $fields['PROPERTY_SORT'] ?? $fields['SORT'],
            'CODE' => $fields['CODE'],
            'IBLOCK_ID' => $ibOut,
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

    private function getListPropVariants($code, $ibOut)
    {
        $select = ["IBLOCK_ID", "ID", "CODE", "VALUE" => "ENUM.VALUE", "VALUE_ID" => "ENUM.ID"];
        $iterator = (new \Bitrix\Main\ORM\Query\Query(\Bitrix\Iblock\PropertyTable::getEntity()))
            ->setSelect($select)//we dont handle multiples here
            ->where("CODE", $code)
            ->where("IBLOCK_ID", $ibOut)
            ->registerRuntimeField(
                new \Bitrix\Main\Entity\ReferenceField(
                    'ENUM',
                    '\Bitrix\Iblock\PropertyEnumerationTable',
                    ['=this.ID' => 'ref.PROPERTY_ID'],
                    ["join_type" => "left"]
                )
            )
            ->exec();

        $values = [];
        while ($enumVariant = $iterator->fetch()) {
            $enumVariants[$enumVariant['VALUE']] = $enumVariant;
            $values[] = $enumVariant['VALUE'];
        }


        return [$values, $enumVariants];
    }


    private function syncMainCatalog()
    {
        $elsDB = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_IN],
            'select' => ['ID', 'XML_ID', 'CODE', 'IBLOCK_SECTION_ID']
        ]);
        while ($el = $elsDB->fetch()) {
            $els[$el['ID']] = $el;
        }

        $existingElsDB = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_OUT],
            'select' => ['ID', 'XML_ID', 'CODE']
        ]);

        $existingElementsIds = [];
        while ($existingEl = $existingElsDB->fetch()) {
            $existingEls[$existingEl['CODE']] = $existingEl;
            $existingElementsIds[] = $existingEl['ID'];
        }

        $this->existingElsMain = $existingEls;

        $sectionsMap = $this->getSectionsMap();


        $products = $this->getProducts(array_keys($els));
        $existingProducts = $this->getProducts($existingElementsIds);

        $this->createProps($this->GOODS_IB_ID_IN, $this->GOODS_IB_ID_OUT, "catalog");

        $createdCount = 0;
        $updatedCount = 0;

        $newEls = [];
        $productType = \Bitrix\Catalog\ProductTable::TYPE_SKU;

        foreach ($els as $el) {

            $element = \CIBlockElement::GetByID($el['ID'])->GetNextElement();
            $fields = $element->GetFields();

            $props = $element->GetProperties();
            $newEl = new \CIBlockElement();
            $isThisElExists = isset($existingEls[$el['CODE']]);
            $allProps = $this->formatPropsForAddingUpdating($props, $this->GOODS_IB_ID_OUT);
            $isActive = true;
            if (isset($sectionsMap[$el['IBLOCK_SECTION_ID']]) && in_array($sectionsMap[$el['IBLOCK_SECTION_ID']]['ID'], $this->SECTIONS_IN_IDS)) {
                $newSectionId = $sectionsMap[$el['IBLOCK_SECTION_ID']]['ID'];
            } else {
                $newSectionId = $this->NEW_PRODUCTS_SECTION_ID_OUT;
                $isActive = false;
            }


            $newFields = [
                'IBLOCK_ID' => $this->GOODS_IB_ID_OUT,
                'NAME' => $fields['NAME'],
                'CODE' => $fields['CODE'],
                'IBLOCK_SECTION_ID' => $newSectionId,
                'PROPERTY_VALUES' => $allProps,
                'ACTIVE' => $isActive ? 'Y' : 'N'
            ];

            if ($isThisElExists) {
                $isUpdated = $newEl->Update(
                    $existingEls[$el['CODE']]['ID'],
                    $newFields
                );
                if (!$isUpdated) {
                    $this->logger->logError(['not updated ib element' => $newEl->LAST_ERROR], true);
                } else {
                    $newEls[$el['ID']] = $existingEls[$el['CODE']]['ID'];
                    $emptyArray = [];
                    $this->updateProduct(
                        $products[$el['ID']],
                        $existingEls[$el['CODE']]['ID'],
                        $productType,
                        $emptyArray,
                    );
                    $updatedCount++;
                }
            } else {
                $id = $newEl->Add(
                    $newFields
                );
                if ($id === false) {
                    $this->logger->logError(['not created ' => $newEl->LAST_ERROR], true);

                } else {
                    $newEls[$el['ID']] = $id;
                    $this->addProduct(
                        $el,
                        $id,
                        $productType);
                    $createdCount++;
                }
            }
        }

        $this->logger->logProcess(['created elements count' => $createdCount, ' updated elements count' => $updatedCount]);

        return $newEls;
    }

    private
    function syncOffers($newEls) //main flow
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
        [$prices, $priceIdToProductIdsMap] = $this->getPrices($offersIds);
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
        [$existingPrices, $existingPriceIdToProductsIdsMap] = $this->getPrices($existingOfferIds);

        $this->existingElsOffers = $existingEls;
        [$_, $valueToIdMapOfListProps] = $this->createProps(
            $this->GOODS_TP_IB_ID_IN,
            $this->GOODS_TP_IB_ID_OUT, "offers"
        );

        $createdIBElementIds = [];
        $updatedIBElementIds = [];
        $updatedProductStoreIds = [];
        $addedProductStoreIds = [];
        $createdProductsIds = [];
        $updatedProductIds = [];
        $createdPricesIds = [];
        $updatedPricesIds = [];

        $productType = \Bitrix\Catalog\ProductTable::TYPE_OFFER;
        $count = 0;
        foreach ($els as $key => $el) {

            $element = \CIBlockElement::GetByID($el['ID'])->GetNextElement();
            $fields = $element->GetFields();
            $props = $element->GetProperties();
            $newEl = new \CIBlockElement();

            $allProps = $this->formatPropsForAddingUpdating($props, $this->GOODS_TP_IB_ID_OUT,
                $existingEls[$el['CODE']]['ID']//this is a hack and its not good but will do for now
            );


            if (isset($allProps['CML2_LINK']) && $newEls[$el['PROPERTY_CML2_LINK_VALUE']]) {
                $allProps['CML2_LINK'] = $newEls[$el['PROPERTY_CML2_LINK_VALUE']];

            } else if (isset($allProps['CML2_LINK']) && $this->existingElsMain[$el['CODE']]) {

                $allProps['CML2_LINK'] = $this->existingElsMain[$el['CODE']]['ID'];
            }


            $newFields = [
                'IBLOCK_ID' => $this->GOODS_TP_IB_ID_OUT,
                'NAME' => $fields['NAME'],
                'CODE' => $fields['CODE'],
                'PROPERTY_VALUES' => $allProps,
            ];


            $offerResult = $this->addUpdateIBCatalogElementOffer(
                $el,
                $newEl,
                $existingEls,
                $newFields,
                $updatedIBElementIds,
                $createdIBElementIds
            );


            if ($offerResult['ACTION'] == 'CREATED' && empty($offerResult['ERRORS'])) {
                $createdProductsIds[] = $offerResult['ID'];
                $this->addProduct(
                    $products[$el['ID']],
                    $offerResult['ID'],
                    $productType,
                    $createdProductsIds
                );
                $this->addProductStore($el['ID'], $offerResult['ID'], $addedProductStoreIds);
                $this->addPrices($prices, $el['ID'], $offerResult['ID'], $createdPricesIds);

            } else if ($offerResult['ACTION'] == 'UPDATED' && empty($offerResult['ERRORS'])) {
                $updatedProductIds[] = $offerResult['ID'];
                $this->updateProduct(
                    $existingProducts[$offerResult['ID']],
                    $offerResult['ID'],
                    $productType,
                    $updatedProductIds,

                );
                $this->updateProductStore(
                    $offerResult['ID'],
                    $el['ID'],
                    $updatedProductStoreIds,
                );
                $this->updatePrices(
                    $prices,
                    $existingPrices,
                    $el['ID'],
                    $offerResult['ID'],
                    $updatedPricesIds
                );
            }

            //TODO mb refactor prices - we have to check them for existence separately from ib elements - if we create them and then delete one price - everything crumbles

            $count++;
        }


        $this->logger->logOffersResults(
            [$createdIBElementIds, $updatedIBElementIds],
            [$createdProductsIds, $updatedProductIds],
            [$createdPricesIds, $updatedPricesIds]
        );


    }

    private
    function updateProductStore($newProductId, $oldProductId, &$updatedProductStoreIds)
    {
        $oldElDB = \Bitrix\Catalog\StoreProductTable::getList([
            'filter' => ['PRODUCT_ID' => $oldProductId],
        ]);

        while ($oldEl = $oldElDB->fetch()) {
            $oldEls[$oldEl['STORE_ID']] = $oldEl;
        }

        $newElsDB = \Bitrix\Catalog\StoreProductTable::getList([
            'filter' => ['PRODUCT_ID' => $newProductId],
        ]);;

        while ($newEl = $newElsDB->fetch()) {
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

    private
    function addProductStore($oldProductId, $newOfferId, &$addedProductStoreIds)
    {
        $errCollection = [];
        $oldEls = \Bitrix\Catalog\StoreProductTable::getList([
            'filter' => ['PRODUCT_ID' => $oldProductId],
        ])->fetchAll();

        foreach ($oldEls as $el) {
            unset($el['ID']);
            $addRes = \Bitrix\Catalog\StoreProductTable::add(array_merge($el, ['PRODUCT_ID' => $newOfferId]));

            if ($addRes->isSuccess()) {
                $addedProductStoreIds[] = $addRes->getId();
            } else {
                $errMessage = implode(',', $addRes->getErrorMessages());
                $errCollection[$el['ID']][] = $errMessage;
            }
        }
        !empty($errCollection) ? $this->logger->logError($errCollection) : false;
        return ['ERRORS' => $errCollection];
    }

    private
    function addProduct(array $newProduct, int $newOfferId, int $type, array &$addedProductStoreIds = [])
    {

        $storeResult = \Bitrix\Catalog\ProductTable::add(
            array_merge(
                $newProduct,
                ['TYPE' => $type],
                ['ID' => $newOfferId]
            )
        );


        if (!$storeResult->isSuccess()) {
            $errCollection = $storeResult->getErrors();
        } else {
            $action = 'CREATED';
            $id = $storeResult->getId();
            $addedProductStoreIds[] = $id;
            $this->setQuantityByProduct($id);
        }

        return ['ERRORS' => $errCollection, 'ACTION' => $action, 'ID' => $storeResult->getId()];
    }

    private
    function updateProduct(array $newProduct, int $newOfferId, int $type, &$updateProductIds,)
    {
        if (empty($newProduct)) {
            $err = "No new product to update with id: {$newProduct['ID']} : {$newOfferId}";
            $this->logger->logError($err);
            return $err;
        }
        unset($newProduct['ID']);
        $storeResult = \Bitrix\Catalog\ProductTable::update(
            $newOfferId,
            array_merge(
                $newProduct,
                ['TYPE' => $type]
            )
        );

        if (!$storeResult->isSuccess()) {
            $errCollection = $storeResult->getErrors();
        } else {
            $id = $storeResult->getId();
            $updateProductIds[] = $id;
            $this->setQuantityByProduct($id);
        }

        return ['ERRORS' => $errCollection, 'ID' => $storeResult->getId()];
    }

    private
    function getProducts($offersIds)
    {
        if (empty($offersIds)) {
            return [];
        }

        $storeIterator = \Bitrix\Catalog\ProductTable::getList([
            "filter" => ['ID' => $offersIds],
        ]);
        $products = [];

        while ($product = $storeIterator->fetch()) {
            $products[$product['ID']] = $product;
        }

        return $products;
    }


    public
    function getProductsStores($offersIds)
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

    private
    function getPrices($offersIds)
    {
        if (empty($offersIds)) {
            return [];
        }
        $priceFilter = ["PRODUCT_ID" => $offersIds];
        $priceIterator = \Bitrix\Catalog\PriceTable::getList([
            "filter" => $priceFilter,
        ]);
        //получаем наличие на складах товаров
        $productPrices = [];
        $priceIdToProductIdMap = [];

        while ($price = $priceIterator->fetch()) {
            $productPrices[$price["PRODUCT_ID"]][$price['CATALOG_GROUP_ID']] = $price;
            $priceIdToProductIdMap[$price["ID"]] = $price['PRODUCT_ID'];
        }

        return [$productPrices, $priceIdToProductIdMap];
    }

    private function addPrices(array $prices, int $currProductId, int $productId, array &$addedPricesIds)
    {
        \Bitrix\Main\Diag\Debug::writeToFile($prices, date("d.m.Y H:i:s"), "local/log.log");

        foreach ($prices[$currProductId] as $newPrice) {
            \Bitrix\Main\Diag\Debug::writeToFile($newPrice, date("d.m.Y H:i:s"), "local/oneprice.log");

            if (empty($newPrice)) {
                $errMessage = 'New price not found: could not add new price';
                $this->logger->logError($errMessage);
                $this->app->ThrowException($errMessage);
                return [];
            }
            unset($newPrice['ID']);
            unset($newPrice['TIMESTAMP_X']);
            $newPrice['PRODUCT_ID'] = $productId;
            $addResult = PriceTable::add($newPrice);

            if ($addResult->isSuccess()) {
                $id = $addResult->getId();
                if (!empty($id)) {
                    $addedPricesIds[] = $id;
                } else {
                    $addedPricesIds[] = 'haha';
                }

            } else {
                $err = $addResult->getErrorMessages();
                $this->logger->logError(['create price' => $err]);
                return ['ERRORS' => $err];
            }
        }

    }

    private
    function updatePrices(
        $newPrices,
        $existingPrices,
        $currProductId,
        $newProductId,
        &$updatedPricesIds
    )
    {

        foreach ($newPrices[$currProductId] as $catalogGroupId => $newPrice) {
            try {
                $updateRes = \Bitrix\Catalog\PriceTable::update(
                    $existingPrices[$newProductId][$catalogGroupId]['ID'],
                    array_merge($newPrice, ['PRODUCT_ID' => $newProductId])
                );
            } catch (\Exception $e) {
                throw new Exception("Could not update product with id: {$newProductId} and catalog group id {$catalogGroupId}");
            }

            if ($updateRes->isSuccess()) {
                $updatedPricesIds[] = $updateRes->getId();
            } else {
                $this->logger->logError(['update' => $updateRes->getErrorMessages()]);
            }
        }


    }

    private function addUpdateIBCatalogElementOffer
    (
        $el,
        $newEl,
        $existingEls,
        $newFields,
        &$updatedCount,
        &$createdCount
    )
    {
        $isThisElExists = isset($existingEls[$el['CODE']]);
        $isSuccess = false;
        $errCollection = [];
        $action = '';
        if ($isThisElExists) {

            $isUpdated = $newEl->Update(
                $existingEls[$el['CODE']]['ID'],
                $newFields
            );
            $id = $existingEls[$el['CODE']]['ID'];

            if (!$isUpdated) {
                $err = $newEl->LAST_ERROR;
                $errCollection[] = $err;
                $this->logger->logError(['not updated offer' => $err]);

            } else {
                $isSuccess = true;
                $updatedCount[] = $existingEls[$el['CODE']]['ID'];
                $action = 'UPDATED';
            }

        } else {
            $id = $newEl->Add(
                $newFields
            );

            if ($id === false) {
                $err = $newEl->LAST_ERROR;
                $errCollection[] = $err;
                $this->logger->logError(['not created  offer' => $err]);
            } else {
                $isSuccess = true;
                $createdCount[] = $id;
                $action = 'CREATED';

            }
        }
        return ['IS_SUCCESS' => $isSuccess, 'ID' => $id, 'ACTION' => $action, 'ERRORS' => $errCollection];
    }

    /**
     * Устанавливаю поле QUANTITY равное значению сумме на всех складах.
     * Работает на событии обновлении складов
     *
     * @param $productId
     *
     */
    private
    function setQuantityByProduct($productId)
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

    //тут мы по хэшмапу определяем какой раздел в какой раздел должен переходить, если раздел не найден,
    // то берется дефолтное значение и все пихается в new_products с деактивацией - слева значение из каталога 1с
    // - справа из нашего новосозданного каталога
    private function getSectionsMap()
    {
        $sectionInOutMap = [
            'accessories' => 'accessories',
            'sportswear' => 'sportswear',
            't-shirts' => 'odezhda',
            'underwear' => 'odezhda',
            'pants' => 'odezhda',
            'dresses' => 'odezhda',
            'shoes' => 'odezhda',
            'default' => 'new-products'
        ];
        $sectionsInDB = \Bitrix\Iblock\SectionTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_IN],
            'select' => ['ID', 'CODE', 'IBLOCK_SECTION_ID'],
            'order' => ['DEPTH_LEVEL' => 'ASC'],
        ]);
        $sectionsIn = [];
        $sectionsOutDB = \Bitrix\Iblock\SectionTable::getList([
            'filter' => ['IBLOCK_ID' => $this->GOODS_IB_ID_OUT],
            'select' => ['ID', 'CODE']
        ]);
        $sectionsOut = [];
        while ($sectionOut = $sectionsOutDB->fetch()) {
            $sectionsOut[$sectionOut['CODE']] = $sectionOut;
        }

        while ($sectionIn = $sectionsInDB->fetch()) {
            if (isset($sectionsIn[$sectionIn['IBLOCK_SECTION_ID']])) {
                $sectionsIn[$sectionIn['ID']] = $sectionsIn[$sectionIn['IBLOCK_SECTION_ID']];//setting subsections
                $sectionsIn[$sectionIn['ID']]['ID'] = $sectionsOut[$sectionsIn[$sectionIn['ID']]['CODE']]['ID'];
            } else {
                $sectionsIn[$sectionIn['ID']] = $sectionIn;//setting top level sections

                if (isset($sectionInOutMap[$sectionIn['CODE']])) {
                    $sectionsIn[$sectionIn['ID']]['ID'] = $sectionsOut[$sectionInOutMap[$sectionIn['CODE']]]['ID'];
                } else if (!isset($sectionInOutMap[$sectionIn['CODE']])) {
                    $sectionsIn[$sectionIn['ID']]['ID'] = $sectionsOut[$sectionInOutMap['default']]['ID'];
                }
            }
        }

        return $sectionsIn;
    }
}

//TODO
//1. bug in the created element it creates Костюм Футболка/шорты Smaillook &amp;amp;amp;quot;Зажигаю солнце&amp;amp;amp;quot; малодетский like this (with &amp;)
