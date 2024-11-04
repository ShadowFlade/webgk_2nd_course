<?php

//подключаемый файл в init.php

function sw_get_value_by_id($table, $id = false, $return_array = false)
{
    global $DB;
    $q = "SELECT distinct `UF_NAME`, `UF_XML_ID`, `UF_DESCRIPTION` FROM `" . $table . "` ";
    if ($id != false) {
        if (is_array($id)) {
            foreach ($id as $key => $line) {
                $dev = ' AND';
                if ($key == 0)
                    $dev = ' WHERE';
                $q .= $dev .= " `UF_XML_ID` = '" . $line . "'";
            }
        } else {
            $q .= "WHERE `UF_XML_ID` = '" . $id . "'";
        }
    }
    $q .= ' ;';

    $results = $DB->Query($q);
    if (!is_array($id) && !$return_array) {
        $row = $results->Fetch();
        return $row['UF_NAME'];
    } elseif ($return_array && !is_array($id)) {
        //$row = $results->Fetch();
        return $results;
    }
    return $results;
}

function sw_get_ptoperty_val($prop)
{
    $val = $prop['VALUE'];
    if ($prop['USER_TYPE_SETTINGS']['TABLE_NAME']) {
        $val = sw_get_value_by_id($prop['USER_TYPE_SETTINGS']['TABLE_NAME'], $prop['VALUE']);
    }
    return $val;
}

function sw_get_option($code, $id = 5)
{
    CModule::IncludeModule("iblock");
    $arSelect = array("IBLOCK_ID", "ID", "PROPERTY_" . $code);
    $arFilter = array("IBLOCK_ID" => $id);
    $res = CIBlockElement::GetList(
        array("SORT" => "ASC"),
        $arFilter,
        false,
        array("nPageSize" => 1),
        $arSelect
    );
    while ($el = $res->GetNextElement()) {
        $arFields = $el->GetFields();
        return $arFields['PROPERTY_' . $code . '_VALUE'];
    }
    return '';
}

function sw_get_option_html($code, $id = 5)
{
    CModule::IncludeModule("iblock");
    $arSelect = array("IBLOCK_ID", "ID", "PROPERTY_" . $code);
    $arFilter = array("IBLOCK_ID" => $id);
    $res = CIBlockElement::GetList(
        array("SORT" => "ASC"),
        $arFilter,
        false,
        array("nPageSize" => 1),
        $arSelect
    );
    while ($el = $res->GetNextElement()) {
        $arFields = $el->GetFields();
        return $arFields['~PROPERTY_' . $code . '_VALUE']['TEXT'];
    }
    return '';
}

function sw_get_option_img($code, $id = 1)
{
    CModule::IncludeModule("iblock");
    $arSelect = array("IBLOCK_ID", "ID"); //$code
    $arFilter = array("IBLOCK_ID" => $id); //$id
    $res = CIBlockElement::GetList(
        array("SORT" => "ASC"),
        $arFilter,
        false,
        array("nPageSize" => 1),
        $arSelect
    );
    while ($el = $res->GetNextElement()) {
        $arFields = $el->GetFields();
        $arFieldsProp = $el->GetProperties();
        return $arFieldsProp[$code]['VALUE'];
    }
    return '';
}

function sw_get_show_price($product_id, $price = 0, $currency = 'RUB')
{
    CModule::IncludeModule("catalog");
    global $USER;
    $arDiscounts = CCatalogDiscount::GetDiscountByProduct(
        $product_id,
        $USER->GetUserGroupArray(),
        "N",
        array(),
        SITE_ID,
        array()
    );

    $discont_price = CCatalogProduct::CountPriceWithDiscount(
        $price,
        $currency,
        $arDiscounts
    );

    $show_price = $price;
    if ($price != $discont_price)
        $show_price = $discont_price;

    $offers = array();
    $arSelect = array("ID", "NAME", "CATALOG_GROUP_1", "CATALOG_PRICE_1");
    $arFilter = array("IBLOCK_ID" => CATALOG_OFFERS, "ACTIVE" => "Y", 'PROPERTY_CML2_LINK' => $product_id);
    $reviews_res = CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 1000), $arSelect);
    //$total = $res -> SelectedRowsCount();
    $min_price = 0;
    while ($offer = $reviews_res->GetNextElement()) {
        $offer_fields = $offer->getFields();

        if ($offer_fields['CATALOG_PRICE_1'] < $min_price || $min_price == 0) {
            $price = $offer_fields['CATALOG_PRICE_1'];
            $min_price = $offer_fields['CATALOG_PRICE_1'];
            $currency = $offer_fields['CATALOG_CURRENCY_1'];

            $show_price = CCatalogProduct::CountPriceWithDiscount(
                $price,
                $currency,
                $arDiscounts
            );
        }
    }

    $ret_arr = array(round($show_price, 0), round($price, 0), $currency);

    return $ret_arr;
}

function sw_product($arItem, $class = "")
{ ?>
    <?
    $product_id = $arItem['ID'];
    if (!empty($arItem['ITEM_ID']))
        $product_id = $arItem['ITEM_ID'];
    if (!empty($arItem['PRODUCT_ID']))
        $product_id = $arItem['PRODUCT_ID'];

    $first_input_id = $product_id;
    $parent_id = '';

    $arFilter = array("IBLOCK_ID" => 4);
    $res = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter, false, false, array(
        "ID",
        "NAME",
        "SHOW_COUNTER"
    ));

    while ($ar_fields = $res->GetNext()) {
        //echo "У элемента ".$ar_fields["NAME"]." ".$ar_fields["SHOW_COUNTER"]." показов<br>";
    }

    $active = '';
    if (sw_check_in_fav($product_id))
        $active = ' active';
    if (cur_page == '/favorites/')
        $active .= ' remove';
    ?>

    <?php
    $img = $arItem['PREVIEW_PICTURE'];
    if (is_array($img))
        $img = $arItem['PREVIEW_PICTURE']['ID'];
    ?><?
    if (cur_page == '/favorites/') :?>
        <div class="catalog-item__delete add_to_fav<?= $active ?>"
             data-id="<?= $first_input_id ?>" <?= $parent_id ?>></div>
    <? else : ?>
        <div class="catalog-item__like ico-like add_to_fav<?= $active ?>"
             data-id="<?= $first_input_id ?>" <?= $parent_id ?>></div>
    <? endif; ?>
    <div class="catalog-item__img">
        <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
            <?
            //$renderImage = CFile::ResizeImageGet($arItem["PREVIEW_PICTURE"], Array("width" => 221, "height" => 191), BX_RESIZE_IMAGE_EXACT, false);
            $file = CFile::ResizeImageGet($img, array(
                'width' => 221,
                'height' => 191
            ), BX_RESIZE_IMAGE_PROPORTIONAL, true);
            $src = $file["src"];
            ?>
            <img src="<?= $src ?>" alt="<?= $arItem["NAME"] ?>">
        </a>
    </div>
    <? if (cur_page == '/catalog-lenses/') : ?>
    <div class="catalog-item__name lenses-setting">
        <a
            href="<?= $arItem["DETAIL_PAGE_URL"] ?>"><?= $arItem["NAME"] ?></a>
    </div>
    <div class="catalog-item__caption"><?= sw_get_ptoperty_val($arItem["PROPERTIES"]["LENSTYPE"]) ?></div>
<? else : ?>
    <div class="catalog-item__name">
        <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>"><?= $arItem["NAME"] ?></a>
    </div>
<? endif; ?>

    <div class="catalog-item__price">

        <?php
        //if(!empty($arItem['CATALOG_PRICE_1']))
        //$price = sw_get_show_price($product_id , $arItem['CATALOG_PRICE_1']);
        //elseif(!empty($arItem['PRICE']))
        //$price = sw_get_show_price($product_id , $arItem['PRICE']);
        //var_dump($arItem['CATALOG_PRICE_1']);

        $price = CCatalogProduct::GetOptimalPrice($arItem["ID"], 1, 'N');
        ?>
        <?php if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
            <span class="old"><?= $price['RESULT_PRICE']['BASE_PRICE'] ?><i class="rouble">a</i></span>
        <?php } ?>
        <span class="new"><?= $price['RESULT_PRICE']["DISCOUNT_PRICE"] ?><i class="rouble">a</i></span>
    </div>
<? }

function sw_product_hbmdev($arItem, $class = "")
{
    $product_id = $arItem['ID'];
    if (!empty($arItem['ITEM_ID']))
        $product_id = $arItem['ITEM_ID'];
    if (!empty($arItem['PRODUCT_ID']))
        $product_id = $arItem['PRODUCT_ID'];

    $first_input_id = $product_id;
    $parent_id = '';

    $img = $arItem['PREVIEW_PICTURE'];
    if (is_array($img))
        $img = $arItem['PREVIEW_PICTURE']['ID'];

    $file = CFile::ResizeImageGet($img, array('width' => 187, 'height' => 187), BX_RESIZE_IMAGE_EXACT, true);
    if (!empty($file)) {
        $src = $file["src"];
    } else {
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }
    ?>

    <div class="product-card">
        <!--            <div class="product-card__label">sale</div>-->
        <div class="product-card__img">
            <img src="<?= $src ?>" alt="<?= $arItem["NAME"] ?>">
        </div>
        <div class="product-card__main">
            <div class="product-card__title">
                <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>"><?= $arItem["NAME"] ?></a>
            </div>
            <?
            $arSKU = CCatalogSKU::getOffersList(
                $arItem["ID"],
                0,
                array('ACTIVE' => 'Y'),
                array(
                    'ID',
                    'NAME',
                    'CODE',
                    'PROPERTY_P0',
                    'PROPERTY_P1',
                    'PROPERTY_P2',
                    'PROPERTY_P3',
                    'PROPERTY_P4',
                    'PROPERTY_P5',
                    'PROPERTY_P6'
                ),
                array()
            );
            foreach ($arSKU as $offer) {
                //var_dump($offer);
                foreach ($offer as $test) {
                    $diametr[] = $test["PROPERTY_P0_VALUE"];
                    $dlina[] = $test["PROPERTY_P1_VALUE"];
                    $tuppokrutty[] = $test["PROPERTY_P2_VALUE"];
                    $klasproch[] = $test["PROPERTY_P3_VALUE"];
                    $typemet[] = $test["PROPERTY_P4_VALUE"];
                    $rezba[] = $test["PROPERTY_P5_VALUE"];
                    $naz[] = $test["PROPERTY_P6_VALUE"];
                }
            }

            $diametr = array_filter(array_unique($diametr));
            $dlina = array_filter(array_unique($dlina));
            $tuppokrutty = array_filter(array_unique($tuppokrutty));
            $klasproch = array_filter(array_unique($klasproch));
            $typemet = array_filter(array_unique($typemet));
            $rezba = array_filter(array_unique($rezba));
            $naz = array_filter(array_unique($naz));
            ?>

            <dl class="product-card__info">
                <? if (!empty($tuppokrutty)) { ?>
                    <dt>
                        <span>Покрытие:</span>
                    </dt>
                    <dd>
                        <? foreach ($tuppokrutty as $key => $rDN2) { ?><?= $rDN2 ?><? if (count($tuppokrutty) != $key + 1) { ?>,<?
                        } ?><?
                        } ?>
                    </dd>
                    <?
                } ?>

                <? if (!empty($naz)) { ?>
                    <dt>
                        <span>Назначение:</span>
                    </dt>
                    <dd>
                        <?
                        $key2 = 0;
                        foreach ($naz as $rDN) {
                            $key2++; ?>
                            <?= $rDN ?><? if (count($naz) != $key2) { ?>,<?
                            } ?><?
                        } ?>
                    </dd>
                    <?
                } ?>

                <? if (!empty($diametr)) { ?>
                    <dt>
                        <span>Диаметр:</span>
                    </dt>
                    <dd>
                        <? foreach ($diametr as $key => $rD) { ?><?= $rD ?><? if (count($diametr) != $key + 1) { ?>,<?
                        } ?><?
                        } ?>
                    </dd>
                    <?
                } ?>

                <? if (!empty($dlina)) { ?>
                    <dt>
                        <span>Длина</span>
                    </dt>
                    <dd>
                        <? foreach ($dlina as $key => $rDl) { ?><?= $rDl ?><? if (count($dlina) != $key + 1) { ?>,<?
                        } ?><?
                        } ?>
                    </dd>
                    <?
                } ?>
        </div>
        <div class="product-card__actions">
            <div class="product-card__avaible">
                <svg class="icon-checked">
                    <use xlink:href="#checked"></use>
                </svg>
                <span>В наличии</span>
                <span>Все размеры</span>
            </div>
            <div class="product-card__actions-btns">
                <form action="<?= SITE_TEMPLATE_PATH ?>/ajax/basket/put_offers_in_basket.php" method="POST"
                      class="add_to_cart_form" style="display: block;">
                    <input type="hidden" name="product_id" value="<?= $arItem['ID'] ?>">
                    <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                           data-id="<?= $arItem['ID'] ?>">
                    <a href="#" class="btn add_to_cart" data-name="<?= $arItem['NAME'] ?>" data-image="<?= $src ?>">Купить</a>
                </form>
            </div>

            <?
            $iblockid = $arItem['IBLOCK_ID'];
            $id = $arItem['ID'];
            if (isset($_SESSION["CATALOG_COMPARE_LIST"][$iblockid]["ITEMS"][$id])) {
                $checked = 'compare_yes';
            } else {
                $checked = '';
            }
            ?>
            <a onclick="compare_tov(<?= $arItem['ID']; ?>);"
               class="compareid_<?= $arItem['ID']; ?> product-card__actions-link compare_class <?= $checked; ?>"
               data-added="Добавлен в сравнение">
                <svg class="icon-rating">
                    <use xlink:href="#rating"></use>
                </svg>
            </a>

            <?php
            $active1 = '';
            $act = 'add';
            $text = 'В избранное';
            global $APPLICATION;
            $arElements = unserialize($APPLICATION->get_cookie('bo_favorites'));
            if ($arElements[$arItem['ID']]) {
                $active1 = ' active';
                $act = 'del';
                $text = 'В избранном';
            }
            ?>
            <a class="cursor_pointer product-card__actions-link add_to_fav_not_main <?= $active1 ?>"
               data-id="<?= $arItem['ID'] ?>" data-act="<?= $act ?>" data-added="Добавлен в избраное">
                <svg class="icon-heart">
                    <use xlink:href="#heart"></use>
                </svg>
            </a>

            <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/basket/put_offers_in_basket.php" method="POST"
                  class="add_to_cart_form">
                <input type="hidden" name="product_id" value="<?= $arItem['ID'] ?>">
                <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                       data-id="<?= $arItem['ID'] ?>">
                <a href="#" class="product-card__actions-link product-card__actions-link--basket add_to_cart"
                   data-name="<?= $arItem['NAME'] ?>" data-image="<?= $src ?>">
                    <svg class="icon-basket">
                        <use xlink:href="#basket"></use>
                    </svg>
                </a>
            </form>
        </div>
    </div>
<? }

