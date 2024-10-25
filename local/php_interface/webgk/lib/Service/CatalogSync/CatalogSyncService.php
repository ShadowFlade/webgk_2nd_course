<?php

namespace Webgk\Service\CatalogSync;

use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Iblock\PropertyTable;


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


        while ($prop = $propsDB->GetNext()) {
            $props[] = $prop;
        }


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

        $products = $this->getProducts(array_keys($els));
        $existingProducts = $this->getProducts($existingElementsIds);

        $this->createProps($this->GOODS_IB_ID_IN, $this->GOODS_IB_ID_OUT);
        $createdCount = 0;
        $updatedCount = 0;

        $newEls = [];
        \Bitrix\Main\Diag\Debug::writeToFile([
            'products' => $products,
            'existingProducts' => $existingProducts,
        ], date("d.m.Y H:i:s"), "local/maincatalog.log");
        $productType = \Bitrix\Catalog\ProductTable::TYPE_SKU;

        foreach ($els as $el) {
            \Bitrix\Main\Diag\Debug::writeToFile(['id ' => $el['ID'], 'code' => $el['CODE']], date("d.m.Y H:i:s"), "local/maincatalog.log");

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
                    $this->logger->logError(['not updated ib element' => $newEl->LAST_ERROR], true);
                } else {
                    $newEls[$el['ID']] = $existingEls[$el['CODE']]['ID'];
                    $emptyArray = [];
                    $this->updateProduct(
                        $existingProducts[$existingEls[$el['CODE']]['ID']],
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
                    $newEls[$el['CODE']] = $id;
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

    private function syncOffers($newEls) //main flow
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
        $createdProductsIds = [];
        $updatedProductIds = [];
        $createdPricesIds = [];
        $updatedPricesIds = [];
        $productType = \Bitrix\Catalog\ProductTable::TYPE_OFFER;

        foreach ($els as $key => $el) {

            $element = \CIBlockElement::GetByID($el['ID'])->GetNextElement();
            $fields = $element->GetFields();
            $props = $element->GetProperties();
            $newEl = new \CIBlockElement();
            $allProps = $this->formatPropsForAddingUpdating($props);

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
            if ($el['ID'] == 43) {
                \Bitrix\Main\Diag\Debug::writeToFile([
                    'offer result' => $offerResult,
                    'el ' => $el,
                    '$newFields' => $newFields,
                    '$updatedIBElementIds' => $updatedIBElementIds,
                    '$createdIBElementIds' => $createdIBElementIds,
                    '$existingProducts' => $existingProducts
                ], date("d.m.Y H:i:s"), "local/offerresult.log");
            }


            if ($offerResult['ACTION'] == 'CREATED' && empty($offerResult['ERRORS'])) {
                $createdProductsIds[] = $offerResult['ID'];
                $this->addProduct($products[$el['ID']], $offerResult['ID'], $productType, $createdProductsIds,);
                $this->addProductStore($el['ID'], $offerResult['ID'], $addedProductStoreIds,);

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
            }

//            $priceResult = $this->addUpdatePrice(
//                $offerResult['ID'],
//                $existingPrices,
//                $existingProductIdToPriceIdsMap,
//                $prices,
//                $productIdToPriceIdsMap,
//                $el['ID']
//            );

//            if ($priceResult['ACTION'] == 'CREATED' && !empty($priceResult['ID'])) {
//                $createdPricesIds[] = $priceResult['ID'];
//            } else if (!empty($priceResult['ID'])) {
//                $updatedPricesIds[] = $priceResult['ID'];
//            }
        }

        $this->logger->logOffersResults(
            [$createdIBElementIds, $updatedIBElementIds],
            [$createdProductsIds, $updatedProductIds],
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

    private function addProductStore($oldProductId, $newOfferId, &$addedProductStoreIds)
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

    private function addProduct(array $newProduct, int $newOfferId, int $type, array &$addedProductStoreIds = [])
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
            $addedProductStoreIds[] = $storeResult->getId();
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
            $updateProductIds[] = $storeResult->getId();
        }

        return ['ERRORS' => $errCollection, 'ID' => $storeResult->getId()];
    }

    private
    function getProducts($offersIds)
    {
        if (empty($offersIds)) {
            return [];
        }
        $storeFilter = [
            "ID" => $offersIds,
        ];

        $storeIterator = \Bitrix\Catalog\ProductTable::getList([
            "filter" => $storeFilter,
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

    private
    function addUpdatePrice(
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


        } else {
            $newPricePrices = $newPrices[$oldProductId];
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
}

//TODO
//1. bug in the created element it creates Костюм Футболка/шорты Smaillook &amp;amp;amp;quot;Зажигаю солнце&amp;amp;amp;quot; малодетский like this (with &amp;)
//2. make logger