function sw_product_hbmdev_card_1($arItem, $class = "")
{
    $img2 = $arItem['PREVIEW_PICTURE'];
    if (is_array($img2))
        $img2 = $arItem['PREVIEW_PICTURE']['ID'];

    $file = CFile::ResizeImageGet($img2, array('width' => 373, 'height' => 250), BX_RESIZE_IMAGE_PROPORTIONAL, true);
    if (!empty($file)) {
        $src = $file["src"];
    } else {
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }
    ?>
    <div class="card_1 card">
        <? if ($arItem["PROPERTY_NEWPRODUCT_VALUE"] == 'Да') { ?>
            <div class="label label__new">new</div>
        <? } elseif ($arItem["PROPERTY_SPECIALOFFER_VALUE"] == 'да') { ?>
            <div class="label label__sale">sale</div>
            <?
        } ?>
        <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>" class="card__link">
            <div class="card__img">
                <img src="<?= $src ?>" alt="<?= $arItem['NAME'] ?>">
            </div>
            <div class="card__title"><?= $arItem['NAME'] ?></div>
        </a>
        <div class="card__buy-line">
            <div class="card__price">
                <? $price = CCatalogProduct::GetOptimalPrice($arItem["ID"], 1, 'N'); ?>
                <? if (!empty($arItem["PROPERTY_OLD_PRICE_VALUE"])) { ?>
                    <div class="card__price_new"><?= $price['RESULT_PRICE']["DISCOUNT_PRICE"] ?>
                        <span
                            class="card__price_mute"> руб.</span>
                    </div>
                    <div class="card__price_old"><?= $arItem["PROPERTY_OLD_PRICE_VALUE"] ?> Р</div>
                <? } else { ?>
                    <div class="price__current"><?= $price['RESULT_PRICE']["DISCOUNT_PRICE"] ?> Р</div>
                    <?
                } ?>
            </div>
            <form action="<?= SITE_TEMPLATE_PATH ?>/ajax/basket/put_offers_in_basket.php" method="POST"
                  class="add_to_cart_form">
                <input type="hidden" name="product_id" value="<?= $arItem['ID'] ?>">
                <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                       data-id="<?= $arItem['ID'] ?>">
                <a href="#" class="card__button button add_to_cart" data-name="<?= $arItem['NAME'] ?>"
                   data-image="<?= $src ?>">
                    купить
                </a>
            </form>
        </div>
    </div>
<? }

function sw_product_hbmdev_card_2($arItem, $class = "")
{
    $img2 = $arItem['PREVIEW_PICTURE'];
    if (is_array($img2))
        $img2 = $arItem['PREVIEW_PICTURE']['ID'];

    $file = CFile::ResizeImageGet($img2, array('width' => 373, 'height' => 250), BX_RESIZE_IMAGE_PROPORTIONAL, true);
    if (!empty($file)) {
        $src = $file["src"];
    } else {
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }
    ?>
    <div class="card_2 card">
        <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>" class="card__link">
            <div class="card__title"><?= $arItem['NAME'] ?></div>
            <div class="card__photo">
                <img src="<?= $src ?>" alt="<?= $arItem['NAME'] ?>">
            </div>
        </a>
        <?
        if (!empty($arItem['PROPERTIES'])) {
            ?>
            <div class="card__chars">
                <? foreach ($arItem['PROPERTIES'] as $arOneProp) {
                    if ($arOneProp['ID'] == 2 || $arOneProp['ID'] == 17 || $arOneProp['ID'] == 3 || $arOneProp['ID'] == 4 || $arOneProp['ID'] == 5 || $arOneProp['ID'] == 6 || $arOneProp['ID'] == 7 || $arOneProp['ID'] == 40 || $arOneProp['ID'] == 41 || $arOneProp['ID'] == 47 || $arOneProp['ID'] == 57) {
                        continue;
                    }
                    if (!empty($arOneProp['VALUE'])) { ?>
                        <div class="good__char">
                            <div class="good__char__title"><?= $arOneProp['NAME'] ?></div>
                            <div class="good__char__value">

                                <?
                                if (is_array($arOneProp['VALUE'])) {
                                    $total = count($arOneProp['VALUE']);
                                    $counter = 0;
                                    foreach ($arOneProp['VALUE'] as $value) {
                                        $counter++;
                                        if ($counter == $total) {
                                            // делаем что-либо с последним элементом...
                                            echo $value . '.';
                                        } else {
                                            // делаем что-либо с каждым элементом
                                            echo $value . ', ';
                                        }
                                    }
                                } else {
                                    echo $arOneProp['VALUE'];
                                }
                                ?>

                            </div>
                        </div>
                        <?
                    } ?>
                    <?
                } ?>
            </div>
            <?
        } ?>
        <div class="card__buy-line">
            <div class="card__price">
                <div class="card__price__title">Цена</div>
                <? $price = CCatalogProduct::GetOptimalPrice($arItem["ID"], 1, 'N'); ?>
                <? if (!empty($arItem['PROPERTIES']['OLD_PRICE']['VALUE'])) { ?>
                    <div class="card__price_new"><?= $price['RESULT_PRICE']["DISCOUNT_PRICE"] ?>
                        <span
                            class="card__price_mute"> руб.</span>
                    </div>
                    <div class="price__old"><?= $arItem['PROPERTIES']['OLD_PRICE']['VALUE'] ?> Р</div>
                <? } else { ?>
                    <div class="price__current"><?= $price['RESULT_PRICE']["DISCOUNT_PRICE"] ?> Р</div>
                    <?
                } ?>
            </div>
            <form action="<?= SITE_TEMPLATE_PATH ?>/ajax/basket/put_offers_in_basket.php" method="POST"
                  class="add_to_cart_form">
                <input type="hidden" name="product_id" value="<?= $arItem['ID'] ?>">
                <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                       data-id="<?= $arItem['ID'] ?>">
                <a href="#" class="card__button button add_to_cart" data-name="<?= $arItem['NAME'] ?>"
                   data-image="<?= $src ?>">
                    купить
                </a>
            </form>
        </div>
    </div>
<? }

function sw_news_post_big($arFields_slider, $prop = "")
{
    $file_big = CFile::ResizeImageGet($arFields_slider['PREVIEW_PICTURE'], array(
        'width' => 626,
        'height' => 421
    ), BX_RESIZE_IMAGE_EXACT, true);

    if (empty($file_small['src'])) {
        $file_small['src'] = DEFAULT_IMAGE;
    }

    if (!empty($arFields_slider["PROPERTIES"]['T4']['VALUE'])) {
        $color = $arFields_slider["PROPERTIES"]['T4']['VALUE'];
    } elseif (!empty($prop['T4']['VALUE'])) {
        $color = $prop['T4']['VALUE'];
    } else {
        $color = ' rgb(255, 215, 135)';
    }

    $resblock = CIBlock::GetByID($arFields_slider['IBLOCK_ID']);
    if ($arresblock = $resblock->GetNext())
        ?>
        <!-- item-->
        <div class="news-post --big news-item" style="background-color: <?=$color ?>;">
    <div class="news-post__content --big flex --direction-column --just-space">
        <div class="news-post__top">
            <div class="tags flex">
                <a href="<?= $arFields_slider['LIST_PAGE_URL'] ? $arFields_slider['LIST_PAGE_URL'] : cur_page ?>"
                   class="tag btn p --m">
                    <span><?= $arFields_slider['IBLOCK_NAME'] ? $arFields_slider['IBLOCK_NAME'] : $arresblock['NAME'] ?></span>
                </a>
            </div>
            <a href="<?= $arFields_slider['DETAIL_PAGE_URL'] ?>"
               class="news-post__name --big h4"><b><?= $arFields_slider['NAME'] ?></b></a>
            <div class="news-post__desc --big p"><?= $arFields_slider['PREVIEW_TEXT'] ?></div>
        </div>
        <div class="news-post__btn-wrap flex --just-center">
            <a href="<?= $arFields_slider['DETAIL_PAGE_URL'] ?>"
               class="news-post__btn btn --border">
                <span>Подробнее</span>
            </a>
        </div>
    </div>
    <a href="<?= $arFields_slider['DETAIL_PAGE_URL'] ?>" class="news-post__bg-pic">
        <div class="news-post__gradient"
             style="background: linear-gradient(270deg, rgba(255, 219, 149, 0) 0%, <?= $color ?> 100%);"></div>
        <div class="news-post__gradient"
             style="background: linear-gradient(270deg, rgba(255, 219, 149, 0) 0%, <?= $color ?> 100%);"></div>
        <div class="news-post__pic-img" style="background-image: url(<?= $file_big['src'] ?>)"></div>
    </a>
    </div>
<? }

function sw_news_post_small($arFields_slider, $class = "")
{
    $file_small = CFile::ResizeImageGet($arFields_slider['PREVIEW_PICTURE'], array(
        'width' => 421,
        'height' => 421
    ), BX_RESIZE_IMAGE_EXACT, true);

    if (empty($file_small['src'])) {
        $file_small['src'] = DEFAULT_IMAGE;
    }

    $resblock = CIBlock::GetByID($arFields_slider['IBLOCK_ID']);
    if ($arresblock = $resblock->GetNext())
        ?>
        <!-- item-->
        <div class="news-post news-item">
        <a href="<?= $arFields_slider['DETAIL_PAGE_URL'] ?>" class="news-post__bg">
    <img src="<?= $file_small['src'] ?>" alt="<?= $arFields_slider['NAME'] ?>">
    </a>
    <div class="news-post__mask">
        <div class="news-post__content flex --direction-column --just-space">
            <div class="news-post__top">
                <div class="tags flex">
                    <a href="<?= $arFields_slider['LIST_PAGE_URL'] ? $arFields_slider['LIST_PAGE_URL'] : cur_page ?>"
                       class="tag btn p --m">
                        <span><?= $arFields_slider['IBLOCK_NAME'] ? $arFields_slider['IBLOCK_NAME'] : $arresblock['NAME'] ?></span>
                    </a>
                </div>
                <a href="<?= $arFields_slider['DETAIL_PAGE_URL'] ?>"
                   class="news-post__name h4"><b><?= $arFields_slider['NAME'] ?></b></a>
                <div class="news-post__desc p --l"><?= $arFields_slider['PREVIEW_TEXT'] ?></div>
            </div>
            <div class="news-post__btn-wrap flex --just-center">
                <a href="<?= $arFields_slider['DETAIL_PAGE_URL'] ?>"
                   class="news-post__btn btn --border">
                    <span>Подробнее</span>
                </a>
            </div>
        </div>
    </div>
    </div>
<? }

function sw_product_viewed($arItem, $class = "")
{ ?>
    <?

    $product_id = $arItem['ID'];
    if (!empty($arItem['ITEM_ID']))
        $product_id = $arItem['ITEM_ID'];
    if (!empty($arItem['PRODUCT_ID']))
        $product_id = $arItem['PRODUCT_ID'];

    $first_input_id = $product_id;
    $parent_id = '';

    ?>
    <?php
    $img = $arItem['PREVIEW_PICTURE'];
    if (is_array($img))
        $img = $arItem['PREVIEW_PICTURE']['ID'];
    ?>

    <div class="similar-items__item">
        <div class="similar-items__item-img">

            <?
            //$renderImage = CFile::ResizeImageGet($arItem["PREVIEW_PICTURE"], Array("width" => 221, "height" => 191), BX_RESIZE_IMAGE_EXACT, false);
            $file = CFile::ResizeImageGet($img, array(
                'width' => 167,
                'height' => 82
            ), BX_RESIZE_IMAGE_PROPORTIONAL, true);
            $src = $file["src"];
            ?>
            <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                <img src="<?= $src ?>" alt="<?= $arItem["NAME"] ?>">
            </a>
        </div>
        <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>" class="similar-items__item-name">
            <?= $arItem["NAME"] ?><!-- <br>Ban 7069 5206 --></a>
        <div class="similar-items__item-price">
            <?
            $price = CCatalogProduct::GetOptimalPrice($arItem["ID"], 1, 'N');
            ?>

            <?php if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                <s><?= $price['RESULT_PRICE']['BASE_PRICE'] ?><i class="rouble">a</i></s>
            <?php } ?>
            <b><?= $price['RESULT_PRICE']["DISCOUNT_PRICE"] ?><i class="rouble">a</i></b>
        </div>
    </div>

<? }

function sw_check_in_compare($product_id)
{
    $compare_res = $_SESSION['CATALOG_COMPARE_LIST'][CATALOG_IBLOCK];
    if (isset($compare_res['ITEMS'][$product_id]))
        return true;
    else
        return false;
}

function sw_compare_total()
{
    $compare_res = $_SESSION['CATALOG_COMPARE_LIST'][CATALOG_IBLOCK];
    return sizeof($compare_res['ITEMS']);
}

function sw_get_default_photo($thumb = array())
{
    $photo_id = sw_get_option('DEFAULT_PHOTO');
    if ($photo_id) {
        if (empty($thumb)) {
            return CFile::GetPath($photo_id);
        } else {
            $file = CFile::ResizeImageGet($photo_id,
                $thumb, BX_RESIZE_IMAGE_PROPORTIONAL, true
            );
            return $file['src'];
        }
    } else {
        return '/bitrix/templates/crushpro/s/img/default.jpg';
    }
}

function sw_add_product_to_basket($product_id, $quantity = 1)
{
    CModule::IncludeModule('catalog');
    CModule::IncludeModule("sale");

    $basket_product = CSaleBasket::GetList(
        array("ID" => "DESC"),
        array(
            "PRODUCT_ID" => $product_id,
            "FUSER_ID" => CSaleBasket::GetBasketUserID(),
            "LID" => SITE_ID,
            "ORDER_ID" => "NULL"
        ),
        false,
        false,
        array()
    )->Fetch();

    // Update product quantity

    if ($basket_product) {
        $result = false;

        CSaleBasket::Update($basket_product['ID'], array(
            "QUANTITY" => $quantity
        ));
        $product_quantity = $quantity;
        $item_id = $basket_product['ID'];
    } else {
        $item_id = Add2BasketByProductID($product_id, $quantity);

        /*global $APPLICATION;
        $ex = $APPLICATION->GetException();
        echo $ex->GetString();
        var_dump($item_id);die();*/
        //var_dump($item_id);die();
    }

    return $item_id;
}

/*----------  events  ----------*/

function sw_check_in_fav($product_id)
{
    /* global $USER;
     global $DB;*/
    CModule::IncludeModule("sale");
    $res = CSaleBasket::GetList(
        array("ID" => "DESC"),
        array(
            "PRODUCT_ID" => $product_id,
            "FUSER_ID" => CSaleBasket::GetBasketUserID(),
            "LID" => SITE_ID,
            'DELAY' => 'Y',
            "ORDER_ID" => "NULL"
        ),
        false,
        false,
        array()
    );
    if ($res->Fetch()) {
        return true;
    } else {
        return false;
    }
    /* if(!$USER->IsAuthorized())
       return false;
     $q = "SELECT `id` FROM `sw_favorites` WHERE `product_id` = ". $product_id ." AND `user_id` = " . CUser::GetID();
     $results = $DB->Query($q);
     if($results->Fetch()){
       return true;
     }else{
       return false;
     }*/
}

function sw_favorites_total()
{
    global $DB;
    global $USER;

    CModule::IncludeModule("sale");

    $res = CSaleBasket::GetList(
        array("ID" => "DESC"),
        array(
            "FUSER_ID" => CSaleBasket::GetBasketUserID(),
            "LID" => SITE_ID,
            'DELAY' => 'Y',
            "ORDER_ID" => "NULL"
        ),
        false,
        false,
        array()
    );
    $total = $res->SelectedRowsCount();
    return $total;
    /*if(!$USER->IsAuthorized()){
      return 0;
    }
    $q = "SELECT `id`, `product_id` FROM `sw_favorites` WHERE `user_id` = " . CUser::GetID();
    $results = $DB->Query($q);
    return intval($results->SelectedRowsCount());*/
}

define('cur_lang', 'ru');
function sw_get_ending($number)
{
    $end = '';
    if ($number == 1) {
        $end = '';
    } elseif ($number > 1 && $number < 5) {
        $end = 'а';
    } elseif (($number > 4 && $number < 2100) || $number == 0) {
        $end = 'ов';
        if (cur_lang == 'ua')
            $end = 'ів';
    } else {
        if (preg_match_all('/\d+/', $text, $numbers))
            $lastnum = end($numbers[0]);
        if ($lastnum = 1) {
            $end = '';
        } elseif ($lastnum > 1 && $lastnum < 5) {
            $end = 'а';
        } else {
            $end = 'ов';
            if (cur_lang == 'ua')
                $end = 'ів';
        }
    }
    return $end;
}

function sw_get_ending_groups($number)
{
    $end = '';
    if ($number == 1) {
        $end = 'а';
    } elseif ($number > 1 && $number < 5) {
        $end = 'ы';
    } elseif (($number > 4 && $number < 21) || $number == 0) {
        $end = '';
        if (cur_lang == 'ua')
            $end = 'ів';
    } else {
        if (preg_match_all('/\d+/', $text, $numbers))
            $lastnum = end($numbers[0]);
        if ($lastnum = 1) {
            $end = '';
        } elseif ($lastnum > 1 && $lastnum < 5) {
            $end = 'а';
        } else {
            $end = 'ов';
            if (cur_lang == 'ua')
                $end = 'ів';
        }
    }
    return $end;
}

function sw_check_payment_avaliable($payment_id, $delivery)
{
    if (!empty($_REQUEST['delivery']) || !empty($delivery)) {
        if (!empty($_REQUEST['delivery']))
            $delivery = (int)$_REQUEST['delivery'];
        global $DB;
        $q = ' SELECT dp.DELIVERY_ID
                    FROM b_sale_delivery2paysystem dp 
                        LEFT JOIN b_sale_pay_system_action pa ON pa.PAY_SYSTEM_ID = dp.PAYSYSTEM_ID
                            WHERE dp.PAYSYSTEM_ID = ' . $payment_id;
        $results = $DB->Query($q);
        $delivery_list = array();
        while ($row = $results->Fetch()) {
            array_push($delivery_list, $row['DELIVERY_ID']);
        }
        if (!empty($delivery_list) && !in_array($delivery, $delivery_list))
            return false;
    }
    return true;
}

function sw_per_page_val()
{
    global $APPLICATION;
    $grid = 20;
    $grid_val = $APPLICATION->get_cookie("sw_per_page");
    if ($grid_val)
        $grid = $grid_val;
    return $grid;
}

function sw_sort_val()
{
    global $APPLICATION;
    $sort_val = 'shows';
    $sort_cook = $APPLICATION->get_cookie("sw_sort");
    if ($sort_cook)
        $sort_val = $sort_cook;
    return $sort_val;
}

function sw_order_val()
{
    global $APPLICATION;
    $order_val = 'desc';
    $order_cook = $APPLICATION->get_cookie("sw_order");
    if ($order_cook)
        $order_val = $order_cook;
    return $order_val;
}

function sw_grid_val()
{
    global $APPLICATION;
    $grid = 'grid';
    $grid_val = $APPLICATION->get_cookie("sw_grid");
    if ($grid_val)
        $grid = $grid_val;
    return $grid;
}

function sw_product_cat($arItem, $class = "", $isRecomendate = false)
{
    $selected = $arItem["OFFERS_SELECTED_ID"];
    $product_id = $arItem['ID'];
    if (!empty($arItem['ITEM_ID']))
        $product_id = $arItem['ITEM_ID'];
    if (!empty($arItem['PRODUCT_ID']))
        $product_id = $arItem['PRODUCT_ID'];

    $img2 = $arItem['PREVIEW_PICTURE'];
    if (is_array($img2))
        $img2 = $arItem['PREVIEW_PICTURE']['ID'];

    $file = CFile::GetPath($img2);
    if (!empty($file)) {
        $src = $file;
    } else {
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }

    $res_product = CIBlockElement::GetByID($arItem["ID"]);
    if ($ar_res_product = $res_product->GetNext()) {
        $arItem['DETAIL_PAGE_URL'] = $ar_res_product["DETAIL_PAGE_URL"];
    }

    $sum = 0;
    CModule::IncludeModule('iblock');
    $arSelect_STARS = array();
    $arFilter_STARS = array(
        "IBLOCK_ID" => 2,
        "ACTIVE" => "Y",
        'PROPERTY_ID' => $arItem['ID'],
        'PROPERTY_1346' => "26478"
    );
    $res_STARS = CIBlockElement::GetList(array("DATE_ACTIVE_FROM" => "DESC"), $arFilter_STARS, false, array(), $arSelect_STARS);
    $total_STARS = $res_STARS->SelectedRowsCount();
    while ($ob_STARS = $res_STARS->GetNextElement()) {
        $arProps_STARS = $ob_STARS->GetProperties();
        $sum += (int)$arProps_STARS['P1']['VALUE'];
    }
    if (!empty($total_STARS)) {
        $res_ev = round($sum / $total_STARS);
    }
    $prop_p1 = $arItem['PROPERTIES']['P1']['VALUE'];
    if (empty($prop_p1)) {
        $prop_p1 = $arItem["PROPERTY_P1_VALUE"];
    }

    $prop_p100 = $arItem['PROPERTIES']['P100']['VALUE'];
    if (empty($prop_p100)) {
        $prop_p100 = $arItem["PROPERTY_P100_VALUE"];
    }

    $prop_p200 = $arItem['PROPERTIES']['P200']['VALUE'];
    if (empty($prop_p200)) {
        $prop_p200 = $arItem["PROPERTY_P200_VALUE"];
    }

    $prop_p300 = $arItem['PROPERTIES']['SPEC']['VALUE'];
    if (empty($prop_p300)) {
        $prop_p300 = $arItem["PROPERTY_SPEC_VALUE"];
    }

    $prop_p400 = $arItem['PROPERTIES']['P400']['VALUE'];
    if (empty($prop_p400)) {
        $prop_p400 = $arItem["PROPERTY_P400_VALUE"];
    }

    $prop_gift = !empty($arItem['PROPERTIES']['PROP_GIFT']['VALUE']);

    $isAvailable = ($arItem["CATALOG_AVAILABLE"] == 'Y') ? 'true' : 'false';

    $arOfferList = CCatalogSKU::getOffersList($arItem['ID'], 4);

    foreach ($arOfferList as $res) {
        foreach ($res as $res___SKU) {
            $id3[] = $res___SKU['ID'];
        }
    }

    if (!empty($id3)) {
        $arSelect_news = array();
        $arFilter_news = array(
            "IBLOCK_ID" => 16,
            "ACTIVE" => "Y",
            'ID' => $id3
        );
        $arOffer = CIBlockElement::GetList(
        //array("ID" => "ASC"),
            array("CATALOG_QUANTITY" => "DESC"),
            $arFilter_news,
            false,
            array(),
            $arSelect_news
        );
        $total_news = $arOffer->SelectedRowsCount();
        if ($total_news > 0) {
            while ($ar_offers = $arOffer->GetNextElement()) {
                $offer = $ar_offers->GetFields();
                $offer['PROPERTIES'] = $ar_offers->GetProperties();
                $allOffers[] = $offer;
                $id_e = $offer['ID'];
            }
        }
    }

    if (!empty($allOffers) && is_array($allOffers)) {
        $srcOffer = CFile::GetFileArray($allOffers[0]['PREVIEW_PICTURE'])['SRC'];
        if (!$srcOffer) {
            $srcOffer = CFile::GetFileArray($allOffers[0]['DETAIL_PICTURE'])['SRC'];
        }
        if ($srcOffer) {
            $img2 = $allOffers[0]['PREVIEW_PICTURE'];
            $src = $srcOffer;
        }

        $arFirstOffer = CCatalogProduct::GetByID($allOffers[0]['ID']);
        if (!empty($arFirstOffer) && $arFirstOffer["QUANTITY"] == 0) {
            $isAvailable = false;
        }

        if ($arItem["PROPERTIES"]["ROZNICHNOE_NAIMENOVANIE_SVOYSTVO"]["VALUE"]
            && $allOffers[0]['PROPERTIES']['TSVET']['VALUE']) {
            $arItem['NAME'] = $arItem["PROPERTIES"]["ROZNICHNOE_NAIMENOVANIE_SVOYSTVO"]["VALUE"] . ' ' . $allOffers[0]['PROPERTIES']['TSVET']['VALUE'];
        } elseif ($arItem["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"]
            && $allOffers[0]['PROPERTIES']['TSVET']['VALUE']) {
            $arItem['NAME'] = $arItem["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"] . ' ' . $allOffers[0]['PROPERTIES']['TSVET']['VALUE'];
        }
    }


    $VALUES = array();
    if (isset($res[$arItem["ID"]]) && count($res[$arItem["ID"]]) > 0) {
        $res = CIBlockElement::GetProperty(16, array_key_first($res[$arItem["ID"]]), "sort", "asc", array("CODE" => "MORE_PHOTO"));
        while ($ob = $res->GetNext()) {
            if ($ob['VALUE'] > 0) {
                $VALUES[] = $ob['VALUE'];
            }
        }
    } else {
        $res = CIBlockElement::GetProperty(4, $arItem["ID"], "sort", "asc", array("CODE" => "MORE_IMAGES"));
        while ($ob = $res->GetNext()) {
            if ($ob['VALUE'] > 0) {
                $VALUES[] = $ob['VALUE'];
            }
        }

        if (!empty($arItem['PREVIEW_PICTURE']['ID']) && is_array($VALUES)) {
            array_unshift($VALUES, $arItem['PREVIEW_PICTURE']['ID']);
        }
    }
    $arImg = [];
    $img2 = $arItem['PREVIEW_PICTURE'];
    if (is_array($img2))
        $img2 = $arItem['PREVIEW_PICTURE']['ID'];

    $file = CFile::GetPath($img2);
    if (!empty($file)) {
        //        $src = $file;
        $src = $img2;
        //        $arImg[] = $file;
        $arImg[] = $img2;
    } else {
        //      $arImg[] = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }

    foreach ($VALUES as $val) {
        if ($val != 0) {
            //          $arImg[] = CFile::GetPath($val);
            $arImg[] = $val;
        } else {
            //          $arImg[] = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
        }
    }

    if (count($VALUES) > 0) {
        unset($arImg[0]);
    }
    $res = CIBlockSection::GetByID($arItem["IBLOCK_SECTION_ID"]);
    if ($ar_res = $res->GetNext()) {
        $parentSectionId = $ar_res['IBLOCK_SECTION_ID'];
    }
    $db_res_ozon_price = CPrice::GetList(
        array(),
        array(
            "PRODUCT_ID" => $product_id,  // Получаем ID Товара
            "CATALOG_GROUP_ID" => 15
        )
    );
    if ($ar_res_ozon = $db_res_ozon_price->Fetch()) {
        $priceOZONE = $ar_res_ozon["PRICE"];
    }
    $db_res_ozon_old = CPrice::GetList(
        array(),
        array(
            "PRODUCT_ID" => $product_id,  // Получаем ID Товара
            "CATALOG_GROUP_ID" => 16
        )
    );
    if ($ar_res_old = $db_res_ozon_old->Fetch()) {
        $priceOZONE_OLD = $ar_res_old["PRICE"];
    }
    ?>

    <div class="card" style="height: 100%" data-id-item="<?= $product_id ?>">
        <?php
        $active1 = '';
        $act = 'add';
        $text = '+  в избранное';
        global $APPLICATION;
        $arElements = unserialize($APPLICATION->get_cookie('bo_favorites'));
        if ($arElements[$arItem['ID']]) {
            $active1 = ' active';
            $act = 'del';
            $text = 'В избранном';
        }
        ?>
        <?
        $frame = new \Bitrix\Main\Page\FrameBuffered('card__fav__' . $product_id);
        $frame->begin();
        ?>
        <a href="#!" class="card__button add_to_fav_main <?= $active1 ?>" data-id="<?= $arItem['ID'] ?>"
           data-act="<?= $act ?>">
            <i class="icon-like"></i>
            <span class="card__button__hint favorite-button"><?= $text ?></span>
        </a>
        <?
        $frame->beginStub();
        ?>
        <a href="#!" class="card__button add_to_fav_main ">
            <i class="icon-like"></i>
            <span class="card__button__hint favorite-button"></span>
        </a>
        <?
        $frame->end();
        ?>

        <div class="card__labels">
            <? $price = CCatalogProduct::GetOptimalPrice($arItem["ID"], 1, 'N');
            if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]): ?>
                <div class="card__label discount">
                    -<?= round(($price['RESULT_PRICE']["BASE_PRICE"] - $price['RESULT_PRICE']['DISCOUNT_PRICE']) * 100 / $price['RESULT_PRICE']["BASE_PRICE"]) ?>
                    %
                </div>
            <? endif; ?>
            <? if ($prop_p300[0] == 'Новинка') : ?>
                <div class="card__label new">New</div>
            <? endif; ?>

            <? if ($prop_p300 == 'Новинка') : ?>
                <div class="card__label new">New</div>
            <? endif; ?>

            <? if ($prop_p200 == 'Y') : ?>
                <div class="card__label top">Это Хит!</div>
            <? endif; ?>

            <?
            if ($prop_p100 == 'Да'): ?>
                <div class="card__label sale">Супер-скидка!</div>
            <? endif; ?>

            <? if ($prop_p400 == 'Y'): ?>
                <div class="card__label recom">Спасимбо рекомендует</div>
            <? endif; ?>
        </div>

        <? if ($prop_gift) : ?>
            <div class="card__gift"></div>
        <? endif; ?>

        <?/*<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="card__photo">
            <img src="<?=resizeImageByWidth($img2, 300);?>" alt="<?=$arItem['NAME']?>">
        </a>*/ ?>

        <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="slidel-discount-mob" data-cntimg="<?php echo COUNT($arImg) > 5 ? 5 : COUNT($arImg); ?>"
           data-curslide="1">
            <?php $ii = 0; ?>

            <? foreach ($arImg as $Image): ?>
                <div class="slidel-discount-mob__item">
                    <img data-lazy="<?= makeWebp(resizeImageByWidth($Image, 300)); ?>"
                         alt="<?= $arItem["NAME"]; ?>">
                </div>
                <?php
                $ii++;
                if ($ii == 5) break;
                ?>
            <? endforeach; ?>
        </a>

        <? if (CSite::InDir('/catalog/')) { ?>
            <div class="swiper-container catalog-list-item-slider">
                <div class="swiper-wrapper">
                    <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="card__photo swiper-slide">
                        <img src="<?= resizeImageByWidth($img2, 200); ?>">
                    </a>
                    <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="card__photo swiper-slide">
                        <img src="<?= resizeImageByWidth($img2, 200); ?>">
                    </a>
                </div>
                <div class="catalog-list-item-swiper-pagination"></div>
            </div>
        <? } ?>

        <div class="card__content">
            <? if (!empty($prop_p1)) : ?>
                <div class="card__vendor">Артикул: <?= $prop_p1 ?></div>
            <? endif; ?>
            <div class="mobile-price-block" <? if ($arItem["CATALOG_AVAILABLE"] == 'N') : ?> style="visibility:hidden" <? endif; ?>>
                <? if ($parentSectionId == "752" || strpos($arItem['NAME'], 'Коляска')  !== false): ?>
                    <? if (!empty($priceOZONE) && $priceOZONE != 0) { ?>
                        <div class="new-price">
                            <?= number_format($priceOZONE, 0, '.', ' ') ?> руб<br>
                        </div>
                        <? if ($priceOZONE_OLD): ?>
                            <div class="old-price"><?= number_format($priceOZONE_OLD, 0, '.', ' ') ?> руб</div>
                        <? endif; ?>
                    <? } else { ?>
                        <div class="new-price">
                            <?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб
                        </div>
                        <? if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) : ?>
                            <div class="old-price">
                                <?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб
                            </div>
                        <? endif; ?>
                    <? } ?>
                <? else: ?>
                    <div class="new-price">
                        <?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб
                    </div>
                    <? if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) : ?>
                        <div class="old-price">
                            <?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб
                        </div>
                    <? endif; ?>
                <? endif; ?>


            </div>

            <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="card__title">
                <?= $arItem['NAME'] ?>
            </a>

            <div class="card__info-small">
                <? if ($isAvailable) : ?>
                    <h5 class="good__title js-aval-block good__title-new good__title-in-stock">
                        <spaan class="avaible-icon">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0)">
                                    <path d="M5.36085 12.2299C5.22721 12.3644 5.04489 12.4394 4.85549 12.4394C4.66609 12.4394 4.48376 12.3644 4.35012 12.2299L0.314135 8.19331C-0.104712 7.77447 -0.104712 7.09528 0.314135 6.67722L0.819501 6.17172C1.23848 5.75288 1.91688 5.75288 2.33573 6.17172L4.85549 8.69161L11.6642 1.88273C12.0832 1.46388 12.7623 1.46388 13.1805 1.88273L13.6858 2.38823C14.1047 2.80707 14.1047 3.48613 13.6858 3.90432L5.36085 12.2299Z"
                                          fill="#23A420"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0">
                                        <rect width="14" height="14" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </spaan>
                        В наличии
                    </h5>
                <? else: ?>
                    <h5 class="good__title js-aval-block good__title-new good__title-out-stock">
                        <span class="avaible-icon">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0)">
                                <path d="M7.32166 5.99953L11.8083 1.5127C11.9317 1.38918 11.9998 1.2244 12 1.0487C12 0.872892 11.9319 0.707916 11.8083 0.584599L11.4151 0.191526C11.2915 0.067721 11.1267 -8.39233e-05 10.9508 -8.39233e-05C10.7752 -8.39233e-05 10.6104 0.067721 10.4868 0.191526L6.0002 4.67806L1.51337 0.191526C1.38995 0.067721 1.22507 -8.39233e-05 1.04927 -8.39233e-05C0.873659 -8.39233e-05 0.70878 0.067721 0.585366 0.191526L0.192 0.584599C-0.064 0.840599 -0.064 1.25699 0.192 1.5127L4.67873 5.99953L0.192 10.4862C0.0684878 10.6099 0.000487805 10.7746 0.000487805 10.9504C0.000487805 11.1261 0.0684878 11.2908 0.192 11.4145L0.585268 11.8075C0.708683 11.9312 0.873658 11.9991 1.04917 11.9991C1.22498 11.9991 1.38985 11.9312 1.51327 11.8075L6.0001 7.32089L10.4867 11.8075C10.6103 11.9312 10.7751 11.9991 10.9507 11.9991H10.9509C11.1266 11.9991 11.2914 11.9312 11.415 11.8075L11.8082 11.4145C11.9316 11.2909 11.9997 11.1261 11.9997 10.9504C11.9997 10.7746 11.9316 10.6099 11.8082 10.4863L7.32166 5.99953Z"
                                      fill="#FE7865"/>
                                </g>
                                <defs>
                                <clipPath id="clip0">
                                <rect width="12" height="12" fill="white"/>
                                </clipPath>
                                </defs>
                                </svg>
                        </span>
                        Нет в наличии
                    </h5>
                <? endif; ?>

                <div class="card__info-small-rigth">
                    <div class="color-quanity">
                        <? if (!empty($id3)): ?>
                            <?= count($id3) ?> цветов
                        <? endif; ?>
                    </div>
                    <? if (($total_STARS != 0)) : ?>
                        <div class="card__rating rating">
                            <div class="star full"><i class="icon-star"></i></div>
                            <?= $arItem['PROPERTIES']['rating']['VALUE']?>
                        </div>
                        <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="card__comments"><?= $total_STARS ?></a>
                    <? else: ?>
                        <br>
                    <? endif; ?>
                </div>
            </div>

            <div class="card__review"></div>

            <div class="sku_section">
                <? if (!empty($allOffers) && is_array($allOffers)) : ?>

                    <?
                    $classIsSetted = false;
                    $addedActiveClass = false;
                    $i = 0;
                    $sfirt = true;

                    if (!empty($_GET['offer']) && !$isRecomendate) {
                        $sfirt = false;
                    }
                    ?>
                    <div class="good__info__block">
                        <div class="good__info__block">
                            <h5 class="good__title good__title-new-color ">Цвет</h5>
                            <div class="hide-tablet js-show-hide-colors">
                                <span class="icon">
                                    <svg width="18" height="11" viewBox="0 0 18 11" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
<path d="M9.00371 8.15717L2.15915 1.01027L2.15914 1.01026C1.85968 0.697621 1.37139 0.697621 1.07192 1.01026L1.07189 1.01029C0.77604 1.31936 0.776029 1.81774 1.0719 2.12677L1.07192 2.12679L8.4601 9.84132L8.46011 9.84134C8.75957 10.1539 9.24785 10.1539 9.54731 9.84134L9.54733 9.84132L16.9355 2.12679L16.9355 2.12681L16.9373 2.12489C17.2279 1.81068 17.2198 1.31223 16.9185 1.0084L16.9185 1.0084C16.6213 0.708644 16.1473 0.708632 15.8501 1.0084L15.8501 1.00839L15.8483 1.01027L9.00371 8.15717Z"
      fill="black" stroke="#090909" stroke-width="0.3"/>
</svg>
                                </span>
                            </div>
                        </div>
                        <div class="good__colors js-show-hide-colors--target">
                            <?
                            $first_id_e = $allOffers[0]["ID"];
                            ?>
                            <? foreach ($allOffers as $offer) : ?>

                                <?
                                $id_e = $offer['ID'];

                                $price = CCatalogProduct::GetOptimalPrice($offer["ID"], 1, 'N');
                                $ar_res = CCatalogProduct::GetByID($offer['ID']);

                                $file_small_big_g = CFile::GetPath($offer['PREVIEW_PICTURE']);
                                $file_small = CFile::GetPath($offer['PROPERTIES']['IMG']['VALUE']);

                                foreach ($offer['PROPERTIES']['MORE_PHOTO']['VALUE'] as &$itmof) {
                                    //$itmof = CFile::GetPath($itmof);
                                    $itmof = makeWebp($itmof);
                                }

                                $haveColorFilter = false;
                                $colorsArr = [];
                                global $arrFilter;
                                if (isset($arrFilter['OFFERS']['PROPERTY_1131'])) {
                                    $haveColorFilter = true;
                                    $colorsArr = $arrFilter['OFFERS']['PROPERTY_1131'];
                                } elseif (isset($arrFilter['OFFERS']['=PROPERTY_1131'])) {
                                    $haveColorFilter = true;
                                    $colorsArr = $arrFilter['OFFERS']['=PROPERTY_1131'];
                                }

                                if (!$addedActiveClass
                                    && (!$haveColorFilter
                                        && $selected == $id_e
                                        || (!$haveColorFilter
                                            && $sfirt
                                            && $ar_res["AVAILABLE"] == 'Y')
                                        || ($haveColorFilter
                                            && in_array($offer['PROPERTIES']['TSVET']['VALUE_ENUM_ID'], $colorsArr))
                                    )) {
                                    $addedActiveClass = true;
                                    $sfirt = false;
                                }

                                if (($total_news - 1) == $i && !$addedActiveClass) {
                                    $addedActiveClass = true;
                                }

                                if ($arItem["PROPERTIES"]["ROZNICHNOE_NAIMENOVANIE_SVOYSTVO"]["VALUE"]
                                    && $offer['PROPERTIES']['TSVET']['VALUE']) {
                                    $offer["NAME"] = $arItem["PROPERTIES"]["ROZNICHNOE_NAIMENOVANIE_SVOYSTVO"]["VALUE"] . ' ' . $offer['PROPERTIES']['TSVET']['VALUE'];
                                } elseif ($arItem["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"]
                                    && $offer['PROPERTIES']['TSVET']['VALUE']) {
                                    $offer["NAME"] = $arItem["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"] . ' ' . $offer['PROPERTIES']['TSVET']['VALUE'];
                                }

                                $src = $offer['PROPERTIES']['MORE_PHOTO']['VALUE'];
                                if (!empty($offer['PREVIEW_PICTURE']) && is_array($offer['PROPERTIES']['MORE_PHOTO']['VALUE'])) {
                                    array_unshift($src, makeWebp($offer['PREVIEW_PICTURE']));
                                }
                                ?>

                                <? $offerAval = ($ar_res["QUANTITY"] && $ar_res["AVAILABLE"] == 'Y') ? 'Y' : 'N'; ?>

                                <button class="good__color change_element sect-item
                                        <? if (!$classIsSetted && $addedActiveClass) echo ' click_this_do active '; ?>
                                        <? if (empty($file_small)) : ?>  <?= $offer['PROPERTIES']['CODE']['VALUE'] ?> <? endif ?>"
                                        data-test="<?= $sfirt; ?>"
                                        data-id="<?= $offer['ID'] ?>"
                                        data-img="<?= resizeImageByWidth($offer['PREVIEW_PICTURE'], 300);/*cloudImage($file_small_big, 300);*/ ?>"
                                        data-name="<?= $offer['NAME'] ?>"
                                        data-imgs='<?= json_encode($src) ?>'
                                        data-price='<?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб'
                                        data-src='<?= $arItem['DETAIL_PAGE_URL'] ?>?offer=<?= $offer['ID'] ?>'
                                        data-aval='<?= $offerAval ?>'>
                                    <? if (!empty($file_small)) : ?>
                                        <img src="<?= resizeImageByWidth($offer['PROPERTIES']['IMG']['VALUE'], 35); ?>">
                                    <? elseif (!empty($file_small_big_g)) : ?>
                                        <img src="<?= resizeImageByWidth($offer['PREVIEW_PICTURE'], 35); ?>">
                                    <? else: ?>
                                        <img src="<?= DEFAULT_IMAGE ?>" loading="lazy">
                                    <? endif ?>
                                </button>
                                <?
                                if ($ar_res["AVAILABLE"] == 'Y') {
                                    $sfirt = false;
                                }

                                if ($addedActiveClass) {
                                    $classIsSetted = true;
                                }

                                $i++;
                                ?>
                            <? endforeach; ?>
                        </div>
                    </div>
                <? endif; ?>
            </div>
            <div class="card__footer">
                <?
                $frame = new \Bitrix\Main\Page\FrameBuffered('card__price__' . $product_id);
                $frame->begin('');
                ?>
                <div class="card__price_block js-canb-hide" <? if (!$isAvailable) : ?>style="visibility:hidden"<? endif; ?>>
                    <? if ($parentSectionId == "752" || strpos($arItem['NAME'], 'Коляска') !==  false): ?>
                        <? if (!empty($priceOZONE) && $priceOZONE != 0) { ?>
                            <div class="card__price"><?= number_format($priceOZONE, 0, '.', ' ') ?> руб</div>
                            <? if ($priceOZONE_OLD): ?>
                                <span class="card__price_old"><?= number_format($priceOZONE_OLD, 0, '.', ' ') ?> руб</span>
                            <? endif; ?>
                        <? } else { ?>
                            <div class="card__price"><?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб</div>
                            <? if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                                <span class="card__price_old"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб</span>
                            <? } ?>
                        <? } ?>
                    <? else: ?>
                        <div class="card__price"><?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб</div>
                        <? if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                            <span class="card__price_old"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб</span>
                        <? } ?>
                    <? endif; ?>
                </div>
                <?
                $frame->end();
                ?>

                <div class="card__footer__buttons">
                    <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/basket/put_offers_in_basket.php" method="POST"
                          class="add_to_cart_form js-canb-hide"
                          <? if (!$isAvailable) : ?>style="visibility:hidden"<? endif; ?>>

                        <?/*<input type="hidden" name="product_id" value="<?=$id_e ? $id_e : $arItem['ID']?>">*/ ?>
                        <input type="hidden" name="product_id" value="<?= $first_id_e ? $first_id_e : $arItem['ID'] ?>">

                        <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                               data-id="<?= $arItem['ID'] ?>">
                        <?
                        $brand = CIBlockElement::GetByID($arItem["PROPERTIES"]["BRANDS"]["VALUE"]);
                        if ($arBrand = $brand->GetNext()) {
                            $brandName = $arBrand["NAME"];
                        } else {
                            $brandName = "";
                        }

                        $list = CIBlockSection::GetNavChain(false, $arItem["IBLOCK_SECTION_ID"], array(), true);
                        $arSections = [];
                        foreach ($list as $arSectionPath) {
                            $arSections[] = $arSectionPath["NAME"];
                        }
                        $section = implode("/", $arSections);
                        ?>

                        <a href="#" class="card__buy add_to_cart_new" data-name="<?= $arItem['NAME'] ?>"
                           data-image="<?= $src ?>" data-brand="<?= $brandName; ?>" data-section="<?= $section; ?>">
                            <div class="icon-cart-new">
                                <svg width="26" height="27" viewBox="0 0 26 27" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.45403 23.0449C7.44063 23.0449 6.61621 23.932 6.61621 25.0225C6.61621 26.1129 7.44063 27 8.45403 27C9.46743 27 10.2919 26.1129 10.2919 25.0225C10.2919 23.932 9.46743 23.0449 8.45403 23.0449ZM8.45403 26.209C7.84603 26.209 7.35134 25.6767 7.35134 25.0225C7.35134 24.3682 7.84603 23.8359 8.45403 23.8359C9.06203 23.8359 9.55673 24.3682 9.55673 25.0225C9.55673 25.6767 9.06203 26.209 8.45403 26.209Z"
                                          fill="white"/>
                                    <path d="M8.45399 25.418C8.65699 25.418 8.82156 25.2409 8.82156 25.0225C8.82156 24.804 8.65699 24.627 8.45399 24.627C8.25099 24.627 8.08643 24.804 8.08643 25.0225C8.08643 25.2409 8.25099 25.418 8.45399 25.418Z"
                                          fill="white"/>
                                    <path d="M21.0492 25.418C21.2522 25.418 21.4168 25.2409 21.4168 25.0225C21.4168 24.804 21.2522 24.627 21.0492 24.627C20.8462 24.627 20.6816 24.804 20.6816 25.0225C20.6816 25.2409 20.8462 25.418 21.0492 25.418Z"
                                          fill="white"/>
                                    <path d="M21.0492 23.0449C20.0358 23.0449 19.2114 23.932 19.2114 25.0225C19.2114 26.1129 20.0358 27 21.0492 27C22.0626 27 22.8871 26.1129 22.8871 25.0225C22.8871 23.932 22.0626 23.0449 21.0492 23.0449ZM21.0492 26.209C20.4412 26.209 19.9466 25.6767 19.9466 25.0225C19.9466 24.3682 20.4412 23.8359 21.0492 23.8359C21.6572 23.8359 22.1519 24.3682 22.1519 25.0225C22.1519 25.6767 21.6572 26.209 21.0492 26.209Z"
                                          fill="white"/>
                                    <path d="M6.06952 19.1402L8.74499 17.5078H14.7516C15.6391 17.5078 16.3817 16.8273 16.5525 15.9258H20.3806C21.5569 15.9258 22.5819 15.0694 22.8733 13.8431L25.0809 4.55409C25.109 4.4357 25.0847 4.31003 25.0151 4.21353C24.9455 4.11703 24.8383 4.06055 24.7248 4.06055H6.67801L6.49055 3.2719C6.0333 1.34546 4.42234 0 2.57295 0H1.10269C0.494693 0 0 0.532301 0 1.18652C0 1.84075 0.494693 2.37305 1.10269 2.37305H2.57295C3.41379 2.37305 4.14608 2.98424 4.35382 3.85947L7.17691 15.7366L4.98231 17.0755C4.17636 17.5665 3.67564 18.4899 3.67564 19.4854C3.67564 21.0119 4.82989 22.2539 6.24859 22.2539H22.5194C23.1274 22.2539 23.6221 21.7216 23.6221 21.0674C23.6221 20.4132 23.1274 19.8809 22.5194 19.8809H6.24859C6.04589 19.8809 5.88103 19.7035 5.88103 19.4854C5.88103 19.3432 5.95317 19.211 6.06952 19.1402ZM19.6938 11.4434H17.435L17.5728 8.54297H20.1073L19.6938 11.4434ZM20.851 8.54297H23.3742L22.6848 11.4434H20.4375L20.851 8.54297ZM19.5809 12.2344L19.1674 15.1348H17.2596L17.3974 12.2344H19.5809ZM16.6989 11.4434H14.4363L14.2984 8.54297H16.8367L16.6989 11.4434ZM17.6104 7.75195L17.7482 4.85156H20.6336L20.2201 7.75195H17.6104ZM16.8743 7.75195H14.2608L14.1229 4.85156H17.0121L16.8743 7.75195ZM13.5247 7.75195H12.0698C11.8667 7.75195 11.7022 7.92898 11.7022 8.14746C11.7022 8.36594 11.8667 8.54297 12.0698 8.54297H13.5623L13.7002 11.4434H11.4415L10.5014 4.85156H13.3867L13.5247 7.75195ZM10.6978 11.4434H8.43281L7.7434 8.54297H10.2841L10.6978 11.4434ZM10.8106 12.2344L11.2243 15.1348H9.31026L8.62085 12.2344H10.8106ZM11.968 15.1348L11.5543 12.2344H13.7378L13.8757 15.1348H11.968ZM14.6119 15.1348L14.4739 12.2344H16.6613L16.5235 15.1348H14.6119ZM22.1611 13.6472C21.953 14.523 21.2208 15.1348 20.3806 15.1348H19.9111L20.3246 12.2344H22.4968L22.1611 13.6472ZM23.5621 7.75195H20.9638L21.3773 4.85156H24.2515L23.5621 7.75195ZM9.75766 4.85156L10.1713 7.75195H7.55546L6.86605 4.85156H9.75766ZM2.57295 1.58203H1.10269C0.899994 1.58203 0.735129 1.40463 0.735129 1.18652C0.735129 0.968414 0.899994 0.791016 1.10269 0.791016H2.57295C3.34557 0.791016 4.06693 1.07847 4.63807 1.56906L4.11403 2.133C3.68064 1.78458 3.14488 1.58203 2.57295 1.58203ZM6.24859 20.6719H22.5194C22.7221 20.6719 22.887 20.8493 22.887 21.0674C22.887 21.2855 22.7221 21.4629 22.5194 21.4629H6.24859C5.23519 21.4629 4.41077 20.5758 4.41077 19.4854C4.41077 18.7743 4.76858 18.1147 5.34473 17.7637L7.78393 16.2755C7.92822 16.1874 7.99982 16.0064 7.9587 15.8334L5.06602 3.66362C4.9806 3.30365 4.83146 2.97596 4.6341 2.69214L5.1583 2.1281C5.44436 2.51316 5.65931 2.96626 5.77831 3.46776L8.66878 15.6283C8.71039 15.8033 8.85693 15.9258 9.02488 15.9258H15.7913C15.6396 16.3862 15.2308 16.7169 14.7516 16.7169H8.64805C8.58449 16.7169 8.52205 16.7346 8.46677 16.7683L5.70715 18.452C5.361 18.6627 5.1459 19.0587 5.1459 19.4854C5.1459 20.1396 5.64059 20.6719 6.24859 20.6719Z"
                                          fill="white"/>
                                </svg>
                            </div>
                            <span>Купить</span>
                        </a>
                    </form>
                    <?
                    $iblockid = $arItem['IBLOCK_ID'];
                    $id = $arItem['ID'];
                    if (isset($_SESSION["CATALOG_COMPARE_LIST"][$iblockid]["ITEMS"][$id])) {
                        $checked = 'compare_yes';
                        $text = 'Удалить из сравнение';
                    } else {
                        $checked = '';
                        $text = 'Сравнить';
                    }
                    $compareTovParam = $arItem['ID'];
                    $compareTovIcon = SITE_TEMPLATE_PATH . "/assets/img/icons/compare-active.svg";
                    $compareImgAttr = "";
                    $compareTooltip = "+  в сравнение";
                    $compareExtraFavorites = '';

                    $curPage = $APPLICATION->GetCurPage(false);
                    if ($curPage == '/catalog/compare/') {
                        $compareTovParam = $arItem['ID'] . ", 'N', 'Y'";
                        $compareTovIcon = SITE_TEMPLATE_PATH . "/assets/img/icons/notification/notification_delete-red.svg";
                        $compareImgAttr = ' height="22" class="mt-2"';
                        $compareTooltip = "удалить";
                        $compareExtraFavorites = ' <div class="add_to_fav_compare"><a href="#!" class="card__button add_to_fav_main ' . $active1 . '" data-id="' . $arItem['ID'] . '"  data-act="' . $act . '"><i class="icon-like"></i></a></div>';
                    }
                    if ($curPage == '/favorites/') {
                        $compareExtraFavorites = ' <div class="add_to_fav_compare"><a href="#!" class="card__button add_to_fav_main favpage ' . $active1 . '" data-id="' . $arItem['ID'] . '"  data-act="' . $act . '"><i class="icon-like"></i></a></div>';
                    }
                    ?>
                    <a onclick="compare_tov(<?= $compareTovParam; ?>);"
                       class="card__button compare_btn compareid_<?= $arItem['ID']; ?>  <?= $checked; ?>">
                        <img src="<?= $compareTovIcon ?>" alt="" <?= $compareImgAttr ?>>
                        <span class="card__button__hint compare-button"><?= $compareTooltip ?></span>
                    </a>
                </div>
            </div>
            <div class="mobile-card-footer">
                <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/basket/put_offers_in_basket.php" method="POST"
                      class="add_to_cart_form js-canb-hide"
                      <? if (!$isAvailable) : ?>style="visibility:hidden"<? endif; ?>>
                    <input type="hidden" name="product_id" value="<?= $id_e ? $id_e : $arItem['ID'] ?>">
                    <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                           data-id="<?= $arItem['ID'] ?>">
                    <a href="#" class="mobile-buy-button 111card__buy add_to_cart" data-name="<?= $arItem['NAME'] ?>"
                       data-image="<?= $src ?>">
                        Купить
                    </a>
                </form>
                <div class="mobile-favorite"><i class="icon-like"></i></div>
                <a onclick="compare_tov(<?= $compareTovParam; ?>);"
                   class="card__button compare_btn compareid_<?= $arItem['ID']; ?>  <?= $checked; ?>">
                    <img src="<?= $compareTovIcon ?>" alt="" class="mt-1">
                </a>
            </div>
            <div class="overflow-action-block">
                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/menu-icons/sub-close-icon.svg"
                     class="action-block-close"/>
                <div class="action-wrap">
                    <?
                    $frame = new \Bitrix\Main\Page\FrameBuffered('overflow__card__fav__' . $product_id);
                    $frame->begin();
                    ?>
                    <a href="#!" class="card__button add_to_fav_main <?= $active1 ?>" data-id="<?= $arItem['ID'] ?>"
                       data-act="<?= $act ?>">
                        <i class="icon-like"></i> Добавить товар в избранное
                        <span class="card__button__hint favorite-button"><?= $text ?></span>
                    </a>
                    <?
                    $frame->beginStub();
                    ?>
                    <a href="#!" class="card__button add_to_fav_main" >
                        <i class="icon-like"></i> Добавить товар в избранное
                        <span class="card__button__hint favorite-button"></span>
                    </a>
                    <?
                    $frame->end();
                    ?>
                    <div class="action-buttons-separator"></div>
                    <a onclick="compare_tov(<?= $arItem['ID']; ?>);"
                       class="card__button compare_btn compareid_<?= $arItem['ID']; ?>  <?= $checked; ?>">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/compare-active.svg" alt="">
                        Добавить товар в сравнение
                        <span class="card__button__hint compare-button">+  в сравнение</span>
                    </a>
                </div>
                <a href="#">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/action-block-banner.svg"
                         class="action-block-banner"/>
                </a>
            </div>
        </div>
        <?= $compareExtraFavorites; ?>
    </div>
    <?
}

function sw_product_cat_cart($arItem, $class = "", $isRecomendate = false)
{ ?>
    <?

    /*  $VALUES = array();
        $res = CIBlockElement::GetProperty(4, $arItem["ID"], "sort", "asc", array("CODE" => "MORE_IMAGES"));
        while ($ob = $res->GetNext())
        {
            if($ob['VALUE'] > 0) {
                $VALUES[] = $ob['VALUE'];
            }
        }
    */

    $res = CCatalogSKU::getOffersList(
        $arItem["ID"], // массив ID товаров
        4, // указываете ID инфоблока только в том случае, когда ВЕСЬ массив товаров из одного инфоблока и он известен
        array('ACTIVE' => 'Y'), // дополнительный фильтр предложений. по умолчанию пуст.
        array('NAME', 'PREVIEW_PICTURE'),  // массив полей предложений. даже если пуст - вернет ID и IBLOCK_ID
        array() /* свойства предложений. имеет 2 ключа:
                               ID - массив ID свойств предложений
                                      либо
                               CODE - массив символьных кодов свойств предложений
                                     если указаны оба ключа, приоритет имеет ID*/
    );

    $VALUES = array();
    global $APPLICATION;
    if ($APPLICATION->GetCurPage() == "/rasprodazha/") {

        if (isset($res[$arItem["ID"]]) && count($res[$arItem["ID"]]) > 0) {
            $res = CIBlockElement::GetProperty(16, array_key_first($res[$arItem["ID"]]), "sort", "asc", array("CODE" => "MORE_PHOTO"));
            while ($ob = $res->GetNext()) {
                if ($ob['VALUE'] > 0) {
                    $VALUES[] = $ob['VALUE'];
                }
            }
        } else {
            $res = CIBlockElement::GetProperty(4, $arItem["ID"], "sort", "asc", array("CODE" => "MORE_IMAGES"));
            while ($ob = $res->GetNext()) {
                if ($ob['VALUE'] > 0) {
                    $VALUES[] = $ob['VALUE'];
                }
            }
        }
    } else {
        foreach ($res[$arItem["ID"]] as $offer) {
            if ($offer["PREVIEW_PICTURE"]) {
                $VALUES[] = $offer["PREVIEW_PICTURE"];
            } else {
                //$VALUES[] = 0;
            }
        }
    }

    $selected = $arItem["OFFERS_SELECTED_ID"];

    $product_id = $arItem['ID'];
    $ar_res = CCatalogProduct::GetByIDEx($product_id);

    if (!empty($arItem['ITEM_ID']))
        $product_id = $arItem['ITEM_ID'];
    if (!empty($arItem['PRODUCT_ID']))
        $product_id = $arItem['PRODUCT_ID'];

    $arImg = [];

    $img2 = $arItem['PREVIEW_PICTURE'];
    if (is_array($img2))
        $img2 = $arItem['PREVIEW_PICTURE']['ID'];

    $file = CFile::GetPath($img2);
    if (!empty($file)) {
        //        $src = $file;
        $src = $img2;
        //        $arImg[] = $file;
        $arImg[] = $img2;
    } else {
        //      $arImg[] = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }

    foreach ($VALUES as $val) {
        if ($val != 0) {
            //          $arImg[] = CFile::GetPath($val);
            $arImg[] = $val;
        } else {
            //          $arImg[] = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
        }
    }

    if (count($VALUES) > 0) {
        unset($arImg[0]);
    }

    $sum = 0;
    CModule::IncludeModule('iblock');
    $arSelect_STARS = array(); //"ID", "NAME", "PREVIEW_TEXT" , "PREVIEW_PICTURE"
    $arFilter_STARS = array("IBLOCK_ID" => 2, "ACTIVE" => "Y", 'PROPERTY_ID' => $arItem['ID']);
    $res_STARS = CIBlockElement::GetList(array("DATE_ACTIVE_FROM" => "DESC"), $arFilter_STARS, false, array(), $arSelect_STARS);
    $total_STARS = $res_STARS->SelectedRowsCount();
    while ($ob_STARS = $res_STARS->GetNextElement()) {
        $arProps_STARS = $ob_STARS->GetProperties();
        $sum += (int)$arProps_STARS['P1']['VALUE'];
    }
    if (!empty($total_STARS)) {
        $res_ev = round($sum / $total_STARS);
    }


    $prop_p1 = $arItem['PROPERTIES']['P1']['VALUE'];
    if (empty($prop_p1)) {
        $prop_p1 = $arItem["PROPERTY_P1_VALUE"];
    }

    $prop_p100 = $arItem['PROPERTIES']['P100']['VALUE'];
    if (empty($prop_p100)) {
        $prop_p100 = $arItem["PROPERTY_P100_VALUE"];
    }

    $prop_p200 = $arItem['PROPERTIES']['P200']['VALUE'];
    if (empty($prop_p200)) {
        $prop_p200 = $arItem["PROPERTY_P200_VALUE"];
    }
    $prop_p300 = $arItem['PROPERTIES']['SPEC']['VALUE'];
    if (empty($prop_p300)) {
        $prop_p300 = $arItem["PROPERTY_SPEC_VALUE"];
    }
    $prop_p400 = $arItem['PROPERTIES']['P400']['VALUE'];
    if (empty($prop_p400)) {
        $prop_p400 = $arItem["PROPERTY_P400_VALUE"];
    }

    $prop_p500 = $arItem['PROPERTIES']['PROP_GIFT']['VALUE'];
    if (empty($prop_p500)) {
        $prop_p500 = $arItem["PROPERTY_PROP_GIFT_VALUE"];
    }

    $prop_gift = !empty($arItem['PROPERTIES']['PROP_GIFT']['VALUE']);

    $arOffer = CCatalogSKU::getOffersList(
        $arItem['ID'], // массив ID товаров
        $iblockID = 4, // указываете ID инфоблока только в том случае, когда ВЕСЬ массив товаров из одного инфоблока и он известен
        $skuFilter = array(), // дополнительный фильтр предложений. по умолчанию пуст.
        $fields = array(),  // массив полей предложений. даже если пуст - вернет ID и IBLOCK_ID
        $propertyFilter = array() /* свойства предложений. имеет 2 ключа:
                               ID - массив ID свойств предложений
                                      либо
                               CODE - массив символьных кодов свойств предложений
                                     если указаны оба ключа, приоритет имеет ID*/
    );

    foreach ($arOffer as $res) {
        foreach ($res as $res___SKU) {
            $id3[] = $res___SKU['ID'];
        }
    }

    if (!empty($id3)) {
        $arSelect_news = array();
        $arFilter_news = array("IBLOCK_ID" => 16, "ACTIVE" => "Y", 'ID' => $id3);
        $arOffer = CIBlockElement::GetList(array("CATALOG_QUANTITY" => "DESC"), $arFilter_news, false, array(), $arSelect_news);
        //        print_r($arOffer);
        $total_news = $arOffer->SelectedRowsCount();
        if ($total_news > 0) {
            while ($ar_offers = $arOffer->GetNextElement()) {
                $offer = $ar_offers->GetFields();

                $id_e = $offer['ID'];
            }
        }
    }

    $res_tttt = CIBlockElement::GetByID($arItem["ID"]);
    if ($ar_res_tttt = $res_tttt->GetNext())
        $arItem['DETAIL_PAGE_URL'] = $ar_res_tttt["DETAIL_PAGE_URL"];

    $db_res_ozon_price = CPrice::GetList(
        array(),
        array(
            "PRODUCT_ID" => $product_id,  // Получаем ID Товара
            "CATALOG_GROUP_ID" => 15
        )
    );
    if ($ar_res_ozon = $db_res_ozon_price->Fetch()) {
        $priceOZONE = $ar_res_ozon["PRICE"];
    }
    $db_res_ozon_old = CPrice::GetList(
        array(),
        array(
            "PRODUCT_ID" => $product_id,  // Получаем ID Товара
            "CATALOG_GROUP_ID" => 16
        )
    );
    if ($ar_res_old = $db_res_ozon_old->Fetch()) {
        $priceOZONE_OLD = $ar_res_old["PRICE"];
    }
    $res = CIBlockSection::GetByID($arItem["IBLOCK_SECTION_ID"]);
    if ($ar_res = $res->GetNext()) {
        $parentSectionId = $ar_res['IBLOCK_SECTION_ID'];
    }
    ?>
    <div class="card" style="height: 100%">
        <div class="card__labels 333">
            <? $price = CCatalogProduct::GetOptimalPrice($arItem["ID"], 1, 'N'); ?>
            <? if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]): ?>
                <div class="card__label discount">
                    -<?= round(($price['RESULT_PRICE']["BASE_PRICE"] - $price['RESULT_PRICE']['DISCOUNT_PRICE']) * 100 / $price['RESULT_PRICE']["BASE_PRICE"]) ?>
                    %
                </div>
            <? endif; ?>
            <? if ($prop_p300[0] == 'Новинка'): ?>
                <div class="card__label new">New</div>
            <? endif; ?>
            <? if ($prop_p300 == 'Новинка'): ?>
                <div class="card__label new">New</div>
            <? endif; ?>
            <? if ($prop_p200 == 'Y'): ?>
                <div class="card__label top">Это Хит!</div>
            <? endif; ?>
            <? if ($prop_p100 == 'Да'): ?>
                <div class="card__label sale">Супер-скидка!</div>
            <? endif; ?>
            <? if ($prop_p400 == 'Y'): ?>
                <div class="card__label recom">Спасимбо рекомендует</div>
            <? endif; ?>
            <? if ($prop_p500 == 'Да'): ?>
                <div class="card__label product-with-gift">
                    <img src="<?= SITE_TEMPLATE_PATH; ?>/img/label-with-gift.svg" alt="">
                    ТОВАР С ПОДАРКОМ
                </div>
            <? endif; ?>
            <? if ($prop_p600 == 'Y'): ?>
                <div class="card__label super-sale">
                    Супер-скидка!
                    <span>%</span>
                </div>
            <? endif; ?>
        </div>
        <?
        if ($prop_gift) {
            ?>
            <div class="card__gift"></div>
            <?
        }
        ?>
        <?php /* ?><a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="card__photo">
            <img src="<?=resizeImageByWidth($src, 300); //cloudImage($src, 300);?>"
                    alt="<?=$arItem['NAME']?>">
        </a><?php */ ?>
        <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="slidel-discount-mob" data-cntimg="<?php echo COUNT($arImg) > 5 ? 5 : COUNT($arImg); ?>"
           data-curslide="1">
            <?php $ii = 0; ?>
            <? foreach ($arImg as $Image): ?>
                <div class="slidel-discount-mob__item">
                    <img src="<?= makeWebp(resizeImageByWidth($Image, 300)); ?>"
                         alt="<?= $arItem["NAME"]; ?>">
                </div>
                <?php
                $ii++;
                if ($ii == 5) break;
                ?>
            <? endforeach; ?>
        </a>
        <div class="card__content">
            <? if (!empty($prop_p1)) : ?>
                <div class="card__vendor">Артикул: <?= $prop_p1 ?></div>
            <? else : ?>
            <? endif; ?>
            <div class="mobile-price-block" <? if ($arItem["CATALOG_AVAILABLE"] == 'N') {
                echo ' style="visibility:hidden"';
            } ?>>
                <? if ($parentSectionId == "752" || (strpos($arItem['NAME'], 'Коляска') !== false)): ?>
                    <? if (!empty($priceOZONE) && $priceOZONE != 0) { ?>
                        <div class="new-price">
                            <?= number_format($priceOZONE, 0, '.', ' ') ?> руб<br>
                        </div>
                        <? if ($priceOZONE_OLD): ?>
                            <div class="old-price"><?= number_format($priceOZONE_OLD, 0, '.', ' ') ?> руб</div>
                        <? endif; ?>
                    <? } else { ?>
                        <div class="new-price">
                            <?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб
                        </div>
                        <?php if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                            <div class="old-price"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?>
                                руб
                            </div>
                        <?php } ?>
                    <? } ?>
                <? else: ?>
                    <div class="new-price">
                        <?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб
                    </div>
                    <?php if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                        <div class="old-price"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?>руб
                        </div>
                    <?php } ?>
                <? endif; ?>
            </div>
            <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="slider-super-discount__title"><?= $arItem['NAME'] ?></a>
            <div class="card__info-small">
                <!--                js-aval-block-->
                <? if ($arItem["CATALOG_AVAILABLE"] == 'N') { ?>
                    <h5 class="good__title js-aval-block good__title-new good__title-out-stock">
                        <span class="avaible-icon">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0)">
                                <path d="M7.32166 5.99953L11.8083 1.5127C11.9317 1.38918 11.9998 1.2244 12 1.0487C12 0.872892 11.9319 0.707916 11.8083 0.584599L11.4151 0.191526C11.2915 0.067721 11.1267 -8.39233e-05 10.9508 -8.39233e-05C10.7752 -8.39233e-05 10.6104 0.067721 10.4868 0.191526L6.0002 4.67806L1.51337 0.191526C1.38995 0.067721 1.22507 -8.39233e-05 1.04927 -8.39233e-05C0.873659 -8.39233e-05 0.70878 0.067721 0.585366 0.191526L0.192 0.584599C-0.064 0.840599 -0.064 1.25699 0.192 1.5127L4.67873 5.99953L0.192 10.4862C0.0684878 10.6099 0.000487805 10.7746 0.000487805 10.9504C0.000487805 11.1261 0.0684878 11.2908 0.192 11.4145L0.585268 11.8075C0.708683 11.9312 0.873658 11.9991 1.04917 11.9991C1.22498 11.9991 1.38985 11.9312 1.51327 11.8075L6.0001 7.32089L10.4867 11.8075C10.6103 11.9312 10.7751 11.9991 10.9507 11.9991H10.9509C11.1266 11.9991 11.2914 11.9312 11.415 11.8075L11.8082 11.4145C11.9316 11.2909 11.9997 11.1261 11.9997 10.9504C11.9997 10.7746 11.9316 10.6099 11.8082 10.4863L7.32166 5.99953Z"
                                      fill="#FE7865"/>
                                </g>
                                <defs>
                                <clipPath id="clip0">
                                <rect width="12" height="12" fill="white"/>
                                </clipPath>
                                </defs>
                                </svg>
                        </span>
                        Нет в наличии
                    </h5>
                <? } else { ?>
                    <h5 class="good__title js-aval-block good__title-new good__title-in-stock">
                        <spaan class="avaible-icon">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0)">
                                    <path d="M5.36085 12.2299C5.22721 12.3644 5.04489 12.4394 4.85549 12.4394C4.66609 12.4394 4.48376 12.3644 4.35012 12.2299L0.314135 8.19331C-0.104712 7.77447 -0.104712 7.09528 0.314135 6.67722L0.819501 6.17172C1.23848 5.75288 1.91688 5.75288 2.33573 6.17172L4.85549 8.69161L11.6642 1.88273C12.0832 1.46388 12.7623 1.46388 13.1805 1.88273L13.6858 2.38823C14.1047 2.80707 14.1047 3.48613 13.6858 3.90432L5.36085 12.2299Z"
                                          fill="#23A420"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0">
                                        <rect width="14" height="14" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                        </spaan>
                        В наличии
                    </h5>
                <? } ?>
                <div class="slider-super-discount__info-small">
                    <div class="colors-val-mob"><? if (!empty($id3)) {
                            echo "<span>" . count($id3) . "</span> цветов";
                        } ?>
                    </div>
                    <div class="right">
                        <? if (intval($res_ev)): ?>
                            <div class="slider-super-discount__rating">
                                <div class="r-star">
                                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M14.9609 5.75398C14.8627 5.45024 14.5933 5.23452 14.2746 5.20579L9.94517 4.81268L8.2332 0.805654C8.10697 0.511991 7.81949 0.321899 7.50008 0.321899C7.18066 0.321899 6.89318 0.511991 6.76695 0.80634L5.05498 4.81268L0.724886 5.20579C0.406732 5.2352 0.138017 5.45024 0.0392524 5.75398C-0.0595127 6.05771 0.0316991 6.39086 0.272375 6.60086L3.5449 9.47088L2.57991 13.7217C2.50929 14.0342 2.6306 14.3573 2.88993 14.5448C3.02933 14.6455 3.19241 14.6967 3.35687 14.6967C3.49866 14.6967 3.63931 14.6585 3.76554 14.583L7.50008 12.351L11.2332 14.583C11.5064 14.7473 11.8508 14.7323 12.1095 14.5448C12.369 14.3567 12.4902 14.0335 12.4196 13.7217L11.4546 9.47088L14.7271 6.60143C14.9678 6.39086 15.0597 6.05828 14.9609 5.75398Z"
                                            fill="#FE7865"/>
                                    </svg>
                                </div>
                                <?= $res_ev; ?>
                            </div>
                        <? endif; ?>
                        <? if ($total_STARS != 0): ?>
                            <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="slider-super-discount__comments">
                                <i>
                                    <img src="<?= SITE_TEMPLATE_PATH; ?>/img/n-chat.svg" alt="">
                                </i>
                                <?= $total_STARS ?>
                            </a>
                        <? endif; ?>
                    </div>
                </div>
            </div>
            <div class="card__review"></div>
            <div class="sku_section">
                <?
                $arOffer = CCatalogSKU::getOffersList(
                    $arItem['ID'], // массив ID товаров
                    $iblockID = 4, // указываете ID инфоблока только в том случае, когда ВЕСЬ массив товаров из одного инфоблока и он известен
                    $skuFilter = array(), // дополнительный фильтр предложений. по умолчанию пуст.
                    $fields = array(),  // массив полей предложений. даже если пуст - вернет ID и IBLOCK_ID
                    $propertyFilter = array() /* свойства предложений. имеет 2 ключа:
                               ID - массив ID свойств предложений
                                      либо
                               CODE - массив символьных кодов свойств предложений
                                     если указаны оба ключа, приоритет имеет ID*/
                );

                foreach ($arOffer as $res) {
                    foreach ($res as $res___SKU) {
                        $id3[] = $res___SKU['ID'];
                    }
                }

                if (!empty($id3)) {
                    $sfirt = true;
                    if (!empty($_GET['offer']) && !$isRecomendate) {
                        $sfirt = false;
                    }

                    $arSelect_news = array();
                    $arFilter_news = array("IBLOCK_ID" => 16, "ACTIVE" => "Y", 'ID' => $id3);
                    $arOffer = CIBlockElement::GetList(array("NAME" => "ASC"), $arFilter_news, false, array(), $arSelect_news);
                    $total_news = $arOffer->SelectedRowsCount();
                    $classIsSetted = false;
                    if ($total_news > 0) { ?>
                        <div class="good__info__block">
                            <div class="good__info__block">
                                <h5 class="good__title good__title-new-color ">Цвет</h5>
                                <div class="hide-tablet js-show-hide-colors">
                                    <span class="icon">
                                        <svg width="18" height="11" viewBox="0 0 18 11" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
<path d="M9.00371 8.15717L2.15915 1.01027L2.15914 1.01026C1.85968 0.697621 1.37139 0.697621 1.07192 1.01026L1.07189 1.01029C0.77604 1.31936 0.776029 1.81774 1.0719 2.12677L1.07192 2.12679L8.4601 9.84132L8.46011 9.84134C8.75957 10.1539 9.24785 10.1539 9.54731 9.84134L9.54733 9.84132L16.9355 2.12679L16.9355 2.12681L16.9373 2.12489C17.2279 1.81068 17.2198 1.31223 16.9185 1.0084L16.9185 1.0084C16.6213 0.708644 16.1473 0.708632 15.8501 1.0084L15.8501 1.00839L15.8483 1.01027L9.00371 8.15717Z"
      fill="black" stroke="#090909" stroke-width="0.3"/>
</svg>
                                    </span>
                                </div>
                            </div>
                            <div class="good__colors js-show-hide-colors--target">
                                <?
                                $addedActiveClass = false;
                                $i = 0;
                                while ($ar_offers = $arOffer->GetNextElement()) {
                                    $offer = $ar_offers->GetFields();
                                    $offer['PROPERTIES'] = $ar_offers->GetProperties();

                                    $id_e = $offer['ID'];

                                    $price = CCatalogProduct::GetOptimalPrice($offer["ID"], 1, 'N');
                                    $ar_res = CCatalogProduct::GetByID($offer['ID']);

                                    $file_small_big = CFile::GetPath($offer['PREVIEW_PICTURE']);
                                    $file_small_big_g = CFile::GetPath($offer['PREVIEW_PICTURE']);
                                    $file_small = CFile::GetPath($offer['PROPERTIES']['IMG']['VALUE']);

                                    foreach ($offer['PROPERTIES']['MORE_PHOTO']['VALUE'] as &$itmof) {
                                        $itmof = CFile::GetPath($itmof);
                                    }

                                    if ($selected == $id_e) {
                                        $pricertop = $price;
                                    }

                                    $haveColorFilter = false;
                                    $colorsArr = [];
                                    global $arrFilter;
                                    if (isset($arrFilter['OFFERS']['PROPERTY_1131'])) {
                                        $haveColorFilter = true;
                                        $colorsArr = $arrFilter['OFFERS']['PROPERTY_1131'];
                                    } elseif (isset($arrFilter['OFFERS']['=PROPERTY_1131'])) {
                                        $haveColorFilter = true;
                                        $colorsArr = $arrFilter['OFFERS']['=PROPERTY_1131'];
                                    }

                                    //                                    $addedActiveClass = !$addedActiveClass && ;

                                    if (!$addedActiveClass && (!$haveColorFilter && $selected == $id_e || (!$haveColorFilter && $sfirt && $ar_res["AVAILABLE"] == 'Y') || ($haveColorFilter && in_array($offer['PROPERTIES']['TSVET']['VALUE_ENUM_ID'], $colorsArr)))) {
                                        $addedActiveClass = true;
                                        $sfirt = false;
                                    }

                                    if (($total_news - 1) == $i && !$addedActiveClass) {
                                        $addedActiveClass = true;
                                    }
                                    if ($arItem["PROPERTIES"]["ROZNICHNOE_NAIMENOVANIE_SVOYSTVO"]["VALUE"] && $offer['PROPERTIES']['TSVET']['VALUE']) {
                                        $offer["NAME"] = $arItem["PROPERTIES"]["ROZNICHNOE_NAIMENOVANIE_SVOYSTVO"]["VALUE"] . ' ' . $offer['PROPERTIES']['TSVET']['VALUE'];
                                    } elseif ($arItem["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"] && $offer['PROPERTIES']['TSVET']['VALUE']) {
                                        $offer["NAME"] = $arItem["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"] . ' ' . $offer['PROPERTIES']['TSVET']['VALUE'];
                                    }
                                    ?>
                                    <button class="good__color change_element sect-item <? if (!$classIsSetted && $addedActiveClass) echo ' click_this_do active '; ?> <? if (empty($file_small)) : ?>  <?= $offer['PROPERTIES']['CODE']['VALUE'] ?> <? endif ?>"
                                            data-test="<?= $sfirt; ?>"
                                            data-id="<?= $offer['ID'] ?>"
                                            data-img="<?= resizeImageByWidth($offer['PREVIEW_PICTURE'], 300); /*cloudImage($file_small_big, 300);*/ ?>"
                                            data-name="<?= $offer['NAME'] ?>"
                                        <?
                                        $ofrImgs = [];
                                        foreach ($offer['PROPERTIES']['MORE_PHOTO']['~VALUE'] as $ofId) {
                                            $ofrImgs[] = makeWebp(resizeImageByWidth($ofId, 300));
                                        }
                                        if (!empty($offer['PREVIEW_PICTURE'])) {
                                            array_unshift($ofrImgs, makeWebp(resizeImageByWidth($offer['PREVIEW_PICTURE'], 300)));
                                        }
                                        ?>
                                            data-imgs='<?= json_encode($ofrImgs) ?>'
                                        <? /*data-imgs='<?=json_encode($offer['PROPERTIES']['MORE_PHOTO']['VALUE'])?>' */ ?>
                                            data-price='<?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб'
                                            data-src='<?= $arItem['DETAIL_PAGE_URL'] ?>?offer=<?= $offer['ID'] ?>'
                                            data-aval='<?= $ar_res["AVAILABLE"] ?>'
                                    >
                                        <? if (!empty($file_small)) : ?>
                                            <img src="<?= resizeImageByWidth($offer['PROPERTIES']['IMG']['VALUE'], 35); /* cloudImage($file_small, 35);*/ ?>">
                                        <? elseif (!empty($file_small_big_g)) : ?>
                                            <img src="<?= resizeImageByWidth($offer['PREVIEW_PICTURE'], 35);/* cloudImage($file_small_big_g, 35);*/ ?>">
                                        <? else: ?>
                                            <br>
                                        <? endif ?>
                                    </button>
                                    <?
                                    if ($ar_res["AVAILABLE"] == 'Y') {
                                        $sfirt = false;
                                    }

                                    if ($addedActiveClass) {
                                        $classIsSetted = true;
                                    }

                                    $i++;
                                } ?>
                            </div>
                        </div>
                    <? }
                } ?>
            </div>
            <div class="card__footer">
                <div class="card__price js-canb-hide">
                    <? if ($parentSectionId == "752" || (strpos($arItem['NAME'], 'Коляска') !== false)): ?>
                        <? if (!empty($priceOZONE) && $priceOZONE != 0) { ?>
                            <?= number_format($priceOZONE, 0, '.', ' ') ?> руб<br>
                            <? if ($priceOZONE_OLD): ?>
                                <span class="card__price_old"><?= number_format($priceOZONE_OLD, 0, '.', ' ') ?> руб</span>
                            <? endif; ?>
                        <? } else { ?>
                            <?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб
                            <? if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                                <span class="card__price_old"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб</span>
                            <? } ?>
                        <? } ?>
                    <? else: ?>
                        <?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб
                        <? if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                            <span class="card__price_old"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб</span>
                        <? } ?>
                    <? endif; ?>
                </div>

                <div class="card__footer__buttons">
                    <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/basket/put_offers_in_basket.php" method="POST"
                          class="add_to_cart_form js-canb-hide" <? if ($arItem["CATALOG_AVAILABLE"] == 'N') {
                        echo ' style="visibility:hidden"';
                    } ?>>
                        <input type="hidden" name="product_id" value="<?= $id_e ? $id_e : $arItem['ID'] ?>">
                        <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                               data-id="<?= $arItem['ID'] ?>">
                        <a href="#" class="card__buy add_to_cart" data-name="<?= $arItem['NAME'] ?>"
                           data-image="<?= $src ?>">
                            <div class="icon-cart-new">
                                <svg width="26" height="27" viewBox="0 0 26 27" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.45403 23.0449C7.44063 23.0449 6.61621 23.932 6.61621 25.0225C6.61621 26.1129 7.44063 27 8.45403 27C9.46743 27 10.2919 26.1129 10.2919 25.0225C10.2919 23.932 9.46743 23.0449 8.45403 23.0449ZM8.45403 26.209C7.84603 26.209 7.35134 25.6767 7.35134 25.0225C7.35134 24.3682 7.84603 23.8359 8.45403 23.8359C9.06203 23.8359 9.55673 24.3682 9.55673 25.0225C9.55673 25.6767 9.06203 26.209 8.45403 26.209Z"
                                          fill="white"/>
                                    <path d="M8.45399 25.418C8.65699 25.418 8.82156 25.2409 8.82156 25.0225C8.82156 24.804 8.65699 24.627 8.45399 24.627C8.25099 24.627 8.08643 24.804 8.08643 25.0225C8.08643 25.2409 8.25099 25.418 8.45399 25.418Z"
                                          fill="white"/>
                                    <path d="M21.0492 25.418C21.2522 25.418 21.4168 25.2409 21.4168 25.0225C21.4168 24.804 21.2522 24.627 21.0492 24.627C20.8462 24.627 20.6816 24.804 20.6816 25.0225C20.6816 25.2409 20.8462 25.418 21.0492 25.418Z"
                                          fill="white"/>
                                    <path d="M21.0492 23.0449C20.0358 23.0449 19.2114 23.932 19.2114 25.0225C19.2114 26.1129 20.0358 27 21.0492 27C22.0626 27 22.8871 26.1129 22.8871 25.0225C22.8871 23.932 22.0626 23.0449 21.0492 23.0449ZM21.0492 26.209C20.4412 26.209 19.9466 25.6767 19.9466 25.0225C19.9466 24.3682 20.4412 23.8359 21.0492 23.8359C21.6572 23.8359 22.1519 24.3682 22.1519 25.0225C22.1519 25.6767 21.6572 26.209 21.0492 26.209Z"
                                          fill="white"/>
                                    <path d="M6.06952 19.1402L8.74499 17.5078H14.7516C15.6391 17.5078 16.3817 16.8273 16.5525 15.9258H20.3806C21.5569 15.9258 22.5819 15.0694 22.8733 13.8431L25.0809 4.55409C25.109 4.4357 25.0847 4.31003 25.0151 4.21353C24.9455 4.11703 24.8383 4.06055 24.7248 4.06055H6.67801L6.49055 3.2719C6.0333 1.34546 4.42234 0 2.57295 0H1.10269C0.494693 0 0 0.532301 0 1.18652C0 1.84075 0.494693 2.37305 1.10269 2.37305H2.57295C3.41379 2.37305 4.14608 2.98424 4.35382 3.85947L7.17691 15.7366L4.98231 17.0755C4.17636 17.5665 3.67564 18.4899 3.67564 19.4854C3.67564 21.0119 4.82989 22.2539 6.24859 22.2539H22.5194C23.1274 22.2539 23.6221 21.7216 23.6221 21.0674C23.6221 20.4132 23.1274 19.8809 22.5194 19.8809H6.24859C6.04589 19.8809 5.88103 19.7035 5.88103 19.4854C5.88103 19.3432 5.95317 19.211 6.06952 19.1402ZM19.6938 11.4434H17.435L17.5728 8.54297H20.1073L19.6938 11.4434ZM20.851 8.54297H23.3742L22.6848 11.4434H20.4375L20.851 8.54297ZM19.5809 12.2344L19.1674 15.1348H17.2596L17.3974 12.2344H19.5809ZM16.6989 11.4434H14.4363L14.2984 8.54297H16.8367L16.6989 11.4434ZM17.6104 7.75195L17.7482 4.85156H20.6336L20.2201 7.75195H17.6104ZM16.8743 7.75195H14.2608L14.1229 4.85156H17.0121L16.8743 7.75195ZM13.5247 7.75195H12.0698C11.8667 7.75195 11.7022 7.92898 11.7022 8.14746C11.7022 8.36594 11.8667 8.54297 12.0698 8.54297H13.5623L13.7002 11.4434H11.4415L10.5014 4.85156H13.3867L13.5247 7.75195ZM10.6978 11.4434H8.43281L7.7434 8.54297H10.2841L10.6978 11.4434ZM10.8106 12.2344L11.2243 15.1348H9.31026L8.62085 12.2344H10.8106ZM11.968 15.1348L11.5543 12.2344H13.7378L13.8757 15.1348H11.968ZM14.6119 15.1348L14.4739 12.2344H16.6613L16.5235 15.1348H14.6119ZM22.1611 13.6472C21.953 14.523 21.2208 15.1348 20.3806 15.1348H19.9111L20.3246 12.2344H22.4968L22.1611 13.6472ZM23.5621 7.75195H20.9638L21.3773 4.85156H24.2515L23.5621 7.75195ZM9.75766 4.85156L10.1713 7.75195H7.55546L6.86605 4.85156H9.75766ZM2.57295 1.58203H1.10269C0.899994 1.58203 0.735129 1.40463 0.735129 1.18652C0.735129 0.968414 0.899994 0.791016 1.10269 0.791016H2.57295C3.34557 0.791016 4.06693 1.07847 4.63807 1.56906L4.11403 2.133C3.68064 1.78458 3.14488 1.58203 2.57295 1.58203ZM6.24859 20.6719H22.5194C22.7221 20.6719 22.887 20.8493 22.887 21.0674C22.887 21.2855 22.7221 21.4629 22.5194 21.4629H6.24859C5.23519 21.4629 4.41077 20.5758 4.41077 19.4854C4.41077 18.7743 4.76858 18.1147 5.34473 17.7637L7.78393 16.2755C7.92822 16.1874 7.99982 16.0064 7.9587 15.8334L5.06602 3.66362C4.9806 3.30365 4.83146 2.97596 4.6341 2.69214L5.1583 2.1281C5.44436 2.51316 5.65931 2.96626 5.77831 3.46776L8.66878 15.6283C8.71039 15.8033 8.85693 15.9258 9.02488 15.9258H15.7913C15.6396 16.3862 15.2308 16.7169 14.7516 16.7169H8.64805C8.58449 16.7169 8.52205 16.7346 8.46677 16.7683L5.70715 18.452C5.361 18.6627 5.1459 19.0587 5.1459 19.4854C5.1459 20.1396 5.64059 20.6719 6.24859 20.6719Z"
                                          fill="white"/>
                                </svg>
                            </div>
                            <span>Купить</span>
                        </a>
                    </form>
                    <?
                    $iblockid = $arItem['IBLOCK_ID'];
                    $id = $arItem['ID'];
                    if (isset($_SESSION["CATALOG_COMPARE_LIST"][$iblockid]["ITEMS"][$id])) {
                        $checked = 'compare_yes';
                        $text = 'Удалить из сравнение';
                    } else {
                        $checked = '';
                        $text = 'Сравнить';
                    }
                    ?>
                    <a onclick="compare_tov(<?= $arItem['ID']; ?>);"
                       class="card__button compare_btn compareid_<?= $arItem['ID']; ?>  <?= $checked; ?>">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/compare-active.svg" alt="">
                        <span class="card__button__hint compare-button">+  в сравнение</span>
                    </a>
                </div>
            </div>
            <div class="mobile-card-footer">
                <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/basket/put_offers_in_basket.php" method="POST"
                      class="add_to_cart_form js-canb-hide" <? if ($arItem["CATALOG_AVAILABLE"] == 'N') {
                    echo ' style="visibility:hidden"';
                } ?>>
                    <input type="hidden" name="product_id" value="<?= $id_e ? $id_e : $arItem['ID'] ?>">
                    <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                           data-id="<?= $arItem['ID'] ?>">
                    <a href="#" class="mobile-buy-button 111card__buy add_to_cart add_to_cart_new"
                       data-name="<?= $arItem['NAME'] ?>"
                       data-image="<?= $src ?>">
                        Купить
                    </a>
                </form>
                <div class="mobile-favorite"><i class="icon-like"></i></div>
                <a onclick="compare_tov(<?= $arItem['ID']; ?>);"
                   class="card__button compare_btn compareid_<?= $arItem['ID']; ?>  <?= $checked; ?>">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/compare<? if (!empty($checked)) echo "-active"; ?>.svg"
                         alt="">
                </a>
            </div>
            <div class="overflow-action-block">
                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/menu-icons/sub-close-icon.svg"
                     class="action-block-close"/>
                <div class="action-wrap">
                    <a href="#!" class="card__button add_to_fav_main <?= $active1 ?>" data-id="<?= $arItem['ID'] ?>"
                       data-act="<?= $act ?>">
                        <i class="icon-like"></i> Добавить товар в избранное
                        <span class="card__button__hint favorite-button"><?= $text ?></span>
                    </a>
                    <div class="action-buttons-separator"></div>
                    <a onclick="compare_tov(<?= $arItem['ID']; ?>);"
                       class="card__button compare_btn compareid_<?= $arItem['ID']; ?>  <?= $checked; ?>">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/compare-active.svg" alt="">
                        Добавить товар в сравнение
                        <span class="card__button__hint compare-button">+  в сравнение</span>
                    </a>
                </div>
                <a href="#">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/action-block-banner.svg"
                         class="action-block-banner"/>
                </a>
            </div>
        </div>
    </div>
<? }

function sw_product_cat_list($arItem, $class = "")
{ ?>
    <?
    $product_id = $arItem['ID'];
    if (!empty($arItem['ITEM_ID']))
        $product_id = $arItem['ITEM_ID'];
    if (!empty($arItem['PRODUCT_ID']))
        $product_id = $arItem['PRODUCT_ID'];

    $img2 = $arItem['PREVIEW_PICTURE'];
    if (is_array($img2))
        $img2 = $arItem['PREVIEW_PICTURE']['ID'];

    $file = CFile::ResizeImageGet($img2, array(
        'width' => 370,
        'height' => 504
    ), BX_RESIZE_IMAGE_PROPORTIONAL, true);
    if (!empty($file)) {
        $src = $file["src"];
    } else {
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }

    $sum = 0;
    CModule::IncludeModule('iblock');
    $arSelect_STARS = array(); //"ID", "NAME", "PREVIEW_TEXT" , "PREVIEW_PICTURE"
    $arFilter_STARS = array("IBLOCK_ID" => 2, "ACTIVE" => "Y", 'PROPERTY_ID' => $arItem['ID']);
    $res_STARS = CIBlockElement::GetList(array("DATE_ACTIVE_FROM" => "DESC"), $arFilter_STARS, false, array(), $arSelect_STARS);
    $total_STARS = $res_STARS->SelectedRowsCount();
    while ($ob_STARS = $res_STARS->GetNextElement()) {
        $arProps_STARS = $ob_STARS->GetProperties();
        $sum += $arProps_STARS['P1']['VALUE'];
    }
    if (!empty($total_STARS)) {
        $res_ev = round($sum / $total_STARS);
    }

    $prop_p1 = $arItem['PROPERTIES']['P1']['VALUE'];
    if (empty($prop_p1)) {
        $prop_p1 = $arItem["PROPERTY_P1_VALUE"];
    }
    $prop_p100 = $arItem['PROPERTIES']['P100']['VALUE'];
    if (empty($prop_p100)) {
        $prop_p100 = $arItem["PROPERTY_P100_VALUE"];
    }
    $prop_p200 = $arItem['PROPERTIES']['P200']['VALUE'];
    if (empty($prop_p200)) {
        $prop_p200 = $arItem["PROPERTY_P200_VALUE"];
    }

    $prop_p300 = $arItem['PROPERTIES']['SPEC']['VALUE'];
    if (empty($prop_p300)) {
        $prop_p300 = $arItem["PROPERTY_SPEC_VALUE"];
    }

    $prop_gift = !empty($arItem['PROPERTIES']['PROP_GIFT']['VALUE']);

    $arOffer = CCatalogSKU::getOffersList(
        $arItem['ID'], // массив ID товаров
        $iblockID = 4, // указываете ID инфоблока только в том случае, когда ВЕСЬ массив товаров из одного инфоблока и он известен
        $skuFilter = array(), // дополнительный фильтр предложений. по умолчанию пуст.
        $fields = array(),  // массив полей предложений. даже если пуст - вернет ID и IBLOCK_ID
        $propertyFilter = array() /* свойства предложений. имеет 2 ключа:
                               ID - массив ID свойств предложений
                                      либо
                               CODE - массив символьных кодов свойств предложений
                                     если указаны оба ключа, приоритет имеет ID*/
    );

    foreach ($arOffer as $res) {
        foreach ($res as $res___SKU) {
            $id3[] = $res___SKU['ID'];
        }
    }

    if (!empty($id3)) {
        $arSelect_news = array();
        $arFilter_news = array("IBLOCK_ID" => 16, "ACTIVE" => "Y", 'ID' => $id3);
        $arOffer = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter_news, false, array(), $arSelect_news);
        $total_news = $arOffer->SelectedRowsCount();
        if ($total_news > 0) {
            while ($ar_offers = $arOffer->GetNextElement()) {
                $offer = $ar_offers->GetFields();
                $id_e = $offer['ID'];
            }
        }
    }

    $res_tttt = CIBlockElement::GetByID($arItem["ID"]);
    if ($ar_res_tttt = $res_tttt->GetNext())
        $arItem['DETAIL_PAGE_URL'] = $ar_res_tttt["DETAIL_PAGE_URL"];
    ?>
    <div class="card-list">
        <div class="card__labels">
            <? $price = CCatalogProduct::GetOptimalPrice($arItem["ID"], 1, 'N'); ?>
            <? if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]): ?>
                <div class="card__label discount">
                    -<?= round(($price['RESULT_PRICE']["BASE_PRICE"] - $price['RESULT_PRICE']['DISCOUNT_PRICE']) * 100 / $price['RESULT_PRICE']["BASE_PRICE"]) ?>
                    %
                </div>
            <? endif; ?>
            <?
            if ($prop_p300[0] == 'Новинка') { ?>
                <div class="card__label new">New</div>
                <?
            } ?>
            <?
            if ($prop_p300 == 'Новинка') { ?>
                <div class="card__label new">New</div>
                <?
            } ?>
            <? if ($prop_p200 == 'Y') { ?>
                <div class="card__label top">Top</div>
                <?
            } ?>
        </div>
        <?
        if ($prop_gift) {
            ?>
            <div class="card__gift"></div>
            <?
        }
        ?>
        <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="card__photo">
            <img src="<?= $src ?>"
                 alt="<?= $arItem['NAME'] ?>">
        </a>
        <div class="card-list__info">
            <? if (!empty($prop_p1)) : ?>
                <div class="card__vendor">Артикул: <?= $prop_p1 ?></div>
            <? else : ?>
                <br>
            <? endif; ?>
            <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="card__title"><?= $arItem['NAME'] ?></a>
            <div class="card__review">
                <? if (($total_STARS != 0)) { ?>
                    <div class="card__rating rating">
                        <? for ($i = 0; $i < $res_ev; $i++) { ?>
                            <div class="star full"><i class="icon-star"></i></div>
                        <? } ?>
                    </div>
                    <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="card__comments"><?= $total_STARS ?></a>
                <? } else { ?>
                    <br>
                    <?
                } ?>
            </div>
        </div>

        <div class="card-list__price">
            <?
            $db_res_ozon_price = CPrice::GetList(
                array(),
                array(
                    "PRODUCT_ID" => $product_id,  // Получаем ID Товара
                    "CATALOG_GROUP_ID" => 15
                )
            );
            if ($ar_res_ozon = $db_res_ozon_price->Fetch()) {
                $priceOZONE = $ar_res_ozon["PRICE"];
            }
            $db_res_ozon_old = CPrice::GetList(
                array(),
                array(
                    "PRODUCT_ID" => $product_id,  // Получаем ID Товара
                    "CATALOG_GROUP_ID" => 16
                )
            );
            if ($ar_res_old = $db_res_ozon_old->Fetch()) {
                $priceOZONE_OLD = $ar_res_old["PRICE"];
            }
            $res = CIBlockSection::GetByID($arItem["IBLOCK_SECTION_ID"]);
            if ($ar_res = $res->GetNext()) {
                $parentSectionId = $ar_res['IBLOCK_SECTION_ID'];
            }
            ?>
            <? if ($parentSectionId == "752" || (str_contains($arItem['NAME'], 'Коляска'))): ?>
            <div class="card__price_block">
                <? if (!empty($priceOZONE) && $priceOZONE != 0) { ?>
                    <div class="card__price"> <?= number_format($priceOZONE, 0, '.', ' ') ?> руб</div>
                    <? if ($priceOZONE_OLD): ?>
                        <span class="card__price_old"><?= number_format($priceOZONE_OLD, 0, '.', ' ') ?> руб</span>
                    <? endif; ?>
                <? } else { ?>
                    <div class="card__price"><?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?>руб </div>
                    <?php if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                        <span class="card__price_old"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб</span>
                    <?php } ?>

                <? } ?>
                <? else: ?>
                    <div class="card__price"><?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> руб </div>
                    <?php if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                        <span class="card__price_old"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб</span>
                    <?php } ?>
                <? endif; ?>
            </div>

        </div>
        <div class="card-list__buttons">
            <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/basket/put_offers_in_basket.php" method="POST"
                  class="add_to_cart_form">
                <input type="hidden" name="product_id" value="<?= $id_e ? $id_e : $arItem['ID'] ?>">
                <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                       data-id="<?= $arItem['ID'] ?>">
                <a href="#" class="button add_to_cart" data-name="<?= $arItem['NAME'] ?>" data-image="<?= $src ?>">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/shopping-bag.svg" alt="">
                    <span>Купить</span>
                </a>
            </form>
            <?php
            $active1 = '';
            $act = 'add';
            $text = 'В избранное';
            global $APPLICATION;
            $arElements = unserialize($APPLICATION->get_cookie('bo_favorites'));
            if ($arElements[$arItem['ID']]) {
                $active1 = ' active';
                $act = 'del';
                $text = 'В избранном';
            }
            ?>
            <a href="#!" class="card-list__button add_to_fav_main <?= $active1 ?>" data-id="<?= $arItem['ID'] ?>"
               data-act="<?= $act ?>">
                <i class="icon-like"></i>
                <span>+  в избранное</span>
            </a>
            <?
            $iblockid = $arItem['IBLOCK_ID'];
            $id = $arItem['ID'];
            if (isset($_SESSION["CATALOG_COMPARE_LIST"][$iblockid]["ITEMS"][$id])) {
                $checked = 'compare_yes';
                $text = 'Удалить из сравнение';
            } else {
                $checked = '';
                $text = 'Сравнить';
            }
            ?>
            <a href="#!" onclick="compare_tov(<?= $arItem['ID']; ?>);"
               class="card-list__button compare_btn compareid_<?= $arItem['ID']; ?>  <?= $checked; ?>">
                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/compare-active.svg" alt="">
                <span>+  в сравнение</span>
            </a>
        </div>
    </div>
<? }

function sw_news__item($arItem, $class = "")
{
    $img2 = $arItem['PREVIEW_PICTURE'];
    if (is_array($img2))
        $img2 = $arItem['PREVIEW_PICTURE']['ID'];

    if ($class) {
        $file = CFile::ResizeImageGet($img2, array(
            'width' => 770,
            'height' => 550
        ), BX_RESIZE_IMAGE_PROPORTIONAL, true);
    } else {
        $file = CFile::ResizeImageGet($img2, array(
            'width' => 370,
            'height' => 265
        ), BX_RESIZE_IMAGE_PROPORTIONAL, true);
    }

    if (!empty($file)) {
        $src = $file["src"];
    } else {
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }

    $date_active_from = date("j", strtotime($arItem["DATE_ACTIVE_FROM"]));
    $date_active_from_month = date("n", strtotime($arItem["DATE_ACTIVE_FROM"]));
    $date_active_from_year = date("Y", strtotime($arItem["DATE_ACTIVE_FROM"]));

    $str_month = array(
        1 => "Января",
        2 => "Февраля",
        3 => "Марта",
        4 => "Апреля",
        5 => "Мая",
        6 => "Июня",
        7 => "Июля",
        8 => "Августа",
        9 => "Сентября",
        10 => "Октября",
        11 => "Ноября",
        12 => "Декабря"
    );
    ?>
    <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="news__item__photo">
        <img src="<?= $src ?>"
             alt="<?= $arItem['NAME'] ?>">
    </a>
    <div class="news__item__content">
        <div class="news__item__date"><?= $date_active_from ?> <?= $str_month[$date_active_from_month] ?> <?= $date_active_from_year ?></div>
        <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="news__item__title"><?= $arItem['NAME'] ?></a>
        <div class="news__item__text"><?= $arItem['PREVIEW_TEXT'] ?> </div>
        <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="link-button">Подробнее</a>
    </div>
<? }

function sw_article_item($arItem, $class = "")
{
    $img2 = $arItem['PREVIEW_PICTURE'];
    if (is_array($img2))
        $img2 = $arItem['PREVIEW_PICTURE']['ID'];

    $file = CFile::ResizeImageGet($img2, array(
        'width' => 280,
        'height' => 300
    ), BX_RESIZE_IMAGE_PROPORTIONAL, true);

    if (!empty($file)) {
        $src = $file["src"];
    } else {
        $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
    }
    ?>
    <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="article-item">
        <div class="article-item__photo">
            <img src="<?= resizeImageByWidth($src, 300); /*cloudImage($src, 300);*/ ?>" alt="<?= $arItem['NAME'] ?>">
        </div>
        <div class="article-item__title"><?= $arItem['NAME'] ?></div>
    </a>
<? }
/*end*/
