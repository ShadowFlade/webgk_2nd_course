<?

//шаблон компонента catalog.element

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponent $component */

CJSCore::Init(array("jquery"));

Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/assets/css/swiper-bundle.min.css');
Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/assets/css/phpdev.css");

use \Bitrix\Conversion\Internals\MobileDetect;

$detect = new MobileDetect;

$this->setFrameMode(true);

?>
<div class="row" itemscope itemtype="http://schema.org/Product">
    <div class="col-xs-12 good_left-col">
        <div class="good__info__buttons good__info__buttons-after-cart">
            <?php
            $active1 = '';
            $act = 'add';
            $text = '+  в избранное';
            global $APPLICATION;
            $arElements = unserialize($APPLICATION->get_cookie('bo_favorites'));
            if ($arElements[$arResult['ID']]) {
                $active1 = ' active';
                $act = 'del';
                $text = 'В избранном';
            }
            ?>
            <a href="#!" class="card-list__button add_to_fav_main <?= $active1 ?>" data-id="<?= $arResult['ID'] ?>"
               data-act="<?= $act ?>">
                <i class="icon-like"></i>
                <span><?= $text ?></span>
            </a>

            <?php
            $iblockid = $arResult['IBLOCK_ID'];
            $id = $arResult['ID'];
            if (isset($_SESSION["CATALOG_COMPARE_LIST"][$iblockid]["ITEMS"][$id])) {
                $checked = 'compare_yes';
                $text = 'Удалить из сравнения';
            } else {
                $checked = '';
                $text = 'Сравнить';
            }
            ?>

            <a href="/catalog/compare/"
               class="card-list__button compare_btn compareid_<?= $arResult['ID']; ?>  <?= $checked; ?> compare_link hidden">
                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/compare-active.svg" loading="lazy" alt=""
                     class="compare_mob_icon_<?= $arResult['ID']; ?>">
                <span>→ к сравнению</span>
            </a>
            <a onclick="compare_tov(<?= $arResult['ID']; ?>);"
               class="card-list__button compare_btn compareid_<?= $arResult['ID']; ?> <?= $checked; ?> compare_add_del hidden">
                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/compare<? if (!empty($checked)) echo "-active"; ?>.svg"
                     loading="lazy" alt="" class="compare_mob_icon_<?= $arResult['ID']; ?>">
                <span>+  в сравнение</span>
            </a>

        </div>

        <div class="gallery-slide">
            <div class="swiper js-gallery gallery-main">
                <div class="swiper-wrapper">
                    <? foreach ($arResult['PROPERTIES']['MORE_IMAGES']['VALUE'] as $key_img => $imageID): ?>
                        <div class="swiper-slide">
                            <div class="swiper-zoom-container">
                                <a href="<?= makeWebp(resizeImageByWidth($imageID, 1000)); ?>" data-fancybox="gallery"
                                   class="swiper-zoom-target">
                                    <img src="<?= makeWebp(resizeImageByWidth($imageID, 600)); ?>"/>
                                </a>
                            </div>
                        </div>
                    <? endforeach; ?>
                </div>
                <div class="swiper-scrollbar"></div>
            </div>
            <div class="gallery-thumbs-wrapper asd">
                <div thumbsSlider="" class="swiper js-gallery-thumbs gallery-thumbs">
                    <div class="swiper-wrapper">
                        <?
                        foreach ($arResult['PROPERTIES']['MORE_IMAGES']['VALUE'] as $key_img => $imageID):
                            /*$file_small_bbb = CFile::GetPath($arResult['PROPERTIES']['MORE_IMAGES']['VALUE'][0]);
                            $file_small = CFile::GetPath($imageID);
                            $file_big = CFile::GetPath($imageID);*/
                            ?>
                            <div class="swiper-slide">
                                <div><img src="<?= resizeImageByWidth($imageID, 120); ?>"></div>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
                <div class="swiper-button-next">
                    <div class="icon">
                        <svg width="25" height="30" viewBox="0 0 25 30" fill="" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24.6553 18.3621C25.131 17.8627 25.1122 17.0709 24.6122 16.5952C24.1122 16.119 23.3209 16.139 22.8447 16.6383L13.7502 26.2272V1.25002C13.7502 0.560008 13.1902 0 12.5002 0C11.8102 0 11.2501 0.560008 11.2501 1.25002V26.1872L2.15503 16.6383C1.67877 16.1383 0.88751 16.119 0.387503 16.5952C0.129999 16.8408 0 17.1702 0 17.5002C0 17.8102 0.114374 18.1202 0.345003 18.3621L10.7326 29.2679C11.2045 29.7404 11.832 30.0004 12.5002 30.0004C13.1683 30.0004 13.7958 29.7404 14.2889 29.246L24.6553 18.3621Z"
                                  fill=""></path>
                        </svg>
                    </div>
                </div>
                <div class="swiper-button-prev">
                    <div class="icon">
                        <svg width="25" height="30" viewBox="0 0 25 30" fill="" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24.6553 18.3621C25.131 17.8627 25.1122 17.0709 24.6122 16.5952C24.1122 16.119 23.3209 16.139 22.8447 16.6383L13.7502 26.2272V1.25002C13.7502 0.560008 13.1902 0 12.5002 0C11.8102 0 11.2501 0.560008 11.2501 1.25002V26.1872L2.15503 16.6383C1.67877 16.1383 0.88751 16.119 0.387503 16.5952C0.129999 16.8408 0 17.1702 0 17.5002C0 17.8102 0.114374 18.1202 0.345003 18.3621L10.7326 29.2679C11.2045 29.7404 11.832 30.0004 12.5002 30.0004C13.1683 30.0004 13.7958 29.7404 14.2889 29.246L24.6553 18.3621Z"
                                  fill=""></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="left_count">
            <span class="left_count--img">
                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24.01 5.51618L12.659 0.0364014C12.5584 -0.0121338 12.4412 -0.0121338 12.3406 0.0364014L0.989575 5.51618C0.863013 5.57727 0.782593 5.70544 0.782593 5.84597V19.154C0.782593 19.2945 0.863013 19.4227 0.989575 19.4838L12.3406 24.9636C12.3908 24.9879 12.4453 25 12.4998 25C12.5542 25 12.6087 24.9879 12.659 24.9636L24.01 19.4838C24.1365 19.4227 24.217 19.2945 24.217 19.154V5.84602C24.217 5.70539 24.1365 5.57732 24.01 5.51618ZM12.4998 0.772876L23.0084 5.84597L19.9622 7.31657C19.9429 7.30188 19.9225 7.2883 19.9002 7.27751L9.46291 2.23894L12.4998 0.772876ZM8.63586 2.653L19.1292 7.71872L16.9801 8.75627L6.49104 3.6926L8.63586 2.653ZM19.3747 8.41345V12.2487L17.3673 13.2178V9.38259L19.3747 8.41345ZM23.4845 18.9242L12.866 24.0503V11.5556L15.3989 10.3328C15.581 10.2449 15.6573 10.026 15.5694 9.84382C15.4815 9.66174 15.2626 9.58527 15.0804 9.67326L12.4998 10.9191L11.4844 10.4289C11.3022 10.3408 11.0833 10.4173 10.9953 10.5994C10.9074 10.7816 10.9838 11.0005 11.1659 11.0884L12.1336 11.5556V24.0503L1.51501 18.9241V6.42942L9.60095 10.333C9.65227 10.3578 9.70652 10.3695 9.75989 10.3695C9.89602 10.3695 10.0268 10.2932 10.0899 10.1624C10.1779 9.98029 10.1015 9.76135 9.91936 9.67341L1.99114 5.84597L5.62908 4.08972L16.6298 9.40041C16.6314 9.40266 16.6332 9.40466 16.6348 9.40686V13.8013C16.6348 13.9274 16.6996 14.0445 16.8064 14.1115C16.8656 14.1487 16.9333 14.1675 17.0011 14.1675C17.0554 14.1675 17.1098 14.1555 17.1603 14.1311L19.9002 12.8084C20.0267 12.7473 20.1072 12.6192 20.1072 12.4786V8.05993L23.4845 6.42946V18.9242Z"
                          fill="white"></path>
                </svg>
            </span>
            <p><span>Осталось :</span> <b class="element_count"></b> шт.</p>
        </div>

        <?
        $rating = \Webgk\Catalog\Reviews::get([$arResult["ID"]]);
        ?>

        <div class="card__review">

            <?$APPLICATION->IncludeComponent(
                'bitrix:iblock.vote',
                'origami_stars',
                [
                    'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID'])
                        ? $arParams['CUSTOM_SITE_ID']
                        : null,
                    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                    'ELEMENT_ID' => $arResult['ID'],
                    'ELEMENT_CODE' => '',
                    'MAX_VOTE' => '5',
                    'VOTE_NAMES' => [
                        '1',
                        '2',
                        '3',
                        '4',
                        '5',
                    ],
                    'SET_STATUS_404' => 'N',
                    'DISPLAY_AS_RATING' => $arParams['VOTE_DISPLAY_AS_RATING'],
                    'CACHE_TYPE' => 'A',
                    'CACHE_TIME' => $arParams['CACHE_TIME'],
                ],
                $component,
                ['HIDE_ICONS' => 'Y']
            );?>
            <p>
                <? if ($rating['count'] != 0): ?>
                    <span>(<?= $rating['count'] ?>) отзыв</span>
                <? endif; ?>
                <? if ($arResult["PROPERTIES"]["PVIDEO"]["VALUE"]): ?>
                    <span>(1) видео</span>
                <? endif; ?>
                <? if (!empty($arResult['MORE_PHOTO_COUNT'])): ?>
                    <span>(<?= $arResult['MORE_PHOTO_COUNT'] ?>) фото</span>
                <? endif; ?>
            </p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/swiper.js"></script>

    <div class="col-xs-12 good_right-col">
        <h1 class="product__title is-desktop name_el" itemprop="name"><?= $arResult['NAME'] ?></h1>

        <div class="good__info__block__top show-on-mobile">
            <h5 class="good__title">Цвет:
                <span class="color"></span><? /* небесно-голубой*/ ?></h5>
            <h5 class="good__title good__title-new good__title-in-stock ">

                <div class="avaible-icon js-canb-hide">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                </div>

                <span class="js-aval-block">В наличии</span>
            </h5>
        </div>

        <div class="row">
            <div class="col-md-6 col-xs-12 col-right-30 good_right-col-left">
                <div class="good__info good__info_first">

                    <?
                    $arOffer = CCatalogSKU::getOffersList(
                        $arResult['ID'], // массив ID товаров
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
                        if (!empty($_GET['offer'])) {
                            $sfirt = false;
                        }


                        $arSelect_news = array();
                        $arFilter_news = array("IBLOCK_ID" => 16, "ACTIVE" => "Y", 'ID' => $id3);
                        $arOffer = CIBlockElement::GetList(
                            array(
                                $arParams["OFFERS_SORT_FIELD"] => $arParams["OFFERS_SORT_ORDER"],
                                $arParams["OFFERS_SORT_FIELD2"] => $arParams["OFFERS_SORT_ORDER2"]
                            ), $arFilter_news, false, array(), $arSelect_news
                        );
                        $total_news = $arOffer->SelectedRowsCount();
                    if ($total_news > 0) { ?>
                        <div class="good__info__block">

                            <div class="good__info__block__top">
                                <h5 class="good__title">Цвет:
                                    <span class="color"></span><? /* небесно-голубой*/ ?></h5>
                                <h5 class="good__title good__title-new good__title-in-stock ">

                                    <div class="avaible-icon js-canb-hide">
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
                                    </div>

                                    <span class="js-aval-block">В наличии</span>
                                </h5>
                            </div>
                            <div class="good__colors swiper-container js-colors-slider">
                                <div class="swiper-wrapper">
                                    <?
                                    $itColor = false;
                                    $itName = false;
                                    $itPrice = false;

                                    while ($ar_offers = $arOffer->GetNextElement()) {
                                        $offer = $ar_offers->GetFields();
                                        $offer['PROPERTIES'] = $ar_offers->GetProperties();

                                        $id_e = $offer['ID'];

                                        $price = CCatalogProduct::GetOptimalPrice($offer["ID"], 1, 'N');
                                        $ar_res = CCatalogProduct::GetByID($offer['ID']);

                                        $file_small_big = CFile::GetPath($offer['PREVIEW_PICTURE']);
                                        $file_small_big_g = CFile::GetPath($offer['PREVIEW_PICTURE']);
                                        $file_small = CFile::GetPath($offer['PROPERTIES']['IMG']['VALUE']);

                                        $morePhThumbs = [];
                                        foreach ($offer['PROPERTIES']['MORE_PHOTO']['VALUE'] as &$itmof) {
//                                            $itmof = CFile::GetPath($itmof);
                                            $morePhThumbs[] = resizeImageByWidth($itmof, 120);
                                            $itmof = makeWebp(resizeImageByWidth($itmof, 1000));
                                        }


                                        if ($sfirt) {
                                            $pricertop = $price;
                                        }

                                        ?>

                                        <?
                                        $discountPrice = number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') . " Р";
                                        if ($price["RESULT_PRICE"]["DISCOUNT"] != 0) {
                                            $discountPrice = number_format($price['RESULT_PRICE']["BASE_PRICE"], 0, '.', ' ') . " Р";
                                        }
                                        ?>
                                        <?
                                        if ($arResult["PROPERTIES"]["ROZNICHNOE_NAIMENOVANIE_SVOYSTVO"]["VALUE"] && $offer['PROPERTIES']['TSVET']['VALUE']) {
                                            $offer["NAME"] = $arResult["PROPERTIES"]["ROZNICHNOE_NAIMENOVANIE_SVOYSTVO"]["VALUE"] . ' ' . $offer['PROPERTIES']['TSVET']['VALUE'];
                                        }


                                        if (!$itColor) $itColor = $offer['PROPERTIES']['TSVET']['VALUE'];
                                        if (!$itName) $itName = $offer['NAME'];
                                        if ($offer['PROPERTIES']['MORE_PHOTO']['VALUE'] == false) {
                                            $offer['PROPERTIES']['MORE_PHOTO']['VALUE'][] = makeWebp(resizeImageByWidth($offer['PREVIEW_PICTURE'], 600));
                                        }


                                        $src = $offer['PROPERTIES']['MORE_PHOTO']['VALUE'];
                                        if (!empty($offer['PREVIEW_PICTURE'])) {
                                            array_unshift($src, makeWebp(resizeImageByWidth($offer['PREVIEW_PICTURE'], 600)));
                                            array_unshift($morePhThumbs, resizeImageByWidth($offer['PREVIEW_PICTURE'], 120));
                                        }
                                        ?>
                                        <div class="swiper-slide">
                                            <button class="good__color change_element product-page-button <? if ($sfirt || (!empty($_GET['offer']) && $_GET['offer'] == $offer['ID'])) {
                                                $aval = $ar_res["AVAILABLE"];
                                                echo ' click_this_do ';
                                            } ?><? if (empty($file_small)) : ?>  <?= $offer['PROPERTIES']['CODE']['VALUE'] ?> <? endif ?>"
                                                    data-id="<?= $offer['ID'] ?>"
                                                    data-color="<?= $offer['PROPERTIES']['TSVET']['VALUE'] ?>"
                                                    data-img="<?= makeWebp(resizeImageByWidth($offer['PREVIEW_PICTURE'], 600)); ?>"
                                                    data-name="<?= $offer['NAME'] ?>"
                                                    data-quantity="<?= $offer['CATALOG_QUANTITY']; ?>"
                                                    data-imgs='<?= json_encode($src) ?>'
                                                    data-thumbs='<?= json_encode($morePhThumbs) ?>'
                                                    data-price='<?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?> Р'
                                                    data-full='<?= $discountPrice; ?>'
                                                    data-percent='<?= $price['RESULT_PRICE']["PERCENT"]; ?>'
                                                    data-aval='<?= $ar_res["AVAILABLE"] ?>'
                                            >
                                                <? if (!empty($file_small)) : ?>
                                                    <img src="<?= resizeImageByWidth($offer['PROPERTIES']['IMG']['VALUE'], 35); ?>"
                                                         loading="lazy">
                                                <? elseif (!empty($file_small_big_g)) : ?>
                                                    <img src="<?= resizeImageByWidth($offer['PREVIEW_PICTURE'], 35); ?>"
                                                         loading="lazy">
                                                <? else: ?>
                                                    <img src="<?= DEFAULT_IMAGE ?>" loading="lazy">
                                                <? endif ?>
                                            </button>
                                        </div>
                                        <?
                                        $sfirt = false;
                                    }
                                    ?>
                                </div>
                                <div class="nav-video show-small-mob is-mob">
                                    <div class="nav-video__prev">
                                        <svg width="71" height="17" viewBox="0 0 71 17" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.0902 0.814399L10.8781 1.09282L11.0902 0.8144C11.2461 0.933166 11.35 1.1122 11.35 1.31722C11.35 1.52224 11.2461 1.70128 11.0902 1.82005L11.0902 1.82006L2.58345 8.29968L11.0894 14.7799C11.0894 14.7799 11.0894 14.7799 11.0894 14.7799C11.2453 14.8987 11.3491 15.0777 11.3491 15.2827C11.3491 15.4878 11.2453 15.6668 11.0894 15.7856C10.9373 15.9014 10.7525 15.95 10.5827 15.95C10.4128 15.95 10.2281 15.9014 10.076 15.7856L0.90958 8.80279L0.908164 8.8017C0.754448 8.68336 0.65 8.50513 0.65 8.29965C0.65 8.0943 0.754377 7.91514 0.910962 7.79671C0.911227 7.79651 0.911493 7.79631 0.911758 7.79611L10.0768 0.81437L10.0769 0.814322C10.229 0.698516 10.4137 0.64997 10.5836 0.649976C10.7534 0.649981 10.9381 0.698539 11.0902 0.814399Z"
                                                  fill="#E5E5E5" stroke="#E5E5E5" stroke-width="0.7"/>
                                            <line x1="70" y1="8.29633" x2="2" y2="8.29633" stroke="#E5E5E5"
                                                  stroke-width="2" stroke-linecap="square" stroke-linejoin="round"/>
                                        </svg>

                                    </div>
                                    <div class="nav-video__next">
                                        <svg width="73" height="17" viewBox="0 0 73 17" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M61.9098 0.814399L62.1219 1.09282L61.9098 0.8144C61.7539 0.933166 61.65 1.1122 61.65 1.31722C61.65 1.52224 61.7539 1.70128 61.9098 1.82005L61.9098 1.82006L70.4166 8.29968L61.9106 14.7799C61.9106 14.7799 61.9106 14.7799 61.9106 14.7799C61.7547 14.8987 61.6509 15.0777 61.6509 15.2827C61.6509 15.4878 61.7547 15.6668 61.9106 15.7856C62.0627 15.9014 62.2475 15.95 62.4173 15.95C62.5872 15.95 62.7719 15.9014 62.924 15.7856L72.0904 8.80279L72.0918 8.8017C72.2456 8.68336 72.35 8.50513 72.35 8.29965C72.35 8.0943 72.2456 7.91514 72.089 7.79671C72.0888 7.79651 72.0885 7.79631 72.0882 7.79611L62.9232 0.81437L62.9231 0.814322C62.771 0.698516 62.5863 0.64997 62.4164 0.649976C62.2466 0.649981 62.0619 0.698539 61.9098 0.814399Z"
                                                  fill="#E5E5E5" stroke="#E5E5E5" stroke-width="0.7"/>
                                            <line x1="69" y1="8.29633" x2="1" y2="8.29633" stroke="#E5E5E5"
                                                  stroke-width="2" stroke-linecap="square" stroke-linejoin="round"/>
                                        </svg>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <? }
                    }else{
                    if ($arResult["CATALOG_AVAILABLE"] == 'N'){
                    ?>
                        <script>
                            document.querySelector('.js-aval-block').innerHTML = 'Нет в наличии';
                            document.querySelector('.js-aval-block').style.color = 'red';
                        </script>
                    <?
                    }else{
                    ?>
                        <script>
                            document.querySelector('.js-aval-block').innerHTML = 'В наличии';
                            document.querySelector('.js-aval-block').style.color = 'green';
                        </script>
                        <?
                    }
                    } ?>

                    <meta itemprop="color" content="<?= $itColor; ?>">
                    <? if (!empty($arResult['PREVIEW_TEXT'])) { ?>
                        <div class="good__info__block mb-big is-desktop">
                            <h5 class="good__title">О товаре</h5>
                            <p class="good__info__text" itemprop="description"><?= $arResult['PREVIEW_TEXT'] ?></p>
                            <a href="#good__specs__content" class="icons-full__link">
                                <span class="icon-full__link">ПОДРОБНЕЕ</span>
                            </a>

                        </div>
                    <? } ?>
                    <div class="good__info__flex good__info__flex-between is-desktop brand-rank-block">

                        <? if (!empty($arResult['PROPERTIES']['BRANDS']['VALUE'])) {
                            $res_b = CIBlockElement::GetByID($arResult['PROPERTIES']['BRANDS']['VALUE']);
                            if ($ar_res = $res_b->GetNext())
                                $file = CFile::ResizeImageGet($ar_res['PREVIEW_PICTURE'], array(), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                            ?>
                            <div class="good__info__block good__info__flex-column good__info-flex-1">
                                <h5 class="good__title">Бренд:</h5>
                                <? if ($file['src']) { ?>
                                    <div class="good__info__brand">
                                        <a href="<?= $ar_res['DETAIL_PAGE_URL'] ?>" class="link">
                                            <img src="<?= $file['src'] ?>" loading="lazy" alt="<?= $ar_res['NAME'] ?>">
                                        </a>
                                    </div>
                                <? } ?>
                                <!--                            <div class="good__link">-->
                                <? //=$ar_res['NAME']?><!--</div>-->
                            </div>
                        <? } ?>


                        <?$APPLICATION->IncludeComponent(
                            'bitrix:iblock.vote',
                            'origami_stars',
                            [
                                'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID'])
                                    ? $arParams['CUSTOM_SITE_ID']
                                    : null,
                                'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                                'ELEMENT_ID' => $arResult['ID'],
                                'ELEMENT_CODE' => '',
                                'MAX_VOTE' => '5',
                                'VOTE_NAMES' => [
                                    '1',
                                    '2',
                                    '3',
                                    '4',
                                    '5',
                                ],
                                'SET_STATUS_404' => 'N',
                                'DISPLAY_AS_RATING' => $arParams['VOTE_DISPLAY_AS_RATING'],
                                'CACHE_TYPE' => 'A',
                                'CACHE_TIME' => $arParams['CACHE_TIME'],
                            ],
                            $component,
                            ['HIDE_ICONS' => 'Y']
                        );?>

                    </div>

                    <?
                    CModule::IncludeModule('iblock');
                    $arSelect_ACT = array(); //"ID", "NAME", "PREVIEW_TEXT" , "PREVIEW_PICTURE"
                    $arFilter_ACT = array("IBLOCK_ID" => 13, "ACTIVE" => "Y", 'PROPERTY_46' => $arResult['ID']);
                    $res_ACT = CIBlockElement::GetList(array("DATE_ACTIVE_FROM" => "DESC"), $arFilter_ACT, false, array(), $arSelect_ACT);
                    $arItemACT = $res_ACT->GetNext();
                    if ($arItemACT) {
                        $img2 = $arItemACT['PREVIEW_PICTURE'];
                        if (is_array($img2))
                            $img2 = $arItemACT['PREVIEW_PICTURE']['ID'];

                        $file = makeWebp(CFile::GetPath($img2));

                        if (!empty($file)) {
                            $src = $file;
                        } else {
                            $src = "https://www.hsjaa.com/images/joomlart/demo/default.jpg";
                        }
                        ?>
                        <div class="sales__item__photo" style="display:none;">
                            <a href="<?= $arItemACT['DETAIL_PAGE_URL'] ?>" class="sales__item__title">
                                <img src="<?= makeWebp(resizeImageByWidth($img2, 350)); ?>" loading="lazy"
                                     alt="<?= $arItemACT['NAME'] ?>">
                            </a>
                        </div>
                        <?php
                    }
                    ?>

                </div>
            </div>
            <div class="col-md-6 col-xs-12 col-left-30 good_right-col-right">

                <div class="is-mob">
                    <? if ($arResult["PROPERTIES"]["PVIDEO"]["VALUE"]): ?>
                        <a href="#videoBlockDescr" class="good__video_thumb--orange">
                            <span class="icon">
                            <svg width="35" height="35" viewBox="0 0 35 35" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
<path d="M14.7903 29.3386H14.7704C14.3083 29.3197 13.851 29.2955 13.3894 29.2672C13.2639 29.2594 13.1467 29.2022 13.0634 29.1081C12.9802 29.0139 12.9378 28.8905 12.9455 28.7651C12.9532 28.6397 13.0104 28.5224 13.1046 28.4392C13.1987 28.3559 13.3221 28.3135 13.4475 28.3212C13.903 28.3491 14.3538 28.3728 14.8092 28.3917C14.8713 28.3929 14.9326 28.4064 14.9895 28.4313C15.0464 28.4562 15.0979 28.4921 15.1409 28.5369C15.1839 28.5817 15.2177 28.6345 15.2404 28.6924C15.263 28.7503 15.274 28.812 15.2727 28.8741C15.2715 28.9362 15.258 28.9975 15.2331 29.0544C15.2082 29.1113 15.1723 29.1627 15.1275 29.2058C15.0827 29.2488 15.0299 29.2826 14.972 29.3052C14.9142 29.3279 14.8524 29.3389 14.7903 29.3376V29.3386Z"
      fill="white"/>
<path d="M22.8158 29.1824C22.6904 29.187 22.5683 29.1416 22.4763 29.0561C22.3844 28.9706 22.3302 28.8521 22.3256 28.7267C22.321 28.6013 22.3664 28.4791 22.4519 28.3872C22.5374 28.2953 22.6559 28.2411 22.7813 28.2365C25.8235 28.004 28.8475 27.5756 31.8345 26.9538C31.9253 26.9337 32.0085 26.8879 32.074 26.8218C32.1395 26.7557 32.1846 26.6721 32.2039 26.5811C33.4014 20.5897 33.4014 14.4206 32.2039 8.42931C32.1848 8.33663 32.1392 8.2515 32.0726 8.18431C32.006 8.11711 31.9213 8.07077 31.8288 8.05093C22.377 6.0737 12.6184 6.0737 3.16663 8.05093C3.07576 8.071 2.9926 8.11683 2.92709 8.18292C2.86158 8.24902 2.8165 8.33259 2.79724 8.42364C1.59967 14.415 1.59967 20.5841 2.79724 26.5754C2.81626 26.6681 2.86186 26.7532 2.92847 26.8204C2.99507 26.8876 3.0798 26.9339 3.17231 26.9538C5.48704 27.4343 7.83913 27.8051 10.16 28.0525C10.2823 28.0687 10.3935 28.1321 10.4698 28.2291C10.546 28.3261 10.5813 28.4491 10.5682 28.5718C10.555 28.6945 10.4945 28.8071 10.3994 28.8858C10.3043 28.9645 10.1823 29.0028 10.0593 28.9928C7.70812 28.7416 5.32481 28.3665 2.97555 27.8789C2.70121 27.8214 2.4499 27.6843 2.25305 27.4848C2.0562 27.2853 1.92254 27.0321 1.86879 26.757C0.647576 20.6464 0.647576 14.3545 1.86879 8.24391C1.92257 7.97025 2.05563 7.71844 2.2514 7.51981C2.44717 7.32117 2.69703 7.18448 2.96988 7.12674C12.552 5.12181 22.4453 5.12181 32.0274 7.12674C32.3018 7.18422 32.5531 7.3213 32.7499 7.52085C32.9468 7.72039 33.0805 7.97354 33.1342 8.24864C34.3554 14.3593 34.3554 20.6511 33.1342 26.7617C33.0804 27.0354 32.9474 27.2872 32.7516 27.4858C32.5558 27.6845 32.306 27.8212 32.0331 27.8789C29.0038 28.511 25.9368 28.9464 22.8513 29.1824C22.8395 29.1824 22.8281 29.1824 22.8158 29.1824Z"
      fill="white"/>
<path d="M18.1007 29.3858C17.9752 29.3858 17.8549 29.336 17.7662 29.2473C17.6775 29.1586 17.6277 29.0383 17.6277 28.9129C17.6277 28.7874 17.6775 28.6671 17.7662 28.5784C17.8549 28.4897 17.9752 28.4399 18.1007 28.4399C18.5642 28.4399 19.0272 28.4304 19.4912 28.4167C19.6166 28.4129 19.7385 28.4591 19.8299 28.5451C19.9213 28.631 19.9748 28.7498 19.9786 28.8753C19.9824 29.0007 19.9363 29.1225 19.8503 29.2139C19.7643 29.3053 19.6455 29.3588 19.5201 29.3627C19.0471 29.3764 18.5736 29.3858 18.1007 29.3858Z"
      fill="white"/>
<path d="M15.3598 22.5955C15.0691 22.5947 14.7833 22.5207 14.5287 22.3802C14.2543 22.2334 14.0252 22.0142 13.8663 21.7465C13.7074 21.4789 13.6248 21.1728 13.6273 20.8615V14.1387C13.6273 13.8279 13.711 13.5229 13.8695 13.2557C14.0281 12.9884 14.2557 12.7688 14.5285 12.6199C14.8012 12.471 15.109 12.3982 15.4196 12.4093C15.7301 12.4204 16.032 12.5149 16.2934 12.6829L21.524 16.0443C21.7677 16.2011 21.9681 16.4165 22.1069 16.6709C22.2457 16.9252 22.3185 17.2103 22.3185 17.5001C22.3185 17.7899 22.2457 18.075 22.1069 18.3293C21.9681 18.5837 21.7677 18.7991 21.524 18.9559L16.2934 22.3173C16.0154 22.4978 15.6912 22.5944 15.3598 22.5955ZM15.3598 13.3526C15.2274 13.3528 15.0972 13.3865 14.9814 13.4505C14.857 13.5171 14.7533 13.6164 14.6812 13.7377C14.6092 13.859 14.5717 13.9976 14.5727 14.1387V20.8615C14.5724 21.0025 14.6101 21.141 14.6818 21.2624C14.7535 21.3838 14.8567 21.4837 14.9803 21.5514C15.104 21.6192 15.2436 21.6524 15.3846 21.6475C15.5255 21.6426 15.6625 21.5999 15.7812 21.5237L21.0123 18.1623C21.1228 18.0913 21.2137 17.9937 21.2767 17.8784C21.3396 17.7631 21.3726 17.6338 21.3726 17.5025C21.3726 17.3711 21.3396 17.2419 21.2767 17.1266C21.2137 17.0113 21.1228 16.9137 21.0123 16.8427L15.7816 13.4798C15.6559 13.3975 15.5091 13.3533 15.3588 13.3526H15.3598Z"
      fill="white"/>
</svg>
                            </span>
                            видео о товаре
                        </a>
                    <? endif; ?>
                </div>

                <h1 class="product__title is-mob"><?= $arResult['NAME'] ?></h1>

                <div class="good__info">
                    <!--                            <div class="row js-canb-hide" --><? //=$aval=='N'?' style="display:none"':'' ?>
                    <div class="">
                        <div class="good__info__block good__info__flex good__info__flex-middle">
                            <h5 class="good__title mb-0 width-title good_quanity-title">количество товара:</h5>
                            <div class="input-stepper">
                                <button class="minus minus_catalog" data-id="<?= $arResult['ID'] ?>">-</button>
                                <input type="number" value="1" disabled/>
                                <button class="plus plus_catalog" data-id="<?= $arResult['ID'] ?>">+</button>
                            </div>
                        </div>
                        <div class="good__info__block good__info__flex good_price-block" itemprop="offers" itemscope
                             itemtype="http://schema.org/Offer">
                            <h5 class="good__title mb-0  width-title posr-top-center">цена товара:</h5>
                            <?
                            if (!empty($id3)) {
                                $price = $pricertop;
                            } else {
                                $price = CCatalogProduct::GetOptimalPrice($arResult["ID"], 1, 'N');
                            }

                            $res = CIBlockSection::GetByID($arResult["IBLOCK_SECTION_ID"]);
                            if ($ar_res = $res->GetNext()) {
                                $parentSectionId = $ar_res['IBLOCK_SECTION_ID'];
                            }
                            $db_res_ozon_price = CPrice::GetList(
                                array(),
                                array(
                                    "PRODUCT_ID" => $_GET['offer'],  // Получаем ID Товара
                                    "CATALOG_GROUP_ID" => 15
                                )
                            );
                            if ($ar_res_ozon = $db_res_ozon_price->Fetch()) {
                                $priceOZONE = $ar_res_ozon["PRICE"];
                            }
                            $db_res_ozon_old = CPrice::GetList(
                                array(),
                                array(
                                    "PRODUCT_ID" => $_GET['offer'],  // Получаем ID Товара
                                    "CATALOG_GROUP_ID" => 16
                                )
                            );
                            if ($ar_res_old = $db_res_ozon_old->Fetch()) {
                                $priceOZONE_OLD = $ar_res_old["PRICE"];
                            }
                            ?>
                            <? if ($parentSectionId == "752"): ?>
                                <? if (!empty($priceOZONE) && $priceOZONE != "0") { ?>
                                    <div class="price-block">
                                        <div class="good__price">
                                            <?= number_format($priceOZONE, 0, '.', ' ') ?> руб
                                        </div>
                                        <meta itemprop="price" content="<?= $priceOZONE; ?>">
                                        <meta itemprop="priceCurrency" content="RUB">
                                        <? if ($arResult["CATALOG_AVAILABLE"]): ?>
                                            <meta itemprop="availability" content="в наличии">
                                        <? else: ?>
                                            <meta itemprop="availability" content="нет в наличии">
                                        <? endif; ?>
                                    </div>
                                <? } else { ?>
                                    <div class="price-block">
                                        <?
                                        $priceItemprop = CCatalogProduct::GetOptimalPrice($arResult["ID"], 1, 'N');
                                        ?>
                                        <div class="good__price"><?= number_format($priceItemprop["DISCOUNT_PRICE"], 0, '.', ' ') ?>
                                            руб
                                        </div>
                                        <meta itemprop="price" content="<?= $priceItemprop["DISCOUNT_PRICE"]; ?>">
                                        <meta itemprop="priceCurrency" content="RUB">
                                        <? if ($arResult["CATALOG_AVAILABLE"]): ?>
                                            <meta itemprop="availability" content="в наличии">
                                        <? else: ?>
                                            <meta itemprop="availability" content="нет в наличии">
                                        <? endif; ?>
                                    </div>
                                <? } ?>
                            <? else: ?>
                                <div class="price-block">
                                    <div class="good__price"><?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?>
                                        руб
                                    </div>
                                    <?
                                    $priceItemprop = CCatalogProduct::GetOptimalPrice($arResult["ID"], 1, 'N');
                                    ?>
                                    <meta itemprop="price" content="<?= $priceItemprop["DISCOUNT_PRICE"]; ?>">
                                    <meta itemprop="priceCurrency" content="RUB">
                                    <? if ($arResult["CATALOG_AVAILABLE"]): ?>
                                        <meta itemprop="availability" content="в наличии">
                                    <? else: ?>
                                        <meta itemprop="availability" content="нет в наличии">
                                    <? endif; ?>
                                </div>
                            <? endif; ?>
                        </div>
                        <? if ($parentSectionId == "752"): ?>
                            <? if ($priceOZONE_OLD && $priceOZONE): ?>
                                <? if (!empty($priceOZONE) && $priceOZONE != 0) { ?>
                                    <? if ($priceOZONE_OLD != 0 && !(empty($priceOZONE_OLD))): ?>
                                        <div class="price-block__sale">
                                            <span class="price__gray"><?= number_format($priceOZONE_OLD, 0, '.', ' ') ?> руб</span>
                                            <div class="price__label">
                                                <span class="price__label-text">SALE</span>
                                            </div>
                                        </div>
                                    <? endif; ?>
                                <? } ?>
                            <? endif; ?>
                        <? endif; ?>
                        <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/basket/put_offers_in_basket.php"
                              method="POST" class="add_to_cart_form js-canb-hide">
                            <input type="hidden" name="product_id" class="product_id"
                                   value="<?= $id_e ? $id_e : $arResult['ID'] ?>">
                            <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                                   data-id="<?= $arResult['ID'] ?>">
                            <a href="#"
                               class="button button-buy good__buy add_to_cart<? if (isset($arResult["CATALOG_QUANTITY"]) && $arResult["CATALOG_QUANTITY"] <= 0): ?> disabled<? endif; ?>"
                               data-name="<?= $arResult['NAME'] ?>" data-image="<?= $src ?>">
                                    <span class="icon"><svg width="27" height="27" viewBox="0 0 27 27" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.09668 23.0449C8.00624 23.0449 7.11914 23.932 7.11914 25.0225C7.11914 26.1129 8.00624 27 9.09668 27C10.1871 27 11.0742 26.1129 11.0742 25.0225C11.0742 23.932 10.1871 23.0449 9.09668 23.0449ZM9.09668 26.209C8.44246 26.209 7.91016 25.6767 7.91016 25.0225C7.91016 24.3682 8.44246 23.8359 9.09668 23.8359C9.7509 23.8359 10.2832 24.3682 10.2832 25.0225C10.2832 25.6767 9.7509 26.209 9.09668 26.209Z"
                                              fill="white"/>
                                        <path d="M9.09668 25.418C9.31511 25.418 9.49219 25.2409 9.49219 25.0225C9.49219 24.804 9.31511 24.627 9.09668 24.627C8.87825 24.627 8.70117 24.804 8.70117 25.0225C8.70117 25.2409 8.87825 25.418 9.09668 25.418Z"
                                              fill="white"/>
                                        <path d="M22.6494 25.418C22.8678 25.418 23.0449 25.2409 23.0449 25.0225C23.0449 24.804 22.8678 24.627 22.6494 24.627C22.431 24.627 22.2539 24.804 22.2539 25.0225C22.2539 25.2409 22.431 25.418 22.6494 25.418Z"
                                              fill="white"/>
                                        <path d="M22.6494 23.0449C21.559 23.0449 20.6719 23.932 20.6719 25.0225C20.6719 26.1129 21.559 27 22.6494 27C23.7399 27 24.627 26.1129 24.627 25.0225C24.627 23.932 23.7399 23.0449 22.6494 23.0449ZM22.6494 26.209C21.9952 26.209 21.4629 25.6767 21.4629 25.0225C21.4629 24.3682 21.9952 23.8359 22.6494 23.8359C23.3036 23.8359 23.8359 24.3682 23.8359 25.0225C23.8359 25.6767 23.3036 26.209 22.6494 26.209Z"
                                              fill="white"/>
                                        <path d="M6.53094 19.1402L9.40982 17.5078H15.873C16.828 17.5078 17.6271 16.8273 17.8109 15.9258H21.93C23.1957 15.9258 24.2986 15.0694 24.6122 13.8431L26.9877 4.55409C27.0179 4.4357 26.9918 4.31003 26.9168 4.21353C26.8419 4.11703 26.7266 4.06055 26.6045 4.06055H7.18569L6.98398 3.2719C6.49197 1.34546 4.75854 0 2.76855 0H1.18652C0.532301 0 0 0.532301 0 1.18652C0 1.84075 0.532301 2.37305 1.18652 2.37305H2.76855C3.67332 2.37305 4.46128 2.98424 4.68482 3.85947L7.72253 15.7366L5.36108 17.0755C4.49387 17.5665 3.95508 18.4899 3.95508 19.4854C3.95508 21.0119 5.19708 22.2539 6.72363 22.2539H24.2314C24.8857 22.2539 25.418 21.7216 25.418 21.0674C25.418 20.4132 24.8857 19.8809 24.2314 19.8809H6.72363C6.50552 19.8809 6.32812 19.7035 6.32812 19.4854C6.32812 19.3432 6.40575 19.211 6.53094 19.1402ZM21.1909 11.4434H18.7605L18.9088 8.54297H21.6359L21.1909 11.4434ZM22.4362 8.54297H25.1511L24.4094 11.4434H21.9912L22.4362 8.54297ZM21.0695 12.2344L20.6246 15.1348H18.5717L18.72 12.2344H21.0695ZM17.9684 11.4434H15.5338L15.3854 8.54297H18.1167L17.9684 11.4434ZM18.9492 7.75195L19.0975 4.85156H22.2022L21.7573 7.75195H18.9492ZM18.1571 7.75195H15.3449L15.1965 4.85156H18.3054L18.1571 7.75195ZM14.5529 7.75195H12.9874C12.7689 7.75195 12.5919 7.92898 12.5919 8.14746C12.5919 8.36594 12.7689 8.54297 12.9874 8.54297H14.5933L14.7417 11.4434H12.3113L11.2997 4.85156H14.4044L14.5529 7.75195ZM11.5111 11.4434H9.0739L8.33208 8.54297H11.0659L11.5111 11.4434ZM11.6325 12.2344L12.0776 15.1348H10.0181L9.27624 12.2344H11.6325ZM12.8778 15.1348L12.4327 12.2344H14.7822L14.9306 15.1348H12.8778ZM15.7227 15.1348L15.5743 12.2344H17.9279L17.7797 15.1348H15.7227ZM23.8458 13.6472C23.6219 14.523 22.834 15.1348 21.93 15.1348H21.4248L21.8698 12.2344H24.2071L23.8458 13.6472ZM25.3534 7.75195H22.5575L23.0025 4.85156H26.0951L25.3534 7.75195ZM10.4995 4.85156L10.9446 7.75195H8.12985L7.38803 4.85156H10.4995ZM2.76855 1.58203H1.18652C0.968414 1.58203 0.791016 1.40463 0.791016 1.18652C0.791016 0.968414 0.968414 0.791016 1.18652 0.791016H2.76855C3.59991 0.791016 4.37611 1.07847 4.99068 1.56906L4.42679 2.133C3.96046 1.78458 3.38396 1.58203 2.76855 1.58203ZM6.72363 20.6719H24.2314C24.4496 20.6719 24.627 20.8493 24.627 21.0674C24.627 21.2855 24.4496 21.4629 24.2314 21.4629H6.72363C5.63319 21.4629 4.74609 20.5758 4.74609 19.4854C4.74609 18.7743 5.13111 18.1147 5.75105 17.7637L8.3757 16.2755C8.53094 16.1874 8.60799 16.0064 8.56375 15.8334L5.45115 3.66362C5.35924 3.30365 5.19877 2.97596 4.9864 2.69214L5.55045 2.1281C5.85826 2.51316 6.08955 2.96626 6.21759 3.46776L9.32781 15.6283C9.37259 15.8033 9.53026 15.9258 9.71098 15.9258H16.9918C16.8285 16.3862 16.3887 16.7169 15.873 16.7169H9.30551C9.23711 16.7169 9.16993 16.7346 9.11044 16.7683L6.14102 18.452C5.76856 18.6627 5.53711 19.0587 5.53711 19.4854C5.53711 20.1396 6.06941 20.6719 6.72363 20.6719Z"
                                              fill="white"/>
                                        </svg>
                                    </span>
                                Купить
                            </a>
                        </form>
                        <?
                        if($arParams["BUY_IN_1_CLICK"]):?>
                            <div class="buy1click_form">
                                <input type="hidden" name="product-url" value="<?= $arResult['DETAIL_PAGE_URL'] ?>"
                                       id="product-url">
                                <input type="hidden" name="product-name" value="<?= $arResult['NAME'] ?>" id="product-name">
                                <? $APPLICATION->IncludeFile(SITE_DIR . "bitrix/templates/aist/includes/forms/v1_click.php", array(), array("MODE" => "php")); ?>
                            </div>
                        <?endif;?>


                        <? if (count($arResult["OFFERS"]) > 0): ?>
                            <div class="js-canb-visible no-instock-button">
                                Товара нет в наличии
                            </div>
                        <? elseif ($arResult["CATALOG_QUANTITY"] <= 0): ?>
                            <div class="no-instock-button">
                                Товара нет в наличии
                            </div>
                        <? endif; ?>

                        <?
                        // Вывод мбаннера для моб
                        $GLOBALS["newsFilter"] = [
                            "ID" => 110574
                        ];
                        ?>
                        <? $APPLICATION->IncludeComponent(
                            "bitrix:news.list",
                            "banner_info",
                            array(
                                "ADD_CLASS" => "is-mob",
                                "ACTIVE_DATE_FORMAT" => "d.m.Y",
                                "ADD_SECTIONS_CHAIN" => "N",
                                "AJAX_MODE" => "N",
                                "AJAX_OPTION_ADDITIONAL" => "",
                                "AJAX_OPTION_HISTORY" => "N",
                                "AJAX_OPTION_JUMP" => "N",
                                "AJAX_OPTION_STYLE" => "Y",
                                "CACHE_FILTER" => "N",
                                "CACHE_GROUPS" => "Y",
                                "CACHE_TIME" => "36000000",
                                "CACHE_TYPE" => "N",
                                "CHECK_DATES" => "Y",
                                "DETAIL_URL" => "",
                                "DISPLAY_BOTTOM_PAGER" => "N",
                                "DISPLAY_DATE" => "Y",
                                "DISPLAY_NAME" => "Y",
                                "DISPLAY_PICTURE" => "Y",
                                "DISPLAY_PREVIEW_TEXT" => "Y",
                                "DISPLAY_TOP_PAGER" => "N",
                                "FIELD_CODE" => array("", ""),
                                "FILTER_NAME" => "newsFilter",
                                "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                                "IBLOCK_ID" => "11",
                                "IBLOCK_TYPE" => "content",
                                "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                                "INCLUDE_SUBSECTIONS" => "Y",
                                "MESSAGE_404" => "",
                                "NEWS_COUNT" => "1",
                                "PAGER_BASE_LINK_ENABLE" => "N",
                                "PAGER_DESC_NUMBERING" => "N",
                                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                "PAGER_SHOW_ALL" => "N",
                                "PAGER_SHOW_ALWAYS" => "N",
                                "PAGER_TEMPLATE" => ".default",
                                "PAGER_TITLE" => "Новости",
                                "PARENT_SECTION" => "",
                                "PARENT_SECTION_CODE" => "",
                                "PREVIEW_TRUNCATE_LEN" => "",
                                "PROPERTY_CODE" => array("LABEL", "HEADER", "TEXT", "BUTTON_TEXT", "BUTTON_LINK", ""),
                                "SET_BROWSER_TITLE" => "N",
                                "SET_LAST_MODIFIED" => "N",
                                "SET_META_DESCRIPTION" => "N",
                                "SET_META_KEYWORDS" => "N",
                                "SET_STATUS_404" => "N",
                                "SET_TITLE" => "N",
                                "SHOW_404" => "N",
                                "SORT_BY1" => "RAND",
                                "SORT_ORDER1" => "ASC",

                                "SORT_BY2" => "RAND",
                                "SORT_ORDER2" => "ASC",
                                "STRICT_SECTION_CHECK" => "N"
                            )
                        ); ?>

                        <div class="good__info__shipping" style="display: none">
                            <div class="good__info__shipping__item">
                                <?
                                $select_fields = array();
                                $filter = array("ACTIVE" => "Y");
                                $resStore = CCatalogStore::GetList(array(), $filter, false, false, $select_fields);
                                $total_STARS1 = $resStore->SelectedRowsCount();
                                ?>
                                <div class="char">Самовывоз из <?= $total_STARS1 ?>
                                    <a href="/contacts/"
                                       class="link">магазин<?= sw_get_ending_groups($total_STARS1) ?></a>
                                </div>
                                <div class="val">                                                                                        <?php
                                    $date = date("Y-m-d");
                                    $timestamp = strtotime($date);
                                    $weekday = date("l", $timestamp);
                                    $normalized_weekday = strtolower($weekday);
                                    if ($normalized_weekday == "sunday") {
                                        echo "Завтра";
                                    } else {
                                        $time = date("H");
                                        if ($time > 16) {
                                            echo 'Завтра';
                                        } else {
                                            echo "Сегодня";
                                        }
                                    }
                                    ?>, БЕСПЛАТНО
                                </div>
                            </div>
                            <div class="good__info__shipping__item">
                                <div class="char">Доставка до дома или офиса</div>
                                <div class="val">
                                    <div class="hint_wrap">
                                        После <?= date('d.m', strtotime(' +5 day')) ?>, от 250 Р
                                        <div class="hint" style="display: none">
                                            <div class="hint__icon"><i class="icon-question"></i></div>
                                            <div class="hint__content">Lorem ipsum lorem ipsum Lorem ipsum lorem ipsum
                                                Lorem ipsum lorem ipsum
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="good__info__shipping__item" style="display: none">
                                <div class="hint_wrap">
                                    Купить по безналичному расчету
                                    <div class="hint">
                                        <div class="hint__icon"><i class="icon-question"></i></div>
                                        <div class="hint__content">Lorem ipsum lorem ipsum Lorem ipsum lorem ipsum Lorem
                                            ipsum lorem ipsum
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <?php
                        //маркетплейсы

                        function create_marketplace_table($p0, $p1, $p2, $p3, $p4)
                        {
                            $type_discont = ($p4 == 'PROMO_PROC') ? '%' : ' руб';
                            $market_table = '';

                            if ($p0 == 'PROMO_OZON') :
                                $market_table .= '<a href="' . $p1 . '" data-discount="' . $p2 . '" data-code="' . $p3 . '" data-typediscont="' . $type_discont . '"><img src="/local/templates/aist/assets/img/market1c.png" alt=""></a>';
                            endif;
                            if ($p0 == 'PROMO_WILDBERRIES') :
                                $market_table .= '<a href="' . $p1 . '" data-discount="' . $p2 . '" data-code="' . $p3 . '" data-typediscont="' . $type_discont . '"><img src="/local/templates/aist/assets/img/market2c.png" alt=""></a>';
                            endif;
                            if ($ap0 == 'PROMO_SBERMEGAMARKET') :
                                $market_table .= '<a href="' . $p1 . '" data-discount="' . $p2 . '" data-code="' . $p3 . '" data-typediscont="' . $type_discont . '"><img src="/local/templates/aist/assets/img/market3c.png" alt=""></a>';
                            endif;
                            if ($ap0 == 'PROMO_YAMARKET') :
                                $market_table .= '<a href="' . $p1 . '" data-discount="' . $p2 . '" data-code="' . $p3 . '" data-typediscont="' . $type_discont . '"><img src="/local/templates/aist/assets/img/market4c.png" alt=""></a>';
                            endif;

                            return $market_table;
                        }

                        $prop_cat_prod = [];
                        $index = 0;

                        $dateNow = new DateTime('now', new DateTimeZone('UTC'));

                        $arFilter = [
                            'IBLOCK_ID' => 100,
                            'ACTIVE' => 'Y',
                            '>=DATE_ACTIVE_TO' => $dateNow->format('d.m.Y H:i:s')
                        ];
                        $result = CIBlockElement::GetList([], $arFilter, false, false, []);
                        while ($ob = $result->GetNextElement()) :

                        $arFields = $ob->GetFields();
                        $arProps = $ob->GetProperties();

                        $prop_cat_prod = explode(',', $arProps['PROMO_CAT_PROD']['VALUE']);

                        if ($arProps['PROMO_CATEGORIES_PRODUCTS']['VALUE_XML_ID'] == 'PROMO_CATEGORIES') :
                        for ($i = 0;
                        $i < count($prop_cat_prod);
                        $i++) :

                        $rsElement = CIBlockElement::GetByID($arResult['ID']);
                        if ($ar_res = $rsElement->Fetch()) :
                            $category_id = $ar_res['IBLOCK_SECTION_ID'];
                        endif;

                        if ($prop_cat_prod[$i] != $category_id) {
                            continue;
                        }

                        if ($ar_res > 0 && $index == 0) :
                        ?>
                        <div class="markets-list">
                            <h5 class="good__title">Купить этот товар также:</h5>
                            <div>
                                <?php
                                $index++;
                                endif;

                                if ($arProps['PROMO_PLACE']['VALUE_XML_ID'] == 'PROMO_OZON' && !empty($arResult['PROPERTIES']['LINK_URL_OZON']['VALUE'])) {
                                    echo create_marketplace_table(
                                        $arProps['PROMO_PLACE']['VALUE_XML_ID'],
                                        $arResult['PROPERTIES']['LINK_URL_OZON']['VALUE'],
                                        $arProps['PROMO_VALUE']['VALUE'],
                                        $arProps['PROMO_CODE']['VALUE'],
                                        $arProps['PROMO_TYPE']['VALUE_XML_ID']
                                    );
                                }

                                if ($arProps['PROMO_PLACE']['VALUE_XML_ID'] == 'PROMO_WILDBERRIES' && !empty($arResult['PROPERTIES']['LINK_URL_WILDBERRIES']['VALUE'])) {
                                    echo create_marketplace_table(
                                        $arProps['PROMO_PLACE']['VALUE_XML_ID'],
                                        $arResult['PROPERTIES']['LINK_URL_WILDBERRIES']['VALUE'],
                                        $arProps['PROMO_VALUE']['VALUE'],
                                        $arProps['PROMO_CODE']['VALUE'],
                                        $arProps['PROMO_TYPE']['VALUE_XML_ID']
                                    );
                                }

                                if ($arProps['PROMO_PLACE']['VALUE_XML_ID'] == 'PROMO_SBERMEGAMARKET' && !empty($arResult['PROPERTIES']['LINK_URL_SBERMEGAMARKET']['VALUE'])) {
                                    echo create_marketplace_table(
                                        $arProps['PROMO_PLACE']['VALUE_XML_ID'],
                                        $arResult['PROPERTIES']['LINK_URL_SBERMEGAMARKET']['VALUE'],
                                        $arProps['PROMO_VALUE']['VALUE'],
                                        $arProps['PROMO_CODE']['VALUE'],
                                        $arProps['PROMO_TYPE']['VALUE_XML_ID']
                                    );
                                }

                                if ($arProps['PROMO_PLACE']['VALUE_XML_ID'] == 'PROMO_YAMARKET' && !empty($arResult['PROPERTIES']['LINK_URL_YAMARKET']['VALUE'])) {
                                    echo create_marketplace_table(
                                        $arProps['PROMO_PLACE']['VALUE_XML_ID'],
                                        $arResult['PROPERTIES']['LINK_URL_YAMARKET']['VALUE'],
                                        $arProps['PROMO_VALUE']['VALUE'],
                                        $arProps['PROMO_CODE']['VALUE'],
                                        $arProps['PROMO_TYPE']['VALUE_XML_ID']
                                    );
                                }

                                endfor;

                                elseif ($arProps['PROMO_CATEGORIES_PRODUCTS']['VALUE_XML_ID'] == 'PROMO_PRODUCTS') :

                                for ($k = 0;
                                     $k < count($prop_cat_prod);
                                     $k++) :

                                if ($prop_cat_prod[$k] != $arResult['ID']) {
                                    continue;
                                }

                                if (count($prop_cat_prod) > 0 && $index == 0) :

                                ?>
                                <div class="markets-list">
                                    <h5 class="good__title">Купить этот товар также:</h5>
                                    <div>
                                        <?php
                                        $index++;
                                        endif;

                                        if ($arProps['PROMO_PLACE']['VALUE_XML_ID'] == 'PROMO_OZON' && !empty($arResult['PROPERTIES']['LINK_URL_OZON']['VALUE'])) {
                                            echo create_marketplace_table(
                                                $arProps['PROMO_PLACE']['VALUE_XML_ID'],
                                                $arResult['PROPERTIES']['LINK_URL_OZON']['VALUE'],
                                                $arProps['PROMO_VALUE']['VALUE'],
                                                $arProps['PROMO_CODE']['VALUE'],
                                                $arProps['PROMO_TYPE']['VALUE_XML_ID']
                                            );
                                        }

                                        if ($arProps['PROMO_PLACE']['VALUE_XML_ID'] == 'PROMO_WILDBERRIES' && !empty($arResult['PROPERTIES']['LINK_URL_WILDBERRIES']['VALUE'])) {
                                            echo create_marketplace_table(
                                                $arProps['PROMO_PLACE']['VALUE_XML_ID'],
                                                $arResult['PROPERTIES']['LINK_URL_WILDBERRIES']['VALUE'],
                                                $arProps['PROMO_VALUE']['VALUE'],
                                                $arProps['PROMO_CODE']['VALUE'],
                                                $arProps['PROMO_TYPE']['VALUE_XML_ID']
                                            );
                                        }

                                        if ($arProps['PROMO_PLACE']['VALUE_XML_ID'] == 'PROMO_SBERMEGAMARKET' && !empty($arResult['PROPERTIES']['LINK_URL_SBERMEGAMARKET']['VALUE'])) {
                                            echo create_marketplace_table(
                                                $arProps['PROMO_PLACE']['VALUE_XML_ID'],
                                                $arResult['PROPERTIES']['LINK_URL_SBERMEGAMARKET']['VALUE'],
                                                $arProps['PROMO_VALUE']['VALUE'],
                                                $arProps['PROMO_CODE']['VALUE'],
                                                $arProps['PROMO_TYPE']['VALUE_XML_ID']
                                            );
                                        }

                                        if ($arProps['PROMO_PLACE']['VALUE_XML_ID'] == 'PROMO_YAMARKET' && !empty($arResult['PROPERTIES']['LINK_URL_YAMARKET']['VALUE'])) {
                                            echo create_marketplace_table(
                                                $arProps['PROMO_PLACE']['VALUE_XML_ID'],
                                                $arResult['PROPERTIES']['LINK_URL_YAMARKET']['VALUE'],
                                                $arProps['PROMO_VALUE']['VALUE'],
                                                $arProps['PROMO_CODE']['VALUE'],
                                                $arProps['PROMO_TYPE']['VALUE_XML_ID']
                                            );
                                        }

                                        endfor;

                                        endif;

                                        endwhile;

                                        if ($index > 0) :
                                        ?>
                                    </div>
                                </div>
                            <?php
                            endif;
                            ?>

                                <div class="market-popup">
                                    <section>
                                        <i></i>
                                        <h4>Промокод на скидку <span>10</span>
                                            <div class="type-discont"><?= ($arProps['PROMO_TYPE']['VALUE_XML_ID'] == 'PROMO_PROC') ? '%' : ' руб'; ?></div>
                                            на выбранный товар в интернет-магазине
                                        </h4>
                                        <img src="" alt="">
                                        <form>
                                            <input type="text" value="" class="promocode-value">
                                            <button class="promocode-copy-btn">Копировать</button>
                                        </form>
                                        <a href="" class="button">Перейти на маркетплейс</a>
                                    </section>
                                </div>

                                <div class="ask-question">
                                    <a href="" class="question-sidebar-button">Задать вопрос о товаре</a>
                                    <div class="question-sidebar">
                                        <div class="question-sidebar__title">Задать свой вопрос</div>
                                        <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/feedback.php" method="POST"
                                              name="pricing__form22" class="question-sidebar__form pricing__form22"
                                              data-href="#register">
                                            <span class="error_result"></span>
                                            <input type="hidden" value="7" name="IBLOCK_ID" class="IBLOCK_ID">
                                            <input type="hidden" value="Задать свой вопрос" name="subject" class="subject_form">
                                            <input type="hidden" value="<?= $arResult['ID'] ?>" name="id" class="subject_form">
                                            <input type="hidden" value="Y" name="ACTIVE" class="subject_form">

                                            <input type="text" class="input input-3" name="name" placeholder="Ваше имя"
                                                   required>
                                            <input type="text" class="input input-3" name="email" placeholder="Ваш Email">
                                            <textarea class="input input-3" name="message" placeholder="Вопрос"
                                                      required></textarea>
                                            <button class="button button-2 button_send_form_not_modal_window">отправить
                                                <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/arrow-right-white.svg"
                                                     loading="lazy" alt="">
                                            </button>
                                            <input type="checkbox" id="asd" name="checkbox" checked>
                                            <label for="asd" class="checkbox-label">Я согласен на обработку
                                                <a href="/policy-privacy/" class="link">персональных данных</a>
                                            </label>
                                        </form>
                                    </div>
                                </div>

                                <div class="good__info__questions">
                                    <!--a href="#!" class="button button_transparent href_to_faq">Задать вопрос о товаре</a-->

                                    <div class="row">
                                        <? /*<div class="col-xs-6">
                                    <div class="good__info__questions__item">
                                        <div class="good__info__questions__item__icon">
                                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10.459 3.02148H6.47461C6.19955 3.02148 5.97656 3.24448 5.97656 3.51953C5.97656 3.79459 6.19955 4.01758 6.47461 4.01758H10.459C10.734 4.01758 10.957 3.79459 10.957 3.51953C10.957 3.24448 10.734 3.02148 10.459 3.02148Z" fill="#FE7865"/>
                                                <path d="M10.459 5.01367H6.47461C6.19955 5.01367 5.97656 5.23666 5.97656 5.51172C5.97656 5.78677 6.19955 6.00977 6.47461 6.00977H10.459C10.734 6.00977 10.957 5.78677 10.957 5.51172C10.957 5.23666 10.734 5.01367 10.459 5.01367Z" fill="#FE7865"/>
                                                <path d="M10.459 7.00586H2.49023C2.21518 7.00586 1.99219 7.22885 1.99219 7.50391C1.99219 7.77896 2.21518 8.00195 2.49023 8.00195H10.459C10.734 8.00195 10.957 7.77896 10.957 7.50391C10.957 7.22885 10.734 7.00586 10.459 7.00586Z" fill="#FE7865"/>
                                                <path d="M8.65347 8.99805H2.49023C2.21518 8.99805 1.99219 9.22104 1.99219 9.49609C1.99219 9.77115 2.21518 9.99414 2.49023 9.99414H8.65347C8.92852 9.99414 9.15151 9.77115 9.15151 9.49609C9.15151 9.22104 8.92852 8.99805 8.65347 8.99805Z" fill="#FE7865"/>
                                                <path d="M12.9824 8.99805V1.49414C12.9824 0.668312 12.282 0 11.4551 0H4.48236C4.35608 0 4.22619 0.0499375 4.12881 0.147355L0.144434 4.16493C0.0528926 4.25721 0 4.38554 0 4.51562V15.5059C0 16.3297 0.670272 17 1.49414 17C2.54489 17 2.89379 17 2.89229 17C4.41973 17 7.42535 17 12.9824 17C15.1816 17 17 15.1893 17 12.9824C17 10.7854 15.1977 8.99805 12.9824 8.99805ZM3.98438 1.70754V4.01758H1.69343L3.98438 1.70754ZM1.49414 16.0039C1.21952 16.0039 0.996094 15.7805 0.996094 15.5059V5.01367H4.48242C4.75748 5.01367 4.98047 4.79068 4.98047 4.51562V0.996094H11.4551C11.7381 0.996094 11.9863 1.22881 11.9863 1.49414V9.12206C10.9292 9.39047 10.0387 10.0758 9.50416 10.9902H2.49023C2.21518 10.9902 1.99219 11.2132 1.99219 11.4883C1.99219 11.7633 2.21518 11.9863 2.49023 11.9863H9.09195C9.009 12.3048 8.96484 12.6386 8.96484 12.9824H2.49023C2.21518 12.9824 1.99219 13.2054 1.99219 13.4805C1.99219 13.7555 2.21518 13.9785 2.49023 13.9785H9.09118C9.29249 14.7574 9.72775 15.4593 10.349 16.0039H1.49414ZM12.9824 16.0039C11.3446 16.0039 9.96094 14.6202 9.96094 12.9824C9.96094 11.3347 11.3164 9.99414 12.9824 9.99414C14.6485 9.99414 16.0039 11.3347 16.0039 12.9824C16.0039 14.6202 14.6202 16.0039 12.9824 16.0039Z" fill="#FE7865"/>
                                                <path d="M14.8648 12.1351C14.672 11.939 14.3566 11.9364 14.1605 12.1293L12.4815 13.7807L11.8014 13.1226C11.6038 12.9313 11.2885 12.9365 11.0972 13.1341C10.9059 13.3318 10.9111 13.6471 11.1087 13.8384L12.138 14.8344C12.3322 15.0223 12.6408 15.0212 12.8336 14.8316L14.859 12.8394C15.0551 12.6466 15.0577 12.3312 14.8648 12.1351Z" fill="#FE7865"/>
                                            </svg>

                                        </div>
                                        <div class="good__info__questions__item__text">
                                            Инструкция
                                        </div>
                                    </div>
                                </div>*/ ?>
                                        <? /*<div class="col-xs-6">
                                    <div class="good__info__questions__item">
                                        <div class="good__info__questions__item__icon">
                                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10.526 10.9564H10.028V6.47433C10.028 6.19931 9.80502 5.97632 9.53 5.97632H6.54196C6.26694 5.97632 6.04395 6.19928 6.04395 6.47433V8.46635C6.04395 8.74137 6.2669 8.96436 6.54196 8.96436H7.03997V10.9564H6.54196C6.26694 10.9564 6.04395 11.1793 6.04395 11.4544V13.4464C6.04395 13.7214 6.2669 13.9444 6.54196 13.9444H10.526C10.801 13.9444 11.024 13.7215 11.024 13.4464V11.4544C11.024 11.1794 10.801 10.9564 10.526 10.9564ZM10.028 12.9485H7.03997V11.9524H7.53798C7.813 11.9524 8.036 11.7295 8.036 11.4544V8.46638C8.036 8.19136 7.813 7.96837 7.53798 7.96837H7.03997V6.97234H9.03199V11.4544C9.03199 11.7294 9.25498 11.9524 9.53 11.9524H10.028V12.9485Z" fill="#FE7865"/>
                                                <path d="M8.53383 1.99219C7.71003 1.99219 7.03979 2.66239 7.03979 3.48623C7.03979 4.31006 7.71 4.98026 8.53383 4.98026C9.35767 4.98026 10.0279 4.31006 10.0279 3.48623C10.0279 2.66239 9.35767 1.99219 8.53383 1.99219ZM8.53383 3.98421C8.25924 3.98421 8.03582 3.76081 8.03582 3.48619C8.03582 3.2116 8.25924 2.98818 8.53383 2.98818C8.80842 2.98818 9.03185 3.2116 9.03185 3.48619C9.03185 3.76081 8.80846 3.98421 8.53383 3.98421Z" fill="#FE7865"/>
                                                <path d="M8.46618 0C13.1108 0 16.9987 3.75248 16.9987 8.46615C16.9987 10.1049 16.5285 11.7335 15.67 13.0831L16.9631 16.3158C17.1265 16.7242 16.7182 17.1241 16.3157 16.9631L13.0831 15.6701C11.7335 16.5285 10.1049 16.9987 8.46615 16.9987C3.74736 16.9987 1.90735e-06 13.1057 1.90735e-06 8.46622C3.43323e-05 3.78691 3.78654 0 8.46618 0ZM8.46618 16.0093C9.99804 16.0093 11.5176 15.54 12.745 14.7C12.8823 14.606 13.0572 14.5869 13.2113 14.6485L15.6068 15.6067L14.6486 13.2113C14.5869 13.057 14.6062 12.8821 14.7 12.745C15.5401 11.5176 16.0094 9.99804 16.0094 8.46618C16.0094 4.34715 12.6218 0.989418 8.46618 0.989418C4.34714 0.989418 0.989418 4.35379 0.989418 8.47283C0.989418 12.6285 4.34711 16.0093 8.46618 16.0093Z" fill="#FE7865"/>
                                            </svg>

                                        </div>
                                        <div class="good__info__questions__item__text">
                                            Информация
                                        </div>
                                    </div>
                                </div>*/ ?>
                                        <div class="col-xs-6">
                                            <div class="good__info__questions__item">
                                                <div class="good__info__questions__item__icon">
                                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M16.25 0.676268C15.2616 0.240188 13.9591 0 12.5823 0C11.2055 0 9.903 0.240188 8.91457 0.676268C7.78897 1.17289 7.16906 1.88558 7.16906 2.68303V7.22766C6.64709 7.0633 6.09194 6.97446 5.51643 6.97446C2.47667 6.97446 0.00366211 9.44747 0.00366211 12.4872C0.00366211 15.527 2.47667 18 5.51643 18C7.60767 18 9.4306 16.8295 10.3647 15.1093C11.0557 15.2552 11.8135 15.3317 12.5823 15.3317C13.9591 15.3317 15.2616 15.0916 16.25 14.6555C17.3757 14.1589 17.9955 13.4462 17.9955 12.6487V2.68303C17.9955 1.88558 17.3756 1.17289 16.25 0.676268ZM9.34032 1.64121C10.1975 1.26299 11.3489 1.05469 12.5823 1.05469C13.8157 1.05469 14.9671 1.26299 15.8243 1.64121C16.513 1.94506 16.9408 2.3443 16.9408 2.68303C16.9408 3.02176 16.513 3.42096 15.8243 3.72485C14.967 4.10307 13.8157 4.31137 12.5823 4.31137C11.3489 4.31137 10.1975 4.10307 9.34032 3.72485C8.65161 3.42096 8.22375 3.0218 8.22375 2.68303C8.22375 2.34426 8.65161 1.94506 9.34032 1.64121ZM5.51639 16.9453C3.05819 16.9453 1.05832 14.9454 1.05832 12.4872C1.05832 10.029 3.05819 8.02916 5.51639 8.02916C7.97456 8.02916 9.97447 10.029 9.97447 12.4872C9.97447 14.9454 7.9746 16.9453 5.51639 16.9453ZM15.8243 13.6905C14.967 14.0687 13.8157 14.277 12.5823 14.277C11.9598 14.277 11.3481 14.2223 10.7831 14.1173C10.9198 13.6769 11.0023 13.2129 11.0234 12.7332C11.5242 12.8038 12.0458 12.8403 12.5823 12.8403C13.9591 12.8403 15.2616 12.6001 16.2501 12.164C16.5073 12.0505 16.7378 11.9256 16.9409 11.7911V12.6487C16.9408 12.9875 16.513 13.3867 15.8243 13.6905ZM15.8243 11.1991C14.9671 11.5773 13.8157 11.7856 12.5823 11.7856C12.0211 11.7856 11.4797 11.7422 10.9668 11.6578C10.8867 11.1291 10.7309 10.6249 10.5114 10.1563C11.1599 10.2823 11.8638 10.3489 12.5823 10.3489C13.9591 10.3489 15.2616 10.1087 16.25 9.67265C16.5073 9.55913 16.7378 9.43422 16.9408 9.29971V10.1573C16.9408 10.496 16.513 10.8952 15.8243 11.1991ZM15.8243 8.70771C14.967 9.08592 13.8157 9.29422 12.5823 9.29422C11.4919 9.29422 10.451 9.12801 9.63352 8.82555C9.2306 8.37302 8.75447 7.98711 8.22375 7.68663V6.80832C8.42682 6.94282 8.65734 7.06773 8.91457 7.18122C9.903 7.6173 11.2055 7.85749 12.5823 7.85749C13.9591 7.85749 15.2616 7.6173 16.25 7.18122C16.5073 7.0677 16.7378 6.94282 16.9408 6.80832V7.66589C16.9408 8.00462 16.513 8.40382 15.8243 8.70771ZM15.8243 6.21625C14.9671 6.59446 13.8157 6.80276 12.5823 6.80276C11.3489 6.80276 10.1975 6.59446 9.34032 6.21625C8.65161 5.91239 8.22375 5.51319 8.22375 5.17442V4.31685C8.42682 4.45136 8.65734 4.57627 8.91457 4.68979C9.903 5.12587 11.2055 5.36602 12.5823 5.36602C13.9591 5.36602 15.2616 5.12587 16.25 4.68979C16.5073 4.57627 16.7378 4.45136 16.9408 4.31685V5.17442C16.9408 5.51319 16.513 5.91239 15.8243 6.21625Z"
                                                              fill="#FE7865"/>
                                                        <path d="M6.2209 11.9198H5.08974C4.83511 11.9198 4.62793 11.7126 4.62793 11.458C4.62793 11.2034 4.83511 10.9962 5.08974 10.9962H6.99954V9.94151H6.0441V9.22998H4.98941V9.9452C4.19987 9.99709 3.57324 10.6556 3.57324 11.458C3.57324 12.2942 4.25355 12.9745 5.08974 12.9745H6.2209C6.49761 12.9745 6.72272 13.1996 6.72272 13.4764C6.72272 13.7531 6.49761 13.9782 6.2209 13.9782H3.78341V15.0329H4.98941V15.7444H6.0441V15.0329H6.2209C7.07917 15.0329 7.77741 14.3346 7.77741 13.4764C7.77741 12.6181 7.07917 11.9198 6.2209 11.9198Z"
                                                              fill="#FE7865"/>
                                                    </svg>

                                                </div>
                                                <div class="good__info__questions__item__text">
                                                    <a href="/delivery-and-payment/">оплата</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="good__info__questions__item">
                                                <div class="good__info__questions__item__icon">
                                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M14.718 4.17006C13.3156 2.76486 11.4889 1.93989 9.52731 1.81417V1.05469H10.3007C10.592 1.05469 10.8281 0.818578 10.8281 0.527344C10.8281 0.236109 10.592 0 10.3007 0H7.69918C7.40795 0 7.17184 0.236109 7.17184 0.527344C7.17184 0.818578 7.40795 1.05469 7.69918 1.05469H8.47262V1.81417C7.12765 1.90037 5.84617 2.31539 4.72134 3.02087L4.2204 2.51891C3.97659 2.27461 3.65228 2.1401 3.30715 2.1401C2.96202 2.1401 2.6377 2.27464 2.39393 2.51891L1.63206 3.28243C1.13055 3.78492 1.13055 4.60259 1.63206 5.10511L2.13641 5.61052C1.34037 6.88426 0.914062 8.35843 0.914062 9.89838C0.914062 12.0622 1.755 14.0966 3.28198 15.6267C4.80923 17.1571 6.83993 18 8.99996 18C11.16 18 13.1907 17.1571 14.718 15.6267C16.2449 14.0966 17.0859 12.0622 17.0859 9.89838C17.0859 7.73459 16.2449 5.7002 14.718 4.17006ZM2.3786 4.02746L3.14051 3.26398C3.20055 3.20382 3.27069 3.19482 3.30718 3.19482C3.34368 3.19482 3.41385 3.20379 3.47389 3.26398L3.85604 3.6469C3.65787 3.81118 3.4662 3.98552 3.28201 4.17009C3.09836 4.35414 2.92486 4.54563 2.76135 4.74363L2.3786 4.36011C2.28705 4.26839 2.28705 4.11915 2.3786 4.02746ZM8.99996 16.9453C5.12293 16.9453 1.96875 13.7841 1.96875 9.89838C1.96875 6.0127 5.12293 2.85145 8.99996 2.85145C12.877 2.85145 16.0312 6.01267 16.0312 9.89838C16.0312 13.7841 12.877 16.9453 8.99996 16.9453Z"
                                                              fill="#FE7865"/>
                                                        <path d="M9.00004 3.83984C5.66575 3.83984 2.95312 6.55763 2.95312 9.89825C2.95312 13.2389 5.66575 15.9567 9.00004 15.9567C12.3343 15.9567 15.0469 13.2389 15.0469 9.89825C15.0469 6.55763 12.3343 3.83984 9.00004 3.83984ZM9.52738 14.8741V14.2577C9.52738 13.9664 9.29127 13.7303 9.00004 13.7303C8.7088 13.7303 8.47269 13.9664 8.47269 14.2577V14.8741C6.13986 14.6274 4.28101 12.7639 4.03552 10.4256H4.64066C4.93189 10.4256 5.168 10.1895 5.168 9.89825C5.168 9.60702 4.93189 9.37091 4.64066 9.37091H4.03555C4.28101 7.03256 6.13986 5.16907 8.47269 4.92245V5.53884C8.47269 5.83007 8.7088 6.06618 9.00004 6.06618C9.29127 6.06618 9.52738 5.83007 9.52738 5.53884V4.92245C11.8602 5.16907 13.7191 7.03256 13.9646 9.37091H13.3594C13.0682 9.37091 12.8321 9.60702 12.8321 9.89825C12.8321 10.1895 13.0682 10.4256 13.3594 10.4256H13.9645C13.7191 12.7639 11.8602 14.6274 9.52738 14.8741Z"
                                                              fill="#FE7865"/>
                                                        <path d="M10.3009 9.59402H9.55689V7.46118C9.55689 7.16995 9.32078 6.93384 9.02955 6.93384C8.73831 6.93384 8.5022 7.16995 8.5022 7.46118V10.1214C8.5022 10.4126 8.73831 10.6487 9.02955 10.6487H10.3009C10.5921 10.6487 10.8282 10.4126 10.8282 10.1214C10.8282 9.83013 10.5921 9.59402 10.3009 9.59402Z"
                                                              fill="#FE7865"/>
                                                    </svg>

                                                </div>
                                                <div class="good__info__questions__item__text">
                                                    <a href="/delivery-and-payment/">Доставка</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <? if (!isset($aval)) {
                    $aval = $arResult["CATALOG_AVAILABLE"];
                } ?>

                <div class="mobile_cart_bottom">
                    <a href="tel:88007776501">
                        <div class="mobile_cart_bottom-phone">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/mobile_phone.png" loading="lazy" alt="">
                            <p>8 (800) 777-65-01</p>
                        </div>
                    </a>
                    <div class="mobile_cart_bottom-buy">
                        <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/basket/put_offers_in_basket.php" method="POST"
                              class="add_to_cart_form" <?= ($aval == 'N') ? ' style="display:none"' : '' ?>>
                            <input type="hidden" name="product_id" class="product_id"
                                   value="<?= $id_e ? $id_e : $arResult['ID'] ?>">
                            <input type="hidden" name="quantity" value="1" class="product_quantity_input"
                                   data-id="<?= $arResult['ID'] ?>">
                            <a href="#" class="add_to_cart" data-name="<?= $arResult['NAME'] ?>" data-image="<?= $src ?>">
                                <div class="mobile_cart_box">
                                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/mobile_cart.png" loading="lazy" alt="">
                                    <p>Купить</p>
                                </div>
                            </a>
                        </form>
                    </div>
                </div>

            </div>

            <a name="reviews" id="reviews"></a>
            <div class="good__specs tab__section">
                <div class="good__specs__tabs tab__buttons">
                    <button class="tab__button good__specs__tab" data-filter="good-about">
                    <span class="icon-spec">
<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M11.3692 9.48508C11.4282 9.60044 11.5455 9.66709 11.6669 9.66709C11.7175 9.66709 11.7695 9.65541 11.8179 9.63076C11.8375 9.62076 13.7996 8.62398 14.7672 8.31828C14.9426 8.26262 15.0399 8.07525 14.9842 7.89992C14.9288 7.72455 14.7429 7.62689 14.5659 7.68225C13.5466 8.00462 11.5979 8.99472 11.5152 9.03671C11.3512 9.12004 11.2859 9.32077 11.3692 9.48508Z"
      fill="black"/>
<path d="M11.6674 5.6677C11.718 5.6677 11.77 5.65602 11.8184 5.63136C11.838 5.62136 13.8 4.62452 14.7677 4.31881C14.943 4.26314 15.0403 4.07577 14.9847 3.90043C14.9293 3.72505 14.7434 3.62737 14.5664 3.68274C13.547 4.00513 11.5984 4.99529 11.5157 5.03729C11.3517 5.12062 11.2864 5.32132 11.3697 5.48568C11.4287 5.60101 11.546 5.6677 11.6674 5.6677Z"
      fill="black"/>
<path d="M19.6665 5.00011C19.4822 5.00011 19.3332 5.14944 19.3332 5.33343V18.3333C19.3332 18.8846 18.8845 19.3333 18.3332 19.3333H10.3333V18.5663C10.8193 18.3963 12.0853 18 13.3333 18C16.2369 18 18.2086 18.643 18.2282 18.6496C18.3285 18.6829 18.4406 18.6663 18.5282 18.6036C18.6152 18.541 18.6665 18.4403 18.6665 18.3333V3.6668C18.6665 3.51048 18.5582 3.37513 18.4055 3.34145C18.4055 3.34145 18.1442 3.28313 17.6975 3.2011C17.5172 3.16778 17.3429 3.28778 17.3095 3.46844C17.2762 3.64977 17.3958 3.82344 17.5769 3.85676C17.7472 3.88809 17.8899 3.91574 17.9999 3.93778V17.8903C17.2445 17.6923 15.5672 17.3333 13.3332 17.3333C11.8809 17.3333 10.4543 17.8103 10.0129 17.972C9.62055 17.7987 8.43458 17.3333 6.99991 17.3333C4.70391 17.3333 2.81259 17.712 1.99994 17.9043V3.92477C2.6656 3.7611 4.6316 3.33345 6.99991 3.33345C8.21525 3.33345 9.25754 3.71176 9.66656 3.88278V17C9.66656 17.12 9.73121 17.231 9.83555 17.29C9.93988 17.3493 10.0686 17.3476 10.1716 17.2857C10.2046 17.266 13.5072 15.2933 16.4385 14.3163C16.5748 14.2707 16.6665 14.1437 16.6665 14V0.333476C16.6665 0.224141 16.6128 0.121798 16.5228 0.0594941C16.4335 -0.00284903 16.3189 -0.0171848 16.2158 0.0211742C13.5489 1.02148 10.8503 2.7008 10.8232 2.71748C10.6672 2.81513 10.6196 3.02079 10.7172 3.17681C10.8146 3.33282 11.0202 3.38048 11.1765 3.28282C11.2012 3.26716 13.5489 1.80651 15.9998 0.820854V13.7614C13.7245 14.552 11.3489 15.843 10.3332 16.421V3.6668C10.3332 3.54048 10.2619 3.42512 10.1489 3.36848C10.0915 3.34016 8.72556 2.66681 6.99991 2.66681C3.98794 2.66681 1.67229 3.31849 1.57495 3.34645C1.43194 3.38712 1.33327 3.51813 1.33327 3.6668V18.3333C1.33327 18.4377 1.38261 18.5363 1.46561 18.5993C1.52428 18.6437 1.59495 18.6667 1.66663 18.6667C1.69729 18.6667 1.72795 18.6624 1.75795 18.6537C1.78096 18.6474 4.08594 18 6.99991 18C8.21857 18 9.25989 18.3794 9.66656 18.5497V19.3333H1.66663C1.1153 19.3333 0.666635 18.8847 0.666635 18.3333V5.33347C0.666635 5.14948 0.517301 5.00015 0.333318 5.00015C0.149335 5.00015 0 5.14948 0 5.33347V18.3333C0 19.2523 0.74765 20 1.66667 20H18.3332C19.2522 20 19.9999 19.2524 19.9999 18.3333V5.33347C19.9998 5.14944 19.8509 5.00011 19.6665 5.00011Z"
      fill="black"/>
<path d="M11.3692 7.48603C11.4282 7.60141 11.5455 7.66806 11.6669 7.66806C11.7175 7.66806 11.7695 7.65638 11.8179 7.63173C11.8375 7.62173 13.7996 6.62483 14.7672 6.3191C14.9426 6.26342 15.0399 6.07604 14.9842 5.90068C14.9288 5.72529 14.7429 5.62762 14.5659 5.68298C13.5466 6.00539 11.5979 6.99561 11.5152 7.03761C11.3512 7.12095 11.2859 7.3217 11.3692 7.48603Z"
      fill="black"/>
<path d="M11.3692 11.4849C11.4282 11.6003 11.5455 11.667 11.6669 11.667C11.7175 11.667 11.7695 11.6553 11.8179 11.6306C11.8375 11.6206 13.7996 10.6238 14.7672 10.3181C14.9426 10.2624 15.0399 10.075 14.9842 9.89969C14.9288 9.72431 14.7429 9.62664 14.5659 9.682C13.5466 10.0044 11.5979 10.9946 11.5152 11.0366C11.3512 11.1199 11.2859 11.3206 11.3692 11.4849Z"
      fill="black"/>
<path d="M8.07622 6.18727C6.01792 5.70386 3.67891 6.31973 3.58059 6.34608C3.40294 6.39378 3.29728 6.57659 3.34493 6.75474C3.38493 6.90385 3.51958 7.00194 3.66661 7.00194C3.69528 7.00194 3.72426 6.99827 3.75329 6.99061C3.77563 6.98427 6.02761 6.39143 7.92427 6.83681C8.10259 6.87852 8.28294 6.76741 8.32493 6.58792C8.36689 6.40879 8.25556 6.2293 8.07622 6.18727Z"
      fill="black"/>
<path d="M8.07622 8.18639C6.01792 7.70332 3.67891 8.31885 3.58059 8.34521C3.40294 8.3929 3.29728 8.57576 3.34493 8.75388C3.38493 8.903 3.51958 9.00109 3.66661 9.00109C3.69528 9.00109 3.72426 8.99741 3.75329 8.98975C3.77563 8.98342 6.02761 8.39056 7.92427 8.83595C8.10259 8.87766 8.28294 8.76655 8.32493 8.58706C8.36689 8.40792 8.25556 8.22842 8.07622 8.18639Z"
      fill="black"/>
<path d="M11.3692 13.4852C11.4282 13.6006 11.5455 13.6672 11.6669 13.6672C11.7175 13.6672 11.7695 13.6555 11.8179 13.6309C11.8375 13.6209 13.7996 12.624 14.7672 12.3183C14.9426 12.2626 15.0399 12.0753 14.9842 11.8999C14.9288 11.7246 14.7429 11.6269 14.5659 11.6822C13.5466 12.0046 11.5979 12.9948 11.5152 13.0368C11.3512 13.1201 11.2859 13.3209 11.3692 13.4852Z"
      fill="black"/>
<path d="M8.07622 10.1862C6.01792 9.70311 3.67891 10.3183 3.58059 10.345C3.40294 10.3927 3.29728 10.5756 3.34493 10.7538C3.38493 10.9029 3.51958 11.0013 3.66661 11.0013C3.69528 11.0013 3.72426 10.9973 3.75329 10.9896C3.77563 10.9833 6.02761 10.3904 7.92427 10.8358C8.10259 10.8775 8.28294 10.7664 8.32493 10.5869C8.36689 10.4078 8.25556 10.2283 8.07622 10.1862Z"
      fill="black"/>
<path d="M8.07622 14.1863C6.01792 13.7029 3.67891 14.3188 3.58059 14.3451C3.40294 14.3928 3.29728 14.5757 3.34493 14.7538C3.38493 14.9029 3.51958 15.0013 3.66661 15.0013C3.69528 15.0013 3.72426 14.9973 3.75329 14.9896C3.77563 14.9833 6.02761 14.3905 7.92427 14.8358C8.10259 14.8772 8.28294 14.7664 8.32493 14.587C8.36689 14.4078 8.25556 14.2283 8.07622 14.1863Z"
      fill="black"/>
<path d="M8.07622 12.1861C6.01792 11.7027 3.67891 12.3182 3.58059 12.3449C3.40294 12.3926 3.29728 12.5755 3.34493 12.7536C3.38493 12.9028 3.51958 13.0012 3.66661 13.0012C3.69528 13.0012 3.72426 12.9972 3.75329 12.9895C3.77563 12.9832 6.02761 12.3903 7.92427 12.8357C8.10259 12.8774 8.28294 12.7663 8.32493 12.5868C8.36689 12.4077 8.25556 12.2282 8.07622 12.1861Z"
      fill="black"/>
</svg>

                    </span>
                        ПРЕЗЕНТАЦИЯ
                    </button>
                    <button class="tab__button good__specs__tab active" data-filter="good-chars">
                    <span class="icon-spec">
<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0333)">
<path d="M19.2036 15.8558H4.35465C4.23859 15.8558 4.14418 15.7614 4.14418 15.6454V0.796406C4.14418 0.357266 3.78688 0 3.34773 0H0.796406C0.357266 0 0 0.357266 0 0.796406V5.56191C0 5.72375 0.131133 5.85488 0.292969 5.85488C0.454805 5.85488 0.585938 5.72375 0.585938 5.56191V0.796406C0.585938 0.680352 0.680352 0.585938 0.796406 0.585938H3.34773C3.46383 0.585938 3.55824 0.680352 3.55824 0.796406V2.22004H2.56512C2.40328 2.22004 2.27215 2.35117 2.27215 2.51301C2.27215 2.67484 2.40328 2.80598 2.56512 2.80598H3.55824V4.08691H2.94922C2.78738 4.08691 2.65625 4.21805 2.65625 4.37988C2.65625 4.54172 2.78738 4.67285 2.94922 4.67285H3.55824V5.95375H2.56512C2.40328 5.95375 2.27215 6.08488 2.27215 6.24672C2.27215 6.40855 2.40328 6.53969 2.56512 6.53969H3.55824V7.82062H2.94922C2.78738 7.82062 2.65625 7.95176 2.65625 8.11359C2.65625 8.27543 2.78738 8.40656 2.94922 8.40656H3.55824V9.68746H2.56512C2.40328 9.68746 2.27215 9.81859 2.27215 9.98043C2.27215 10.1423 2.40328 10.2734 2.56512 10.2734H3.55824V11.5543H2.94922C2.78738 11.5543 2.65625 11.6855 2.65625 11.8473C2.65625 12.0091 2.78738 12.1403 2.94922 12.1403H3.55824V13.4212H2.56512C2.40328 13.4212 2.27215 13.5523 2.27215 13.7142C2.27215 13.876 2.40328 14.0071 2.56512 14.0071H3.55824V15.6454C3.55824 15.7577 3.58164 15.8648 3.62383 15.9618L0.585938 18.9997V6.93152C0.585938 6.76969 0.454805 6.63856 0.292969 6.63856C0.131133 6.63856 0 6.76969 0 6.93152V19.2036C0 19.6425 0.357031 20 0.796406 20H19.2036C19.6428 20 20 19.6427 20 19.2036V16.6523C20 16.2131 19.6427 15.8558 19.2036 15.8558ZM19.4141 19.2036C19.4141 19.3196 19.3196 19.4141 19.2036 19.4141H1.00027L4.03816 16.3762C4.1352 16.4184 4.24223 16.4418 4.35461 16.4418H5.80684V17.4355C5.80684 17.5973 5.93797 17.7284 6.0998 17.7284C6.26164 17.7284 6.39277 17.5973 6.39277 17.4355V16.4418H7.67371V17.0514C7.67371 17.2132 7.80484 17.3443 7.96668 17.3443C8.12852 17.3443 8.25965 17.2132 8.25965 17.0514V16.4418H9.54055V17.4355C9.54055 17.5973 9.67168 17.7284 9.83352 17.7284C9.99535 17.7284 10.1265 17.5973 10.1265 17.4355V16.4418H11.4074V17.0514C11.4074 17.2132 11.5386 17.3443 11.7004 17.3443C11.8622 17.3443 11.9934 17.2132 11.9934 17.0514V16.4418H13.2743V17.4355C13.2743 17.5973 13.4054 17.7284 13.5672 17.7284C13.7291 17.7284 13.8602 17.5973 13.8602 17.4355V16.4418H15.1411V17.0514C15.1411 17.2132 15.2723 17.3443 15.4341 17.3443C15.5959 17.3443 15.7271 17.2132 15.7271 17.0514V16.4418H17.008V17.4355C17.008 17.5973 17.1391 17.7284 17.3009 17.7284C17.4628 17.7284 17.5939 17.5973 17.5939 17.4355V16.4418H19.2036C19.3196 16.4418 19.4141 16.5362 19.4141 16.6523V19.2036Z"
      fill="black"/>
<g clip-path="url(#clip1)">
<path d="M15.7917 14H8.20833C7.4045 14 6.75 13.3461 6.75 12.5417V4.95833C6.75 4.15392 7.4045 3.5 8.20833 3.5H9.375C9.48758 3.5 9.58558 3.43933 9.63575 3.339C9.68592 3.23867 9.676 3.12375 9.60833 3.03333L8.73333 1.86608C8.5805 1.66308 8.5 1.421 8.5 1.16667C8.5 0.52325 9.02325 0 9.66667 0H14.3333C14.9767 0 15.5 0.52325 15.5 1.16667C15.5 1.421 15.4195 1.66308 15.2673 1.86608L14.3917 3.03333C14.324 3.12375 14.3135 3.23808 14.3642 3.33842C14.415 3.43875 14.5124 3.5 14.625 3.5H15.7917C16.5955 3.5 17.25 4.15392 17.25 4.95833V12.5417C17.25 13.3461 16.5955 14 15.7917 14ZM8.20833 4.08333C7.72592 4.08333 7.33333 4.47592 7.33333 4.95833V12.5417C7.33333 13.0241 7.72592 13.4167 8.20833 13.4167H15.7917C16.2741 13.4167 16.6667 13.0241 16.6667 12.5417V4.95833C16.6667 4.47592 16.2741 4.08333 15.7917 4.08333H14.625C14.2913 4.08333 13.9915 3.89842 13.8422 3.59975C13.6934 3.30108 13.7249 2.9505 13.925 2.68392L14.8 1.51667C14.8764 1.41517 14.9167 1.29383 14.9167 1.16667C14.9167 0.844667 14.6547 0.583333 14.3333 0.583333H9.66667C9.34525 0.583333 9.08333 0.844667 9.08333 1.16667C9.08333 1.29383 9.12358 1.41517 9.2 1.51667L10.075 2.68392C10.2751 2.9505 10.3066 3.30167 10.1578 3.59975C10.0085 3.89842 9.70867 4.08333 9.375 4.08333H8.20833Z"
      fill="black"/>
<path d="M15.2082 10.5001H13.4582C12.9758 10.5001 12.5833 10.1075 12.5833 9.62508V7.29175C12.5833 6.80933 12.9758 6.41675 13.4582 6.41675H15.2082C15.3692 6.41675 15.4999 6.54741 15.4999 6.70841C15.4999 6.86941 15.3692 7.00008 15.2082 7.00008H13.4582C13.2972 7.00008 13.1666 7.13133 13.1666 7.29175V9.62508C13.1666 9.7855 13.2972 9.91675 13.4582 9.91675H14.9166V8.75008H14.0416C13.8806 8.75008 13.7499 8.61941 13.7499 8.45841C13.7499 8.29741 13.8806 8.16675 14.0416 8.16675H15.2082C15.3692 8.16675 15.4999 8.29741 15.4999 8.45841V10.2084C15.4999 10.3694 15.3692 10.5001 15.2082 10.5001Z"
      fill="black"/>
<path d="M8.79167 10.5001C8.63067 10.5001 8.5 10.3694 8.5 10.2084V6.70841C8.5 6.54741 8.63067 6.41675 8.79167 6.41675C8.95267 6.41675 9.08334 6.54741 9.08334 6.70841V10.2084C9.08334 10.3694 8.95267 10.5001 8.79167 10.5001Z"
      fill="black"/>
<path d="M11.1251 10.5002C11.0726 10.5002 11.0189 10.4862 10.9705 10.4559L8.63714 8.99753C8.55897 8.94853 8.50822 8.86511 8.50122 8.77353C8.49364 8.68136 8.53039 8.59095 8.59981 8.53086L10.9331 6.4892C11.0539 6.38303 11.2382 6.3947 11.345 6.51661C11.4511 6.63795 11.4389 6.8217 11.3176 6.92787L9.27997 8.71111L11.2802 9.96178C11.4167 10.0469 11.4581 10.2272 11.373 10.3631C11.317 10.4518 11.2219 10.5002 11.1251 10.5002Z"
      fill="black"/>
</g>
</g>
<defs>
<clipPath id="clip0333">
<rect width="20" height="20" fill="white"/>
</clipPath>
<clipPath id="clip1">
<rect width="14" height="14" fill="white" transform="translate(5)"/>
</clipPath>
</defs>
</svg>
                    </span>
                        Характеристики
                    </button>
                    <button class="tab__button good__specs__tab" data-filter="good-reviews" id="greviews">
                    <span class="icon-spec">
<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip01112)">
<path d="M5.62825 12.8409C4.95632 12.7179 4.31429 12.4848 3.72003 12.1483C3.60941 12.0857 3.47119 12.102 3.37801 12.1885C2.85474 12.6748 2.17361 12.9427 1.46013 12.9427C1.32876 12.9427 1.19863 12.9338 1.07022 12.916C1.63543 12.5205 2.07483 11.9578 2.31826 11.3003C2.35689 11.1959 2.33322 11.0786 2.25717 10.9975C1.17823 9.84731 0.584053 8.34391 0.584053 6.76434C0.584053 3.35754 3.34678 0.585938 6.74259 0.585938C9.71865 0.585938 12.2663 2.71418 12.8003 5.64645C12.8293 5.80559 12.9815 5.91094 13.1401 5.88199C13.2988 5.85293 13.4039 5.70031 13.3749 5.54113C12.7901 2.33039 10.0008 0 6.74259 0C3.02474 0 0 3.03449 0 6.76434C0 8.43055 0.603911 10.0193 1.70446 11.2594C1.40839 11.9278 0.861751 12.4608 0.182419 12.7367C0.072189 12.7815 0 12.8889 0 13.0083C0 13.1277 0.072189 13.2351 0.182458 13.2798C0.588921 13.445 1.01882 13.5286 1.46013 13.5286C2.25032 13.5286 3.00764 13.2564 3.61506 12.758C4.21445 13.0734 4.85543 13.295 5.52339 13.4173C5.68191 13.4462 5.83411 13.3409 5.86312 13.1817C5.89213 13.0226 5.78692 12.87 5.62825 12.8409Z"
      fill="black"/>
<path d="M16.8841 11.757L14.5223 11.4L13.4542 9.25692C13.4048 9.1577 13.3036 9.09497 13.193 9.09497C13.0824 9.09497 12.9813 9.15767 12.9318 9.25692L11.8637 11.4L9.50197 11.757C9.39259 11.7735 9.30191 11.8506 9.26772 11.9561C9.23354 12.0617 9.26173 12.1775 9.34053 12.2554L11.0422 13.9368L10.6506 16.3004C10.6325 16.4099 10.6776 16.5202 10.7671 16.5855C10.8565 16.6507 10.975 16.6596 11.0733 16.6085L13.193 15.5045L15.3128 16.6085C15.3552 16.6306 15.4013 16.6415 15.4473 16.6415C15.5079 16.6415 15.5681 16.6226 15.619 16.5855C15.7085 16.5202 15.7536 16.4099 15.7354 16.3004L15.3439 13.9368L17.0455 12.2554C17.1243 12.1775 17.1525 12.0617 17.1183 11.9561C17.0841 11.8506 16.9935 11.7735 16.8841 11.757ZM14.8261 13.626C14.7584 13.6929 14.7273 13.7887 14.7429 13.8828L15.0635 15.8185L13.3276 14.9144C13.2433 14.8704 13.1428 14.8704 13.0585 14.9144L11.3225 15.8185L11.6431 13.8828C11.6587 13.7887 11.6277 13.6929 11.56 13.626L10.1665 12.2491L12.1006 11.9568C12.1946 11.9427 12.2758 11.8834 12.3183 11.7982L13.193 10.0431L14.0677 11.7982C14.1102 11.8834 14.1914 11.9426 14.2854 11.9568L16.2195 12.2491L14.8261 13.626Z"
      fill="black"/>
<path d="M19.7532 19.2082C19.0738 18.9323 18.5272 18.3993 18.2311 17.7309C19.3317 16.4908 19.9356 14.9019 19.9356 13.2357C19.9356 9.50589 16.9109 6.47144 13.193 6.47144C9.47514 6.47144 6.45044 9.50589 6.45044 13.2357C6.45044 16.9656 9.47517 20.0001 13.193 20.0001C14.2835 20.0001 15.3603 19.7341 16.3205 19.2294C16.9279 19.7278 17.6853 20.0001 18.4755 20.0001C18.9168 20.0001 19.3467 19.9164 19.7532 19.7513C19.8634 19.7065 19.9356 19.5991 19.9356 19.4797C19.9356 19.3603 19.8634 19.2529 19.7532 19.2082ZM18.4755 19.4141C17.762 19.4141 17.0809 19.1463 16.5576 18.6599C16.5023 18.6085 16.431 18.5818 16.3591 18.5818C16.3099 18.5818 16.2605 18.5942 16.2156 18.6197C15.2978 19.1394 14.2526 19.4141 13.193 19.4141C9.79722 19.4141 7.03449 16.6425 7.03449 13.2357C7.03449 9.82897 9.79722 7.05737 13.193 7.05737C16.5888 7.05737 19.3516 9.82897 19.3516 13.2357C19.3516 14.8153 18.7574 16.3187 17.6784 17.4689C17.6024 17.5499 17.5787 17.6673 17.6173 17.7717C17.8608 18.4292 18.3002 18.9919 18.8654 19.3874C18.737 19.4052 18.6068 19.4141 18.4755 19.4141Z"
      fill="black"/>
</g>
<defs>
<clipPath id="clip01112">
<rect width="19.9357" height="20" fill="white"/>
</clipPath>
</defs>
</svg>
                    </span>
                        Отзывы
                    </button>
                    <button class="tab__button good__specs__tab" data-filter="good-faq" id="gfaq">
                    <span class="icon-spec">
<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M17.0711 2.92892C15.1823 1.04019 12.6711 0 10 0C7.59552 0 5.27191 0.865631 3.45703 2.43759C3.33298 2.54501 3.31955 2.7327 3.42697 2.8566C3.53439 2.98065 3.72192 2.99408 3.84598 2.88666C5.55283 1.40823 7.73834 0.594025 10 0.594025C12.5124 0.594025 14.8744 1.57242 16.651 3.349C18.4276 5.12558 19.406 7.48764 19.406 10C19.406 12.5124 18.4276 14.8744 16.651 16.651C14.8744 18.4276 12.5124 19.406 10 19.406C7.48764 19.406 5.12558 18.4276 3.349 16.651C1.57242 14.8744 0.594025 12.5124 0.594025 10C0.594025 7.66312 1.4566 5.42282 3.02307 3.69202C3.13309 3.5704 3.12363 3.38257 3.00201 3.2724C2.8804 3.16238 2.69257 3.17169 2.58255 3.29346C0.917206 5.13367 0 7.51541 0 10C0 12.6711 1.04019 15.1823 2.92892 17.0711C4.81766 18.9598 7.32895 20 10 20C12.6711 20 15.1823 18.9598 17.0711 17.0711C18.9598 15.1823 20 12.6711 20 10C20 7.32895 18.9598 4.81766 17.0711 2.92892Z"
      fill="black"/>
<path d="M9.62225 13.5596C8.70505 13.5596 7.95874 14.314 7.95874 15.2411C7.95874 16.1682 8.70505 16.9226 9.62225 16.9226C10.0725 16.9226 10.4966 16.7435 10.8163 16.418C11.126 16.1026 11.3038 15.6737 11.3038 15.2411C11.3038 14.7966 11.1275 14.3756 10.8077 14.0556C10.4877 13.7358 10.0667 13.5596 9.62225 13.5596ZM9.62225 16.3286C9.0325 16.3286 8.55276 15.8408 8.55276 15.2411C8.55276 14.6414 9.0325 14.1536 9.62225 14.1536C10.2117 14.1536 10.7096 14.6516 10.7096 15.2411C10.7096 15.8305 10.2117 16.3286 9.62225 16.3286Z"
      fill="black"/>
<path d="M12.4847 8.92068C12.6084 9.02872 12.796 9.0159 12.9038 8.8923C13.6196 8.0723 13.9826 7.21384 13.9826 6.34088C13.9826 5.42032 13.5691 4.58582 12.8184 3.99118C12.0637 3.39349 11.0395 3.07764 9.85635 3.07764C8.39273 3.07764 7.48743 3.57172 6.98587 3.98615C6.37933 4.48724 6.01709 5.15924 6.01709 5.78363C6.01709 6.18951 6.18326 6.54703 6.48508 6.79041C6.73349 6.99075 7.0643 7.10565 7.39297 7.10565C7.95923 7.10565 8.20749 6.73959 8.40692 6.4454C8.65961 6.07294 8.8981 5.72107 9.80218 5.72107C10.1121 5.72107 11.1233 5.7879 11.1233 6.64667C11.1233 7.29486 10.5241 7.75491 9.99536 8.1608C9.86475 8.26089 9.74145 8.35565 9.6264 8.45331C9.01758 8.97806 8.31857 9.7886 8.31857 11.4114C8.31857 12.3127 8.53723 12.9131 9.60458 12.9131C10.0773 12.9131 10.4342 12.806 10.6654 12.5948C10.8595 12.4175 10.9622 12.1699 10.9622 11.8786C10.9622 11.0031 10.9622 10.5636 11.8551 9.8652L11.8704 9.8533C11.9097 9.82263 11.9552 9.78708 12.0052 9.74664C12.133 9.64365 12.153 9.45673 12.05 9.32901C11.947 9.20129 11.76 9.1813 11.6323 9.2843C11.5849 9.3226 11.542 9.35602 11.5049 9.38501L11.4892 9.39722C10.4202 10.2334 10.3681 10.854 10.3681 11.8788C10.3681 12.0104 10.3681 12.3191 9.60458 12.3191C9.23166 12.3191 9.11813 12.2411 9.06091 12.1731C8.96112 12.0545 8.9126 11.8054 8.9126 11.4114C8.9126 10.0379 9.46954 9.3728 10.0125 8.90482C10.1148 8.81799 10.2325 8.72766 10.3571 8.63199C10.9631 8.16675 11.7175 7.58768 11.7175 6.64667C11.7175 5.72351 10.9657 5.12704 9.80218 5.12704C8.58331 5.12704 8.19727 5.69635 7.91528 6.112C7.72256 6.39627 7.63467 6.51163 7.39297 6.51163C7.0788 6.51163 6.61127 6.31769 6.61127 5.78363C6.61127 5.4368 6.80902 4.90274 7.36429 4.44406C7.79092 4.09174 8.56943 3.67151 9.85651 3.67151C11.9363 3.67151 13.3886 4.76923 13.3886 6.34088C13.3886 7.06598 13.075 7.79291 12.4565 8.50168C12.3484 8.62527 12.3612 8.81281 12.4847 8.92068Z"
      fill="black"/>
</svg>
                    </span>
                        Вопрос-ответ
                    </button>
                </div>
                <div class="good__specs__content" id="good__specs__content">
                    <div class="tab__item good__specs__section good-chars">
                        <h3>Характеристики</h3>
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="good__specs__items">

                                    <? $APPLICATION->IncludeComponent(
                                        "redsign:grupper.list",
                                        "",
                                        array(
                                            "CACHE_TIME" => "3600000",
                                            "CACHE_TYPE" => "A",
                                            "COMPOSITE_FRAME_MODE" => "A",
                                            "COMPOSITE_FRAME_TYPE" => "AUTO",
                                            "DISPLAY_PROPERTIES" => $arResult['DISPLAY_PROPERTIES']
                                        )
                                    ); ?>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <? $APPLICATION->IncludeComponent(
                                    "bitrix:catalog.set.constructor",
                                    "constructor",
                                    array(
                                        "BASKET_URL" => "/personal/basket.php",
                                        "BUNDLE_ITEMS_COUNT" => "3",
                                        "CACHE_GROUPS" => "Y",
                                        "CACHE_TIME" => "36000000",
                                        "CACHE_TYPE" => "A",
                                        "CONVERT_CURRENCY" => "N",
                                        "ELEMENT_ID" => $arResult["ID"],
                                        "IBLOCK_ID" => "4",
                                        "IBLOCK_TYPE_ID" => "catalog",
                                        "PRICE_CODE" => array("rub"),
                                        "PRICE_VAT_INCLUDE" => "Y",
                                        "TEMPLATE_THEME" => "blue"
                                    )
                                ); ?>
                            </div>

                        </div>
                    </div>

                    <div class="tab__item good__specs__section good-about">
                        <h3>О товаре</h3>
                        <? foreach ($arResult["PROPERTIES"]["FILES"]["VALUE"] as $k => $v): ?>
                            <div class="good__spec-img">
                                <img src="<?= CFile::GetPath($v); ?>" alt="">
                            </div>
                        <? endforeach; ?>

                        <? if ($arResult["PROPERTIES"]["PVIDEO"]["VALUE"]): ?>
                            <div class="good__spec-video" id="videoBlockDescr">
                                <div class="video-block__wrapper">
                                    <div class="video-block__item">
                                        <iframe id="videoCustom"
                                                width="560"
                                                height="315"
                                                src="https://www.youtube.com/embed/<?= $arResult["PROPERTIES"]["PVIDEO"]["VALUE"]; ?>"
                                                frameborder="0"
                                                allow="accelerometer; gyroscope; picture-in-picture"
                                                allowfullscreen
                                                title=""
                                        >
                                        </iframe>
                                    </div>
                                    <script>
                                        var videoIDcustom = 'https://www.youtube.com/embed/<?=$arResult["PROPERTIES"]["PVIDEO"]["VALUE"];?>';
                                        var videoIDparsed = videoIDcustom.split('https://www.youtube.com/embed/');

                                        let elemVideo = document.getElementById('videoCustom');
                                        elemVideo.setAttribute('srcdoc', "<style>*{padding:0;margin:0;overflow:hidden}html,body{height:100%}img,span{position:absolute;width:100%;top:0;bottom:0;margin:auto}span{height:1.5em;text-align:center;font:48px/1.5 sans-serif;color:white;background-image: url(\'data:image/svg+xml,%3Csvg width=`150` height=`150` viewBox=`0 0 150 150` fill=`none` xmlns=`http://www.w3.org/2000/svg`%3E%3Cpath d=`M113.908 72.9347L58.9076 35.4348C58.1401 34.9148 57.1501 34.8573 56.3301 35.2898C55.5126 35.7223 55.0001 36.5723 55.0001 37.4998V112.5C55.0001 113.427 55.5126 114.277 56.3326 114.71C56.6976 114.905 57.1001 115 57.5001 115C57.9926 115 58.4851 114.852 58.9076 114.565L113.908 77.0647C114.59 76.5997 115 75.8272 115 74.9997C115 74.1722 114.59 73.3997 113.908 72.9347ZM60.0001 107.767V42.2323L108.063 74.9997L60.0001 107.767Z` fill=`white`/%3E%3Cpath d=`M75 0C33.645 0 0 33.645 0 75C0 116.355 33.645 150 75 150C116.355 150 150 116.355 150 75C150 33.645 116.355 0 75 0ZM75 145C36.4025 145 5 113.598 5 75C5 36.4025 36.4025 5 75 5C113.598 5 145 36.4025 145 75C145 113.598 113.598 145 75 145Z` fill=`white`/%3E%3C/svg%3E%0A\');}</style><a href=https://www.youtube.com/embed/" + videoIDparsed[1] + "?autoplay=1><img src=https://img.youtube.com/vi/" + videoIDparsed[1] + "/hqdefault.jpg ><span>▶</span></a>");

                                    </script>

                                </div>
                            </div>
                        <? endif; ?>

                        <? foreach ($arResult["PROPERTIES"]["FILES_1"]["VALUE"] as $k => $v): ?>
                            <div class="good__spec-img">
                                <img src="<?= CFile::GetPath($v); ?>" alt="">
                            </div>
                        <? endforeach; ?>

                        <? if ($arResult['DETAIL_TEXT'] != ""): ?>
                            <div class="good-spec-info">
                                <? if ($arResult["PROPERTIES"]["HEADER"]["VALUE"]): ?>
                                    <h4><?= $arResult["PROPERTIES"]["HEADER"]["VALUE"]; ?></h4>
                                <? endif; ?>
                                <?= $arResult['DETAIL_TEXT'] ?>
                            </div>
                        <? endif; ?>
                    </div>

                    <div class="tab__item good__specs__section good-reviews">
                        <h3>Отзывы о товаре (<?= $arResult["REVIEWS_COUNT"] ?>): <a href="">все ОТЗЫВы</a></h3>
                        <div class="row">
                            <a name="reviews_m"></a>
                            <div class="col-lg-7">
                                <?
                                $per_page_val = 20;
                                $per_page_cook = $APPLICATION->get_cookie("sw_per_page");
                                if ($per_page_cook)
                                    $per_page_val = $per_page_cook;

                                global $arrFilter;

                                $arrFilter['PROPERTY_ID'] = $arResult['ID'];
                                $arrFilter['PROPERTY_1346'] = "26478";

                                $APPLICATION->IncludeComponent(
                                    "bitrix:news.list",
                                    "reviews",
                                    array(
                                        "USE_FILTER" => "Y",
                                        "MODERATION" => 'Y',
                                        "FILTER_NAME" => "arrFilter",
                                        "ACTIVE_DATE_FORMAT" => "d.m.Y",
                                        "ADD_SECTIONS_CHAIN" => "Y",
                                        "AJAX_MODE" => "N",
                                        "AJAX_OPTION_ADDITIONAL" => "",
                                        "AJAX_OPTION_HISTORY" => "N",
                                        "AJAX_OPTION_JUMP" => "N",
                                        "AJAX_OPTION_STYLE" => "Y",
                                        "CACHE_FILTER" => "N",
                                        "CACHE_GROUPS" => "Y",
                                        "CACHE_TIME" => "0",
                                        "CACHE_TYPE" => "N",
                                        "CHECK_DATES" => "Y",
                                        "DETAIL_URL" => "",
                                        "DISPLAY_BOTTOM_PAGER" => "Y",
                                        "DISPLAY_DATE" => "Y",
                                        "DISPLAY_NAME" => "Y",
                                        "DISPLAY_PICTURE" => "Y",
                                        "DISPLAY_PREVIEW_TEXT" => "Y",
                                        "DISPLAY_TOP_PAGER" => "N",
                                        "FIELD_CODE" => array(
                                            0 => "ID",
                                            1 => "CODE",
                                            2 => "XML_ID",
                                            3 => "NAME",
                                            4 => "TAGS",
                                            5 => "SORT",
                                            6 => "PREVIEW_TEXT",
                                            7 => "PREVIEW_PICTURE",
                                            8 => "DETAIL_TEXT",
                                            9 => "DETAIL_PICTURE",
                                            10 => "DATE_ACTIVE_FROM",
                                            11 => "ACTIVE_FROM",
                                            12 => "DATE_ACTIVE_TO",
                                            13 => "ACTIVE_TO",
                                            14 => "SHOW_COUNTER",
                                            15 => "SHOW_COUNTER_START",
                                            16 => "IBLOCK_TYPE_ID",
                                            17 => "IBLOCK_ID",
                                            18 => "IBLOCK_CODE",
                                            19 => "IBLOCK_NAME",
                                            20 => "IBLOCK_EXTERNAL_ID",
                                            21 => "DATE_CREATE",
                                            22 => "CREATED_BY",
                                            23 => "CREATED_USER_NAME",
                                            24 => "TIMESTAMP_X",
                                            25 => "MODIFIED_BY",
                                            26 => "USER_NAME",
                                            27 => "",
                                        ),
                                        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                                        "IBLOCK_ID" => "2",
                                        "IBLOCK_TYPE" => "content",
                                        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                                        "INCLUDE_SUBSECTIONS" => "Y",
                                        "MESSAGE_404" => "",
                                        "NEWS_COUNT" => $per_page_val,
                                        "PAGER_BASE_LINK_ENABLE" => "N",
                                        "PAGER_DESC_NUMBERING" => "N",
                                        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                        "PAGER_SHOW_ALL" => "N",
                                        "PAGER_SHOW_ALWAYS" => "N",
                                        "PAGER_TEMPLATE" => ".default",
                                        "PAGER_TITLE" => "Новости",
                                        "PARENT_SECTION" => "",
                                        "PARENT_SECTION_CODE" => "",
                                        "PREVIEW_TRUNCATE_LEN" => "",
                                        "PROPERTY_CODE" => array(
                                            0 => "",
                                            1 => "NAME",
                                            2 => "EMAIL",
                                            3 => "MESSAGE",
                                            4 => "P1",
                                            5 => "P2",
                                            6 => "P3",
                                            7 => "P4",
                                            8 => "ANSWER",
                                            9 => "MODERATION",
                                            10 => ""
                                        ),
                                        "SET_BROWSER_TITLE" => "Y",
                                        "SET_LAST_MODIFIED" => "N",
                                        "SET_META_DESCRIPTION" => "Y",
                                        "SET_META_KEYWORDS" => "Y",
                                        "SET_STATUS_404" => "Y",
                                        "SET_TITLE" => "Y",
                                        "SHOW_404" => "Y",
                                        "SORT_BY1" => "ACTIVE_FROM",
                                        "SORT_BY2" => "SORT",
                                        "SORT_ORDER1" => "DESC",
                                        "SORT_ORDER2" => "ASC",
                                        "STRICT_SECTION_CHECK" => "N",
                                        "COMPONENT_TEMPLATE" => "reviews",
                                        "FILE_404" => ""
                                    ),
                                    false
                                ); ?>
                            </div>
                            <div class="col-lg-5">
                                <a href="" class="question-sidebar-button">Оставить отзыв</a>
                                <div class="question-sidebar">
                                    <div class="question-sidebar__title">Оставить отзыв</div>
                                    <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/feedback.php" method="POST"
                                          name="pricing__form2" class="question-sidebar__form pricing__form2"
                                          data-href="#register" enctype="multipart/form-data">
                                        <span class="error_result"></span>
                                        <input type="hidden" value="2" name="IBLOCK_ID" class="IBLOCK_ID">
                                        <input type="hidden" value="Оставить отзыв" name="subject" class="subject_form">
                                        <input type="hidden" value="Y" name="ACTIVE" class="subject_form">
                                        <input type="hidden" value="<?= $arResult['ID'] ?>" name="id" class="subject_form">
                                        <input type="hidden" value="<?= $arResult['NAME'] ?>" name="cart_name"
                                               class="cart_name">

                                        <input type="text" name="name" class="input input-3" placeholder="Ваше имя" required>
                                        <input type="text" name="email" class="input input-3" placeholder="Ваш Email">
                                        <textarea class="input input-3" name="message" placeholder="Отзыв" required></textarea>
                                        <label class="question-sidebar__attach label_label">
                                            <input type="file" name="file[]" multiple class="file_input"/>
                                            Прикрепить файлы
                                        </label>
                                        <div class="leave-review">

                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <div class="leave-review__item">

                                                        <div class="leave-review__item__title">Общая оценка</div>
                                                        <div class="rating__inputs">

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="general"
                                                                       id="general1" value="1" aria-label="Ужасно">
                                                                <label for="general1" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="general"
                                                                       id="general2" value="2" aria-label="Сносно">
                                                                <label for="general2" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="general"
                                                                       id="general3" value="3" aria-label="Нормально">
                                                                <label for="general3" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="general"
                                                                       id="general4" value="4" aria-label="Хорошо">
                                                                <label for="general4" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="general"
                                                                       id="general5" value="5" aria-label="Отлично">
                                                                <label for="general5" class="rating__star__label"></label>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-6">
                                                    <div class="leave-review__item">

                                                        <div class="leave-review__item__title">Служба сервиса</div>
                                                        <div class="rating__inputs">

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="service"
                                                                       id="service1" value="1" aria-label="Ужасно">
                                                                <label for="service1" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="service"
                                                                       id="service2" value="2" aria-label="Сносно">
                                                                <label for="service2" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="service"
                                                                       id="service3" value="3" aria-label="Нормально">
                                                                <label for="service3" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="service"
                                                                       id="service4" value="4" aria-label="Хорошо">
                                                                <label for="service4" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="service"
                                                                       id="service5" value="5" aria-label="Отлично">
                                                                <label for="service5" class="rating__star__label"></label>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-6">
                                                    <div class="leave-review__item">

                                                        <div class="leave-review__item__title">Качество товара</div>
                                                        <div class="rating__inputs">

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="quality"
                                                                       id="quality1" value="1" aria-label="Ужасно">
                                                                <label for="quality1" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="quality"
                                                                       id="quality2" value="2" aria-label="Сносно">
                                                                <label for="quality2" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="quality"
                                                                       id="quality3" value="3" aria-label="Нормально">
                                                                <label for="quality3" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="quality"
                                                                       id="quality4" value="4" aria-label="Хорошо">
                                                                <label for="quality4" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="quality"
                                                                       id="quality5" value="5" aria-label="Отлично">
                                                                <label for="quality5" class="rating__star__label"></label>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-6">
                                                    <div class="leave-review__item">

                                                        <div class="leave-review__item__title">Доставка</div>
                                                        <div class="rating__inputs">

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="shipping"
                                                                       id="shipping1" value="1" aria-label="Ужасно">
                                                                <label for="shipping1" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="shipping"
                                                                       id="shipping2" value="2" aria-label="Сносно">
                                                                <label for="shipping2" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="shipping"
                                                                       id="shipping3" value="3" aria-label="Нормально">
                                                                <label for="shipping3" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="shipping"
                                                                       id="shipping4" value="4" aria-label="Хорошо">
                                                                <label for="shipping4" class="rating__star__label"></label>
                                                            </div>

                                                            <div class="rating__star__item">
                                                                <input class="rating__star" type="radio" name="shipping"
                                                                       id="shipping5" value="5" aria-label="Отлично">
                                                                <label for="shipping5" class="rating__star__label"></label>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button class="button button-2 button_send_form_not_modal_window_with_file">отправить
                                            <img src="img/icons/arrow-right-white.svg" loading="lazy" alt="">
                                        </button>
                                        <input type="checkbox" id="sdf" name="checkbox" checked>
                                        <label for="sdf" class="checkbox-label">Я согласен на обработку
                                            <a href="/policy-privacy/" class="link">персональных данных</a>
                                        </label>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div id="review-full-text">
                            <div class="good-reviews">
                                <i></i>
                                <section></section>
                            </div>
                        </div>
                    </div>

                    <div class="tab__item good__specs__section good-faq">
                        <h3>Вопрос-ответ</h3>
                        <div class="row">
                            <div class="col-lg-7">
                                <?
                                $per_page_val = 20;
                                $per_page_cook = $APPLICATION->get_cookie("sw_per_page");
                                if ($per_page_cook)
                                    $per_page_val = $per_page_cook;

                                global $arrFilter;
                                $arrFilter['PROPERTY_ID'] = $arResult['ID'];
                                $arrFilter['MODERATION'] = 'Y';

                                $APPLICATION->IncludeComponent("bitrix:news.list", "otvet", array(
                                    "USE_FILTER" => "Y",

                                    "FILTER_NAME" => "arrFilter",
                                    "ACTIVE_DATE_FORMAT" => "d.m.Y",
                                    // Формат показа даты
                                    "ADD_SECTIONS_CHAIN" => "N",
                                    // Включать раздел в цепочку навигации
                                    "AJAX_MODE" => "N",
                                    // Включить режим AJAX
                                    "AJAX_OPTION_ADDITIONAL" => "",
                                    // Дополнительный идентификатор
                                    "AJAX_OPTION_HISTORY" => "N",
                                    // Включить эмуляцию навигации браузера
                                    "AJAX_OPTION_JUMP" => "N",
                                    // Включить прокрутку к началу компонента
                                    "AJAX_OPTION_STYLE" => "Y",
                                    // Включить подгрузку стилей
                                    "CACHE_FILTER" => "N",
                                    // Кешировать при установленном фильтре
                                    "CACHE_GROUPS" => "N",
                                    // Учитывать права доступа
                                    "CACHE_TIME" => "0",
                                    // Время кеширования (сек.)
                                    "CACHE_TYPE" => "A",
                                    // Тип кеширования
                                    "CHECK_DATES" => "Y",
                                    // Показывать только активные на данный момент элементы
                                    "DETAIL_URL" => "",
                                    // URL страницы детального просмотра (по умолчанию - из настроек инфоблока)
                                    "DISPLAY_BOTTOM_PAGER" => "Y",
                                    // Выводить под списком
                                    "DISPLAY_DATE" => "Y",
                                    // Выводить дату элемента
                                    "DISPLAY_NAME" => "Y",
                                    // Выводить название элемента
                                    "DISPLAY_PICTURE" => "Y",
                                    // Выводить изображение для анонса
                                    "DISPLAY_PREVIEW_TEXT" => "Y",
                                    // Выводить текст анонса
                                    "DISPLAY_TOP_PAGER" => "N",
                                    // Выводить над списком
                                    "FIELD_CODE" => array(    // Поля
                                        0 => "",
                                        1 => "ID",
                                        2 => "CODE",
                                        3 => "XML_ID",
                                        4 => "NAME",
                                        5 => "TAGS",
                                        6 => "SORT",
                                        7 => "PREVIEW_TEXT",
                                        8 => "PREVIEW_PICTURE",
                                        9 => "DETAIL_TEXT",
                                        10 => "DETAIL_PICTURE",
                                        11 => "DATE_ACTIVE_FROM",
                                        12 => "ACTIVE_FROM",
                                        13 => "DATE_ACTIVE_TO",
                                        14 => "ACTIVE_TO",
                                        15 => "SHOW_COUNTER",
                                        16 => "SHOW_COUNTER_START",
                                        17 => "IBLOCK_TYPE_ID",
                                        18 => "IBLOCK_ID",
                                        19 => "IBLOCK_CODE",
                                        20 => "IBLOCK_NAME",
                                        21 => "IBLOCK_EXTERNAL_ID",
                                        22 => "DATE_CREATE",
                                        23 => "CREATED_BY",
                                        24 => "CREATED_USER_NAME",
                                        25 => "TIMESTAMP_X",
                                        26 => "MODIFIED_BY",
                                        27 => "USER_NAME",
                                        28 => "BRANDS",
                                    ),
                                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                                    // Скрывать ссылку, если нет детального описания
                                    "IBLOCK_ID" => "7",
                                    // Код информационного блока
                                    "IBLOCK_TYPE" => "content",
                                    // Тип информационного блока (используется только для проверки)
                                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                                    // Включать инфоблок в цепочку навигации
                                    "INCLUDE_SUBSECTIONS" => "Y",
                                    // Показывать элементы подразделов раздела
                                    "MESSAGE_404" => "",
                                    // Сообщение для показа (по умолчанию из компонента)
                                    "NEWS_COUNT" => $per_page_val,
                                    // Количество новостей на странице
                                    "PAGER_BASE_LINK_ENABLE" => "N",
                                    // Включить обработку ссылок
                                    "PAGER_DESC_NUMBERING" => "N",
                                    // Использовать обратную навигацию
                                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                    // Время кеширования страниц для обратной навигации
                                    "PAGER_SHOW_ALL" => "N",
                                    // Показывать ссылку "Все"
                                    "PAGER_SHOW_ALWAYS" => "N",
                                    // Выводить всегда
                                    "PAGER_TEMPLATE" => ".default",
                                    // Шаблон постраничной навигации
                                    "PAGER_TITLE" => "Новости",
                                    // Название категорий
                                    "PARENT_SECTION" => "",
                                    // ID раздела
                                    "PARENT_SECTION_CODE" => "",
                                    // Код раздела
                                    "PREVIEW_TRUNCATE_LEN" => "",
                                    // Максимальная длина анонса для вывода (только для типа текст)
                                    "PROPERTY_CODE" => array(    // Свойства
                                        0 => "",
                                        1 => "NAME",
                                        2 => "EMAIL",
                                        3 => "MESSAGE",
                                        4 => "ANSWER",
                                        5 => "MODERATION",
                                        6 => "BRANDS",
                                    ),
                                    "SET_BROWSER_TITLE" => "Y",
                                    // Устанавливать заголовок окна браузера
                                    "SET_LAST_MODIFIED" => "N",
                                    // Устанавливать в заголовках ответа время модификации страницы
                                    "SET_META_DESCRIPTION" => "Y",
                                    // Устанавливать описание страницы
                                    "SET_META_KEYWORDS" => "Y",
                                    // Устанавливать ключевые слова страницы
                                    "SET_STATUS_404" => "N",
                                    // Устанавливать статус 404
                                    "SET_TITLE" => "Y",
                                    // Устанавливать заголовок страницы
                                    "SHOW_404" => "N",
                                    // Показ специальной страницы
                                    "SORT_BY1" => "ACTIVE_FROM",
                                    // Поле для первой сортировки новостей
                                    "SORT_BY2" => "SORT",
                                    // Поле для второй сортировки новостей
                                    "SORT_ORDER1" => "DESC",
                                    // Направление для первой сортировки новостей
                                    "SORT_ORDER2" => "ASC",
                                    // Направление для второй сортировки новостей
                                    "STRICT_SECTION_CHECK" => "N",
                                    // Строгая проверка раздела для показа списка
                                ),
                                    false
                                ); ?>
                            </div>
                            <div class="col-lg-5">
                                <a href="" class="question-sidebar-button">Задать вопрос о товаре</a>
                                <div class="question-sidebar">
                                    <div class="question-sidebar__title">Задать свой вопрос</div>
                                    <form action="<?= SITE_TEMPLATE_PATH ?>/hbmdev/ajax/feedback.php" method="POST"
                                          name="pricing__form22" class="question-sidebar__form pricing__form22"
                                          data-href="#register">
                                        <span class="error_result"></span>
                                        <input type="hidden" value="7" name="IBLOCK_ID" class="IBLOCK_ID">
                                        <input type="hidden" value="Задать свой вопрос" name="subject" class="subject_form">
                                        <input type="hidden" value="<?= $arResult['ID'] ?>" name="id" class="subject_form">
                                        <input type="hidden" value="Y" name="ACTIVE" class="subject_form">

                                        <input type="text" class="input input-3" name="name" placeholder="Ваше имя" required>
                                        <input type="text" class="input input-3" name="email" placeholder="Ваш Email">
                                        <textarea class="input input-3" name="message" placeholder="Вопрос" required></textarea>
                                        <button class="button button-2 button_send_form_not_modal_window">отправить
                                            <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/icons/arrow-right-white.svg"
                                                 loading="lazy" alt="">
                                        </button>
                                        <input type="checkbox" id="asd" name="checkbox" checked>
                                        <label for="asd" class="checkbox-label">Я согласен на обработку
                                            <a href="/policy-privacy/" class="link">персональных данных</a>
                                        </label>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--            new -->
            <?php
            $arSelect = array(
                "ID",
                "NAME",
                'DETAIL_PAGE_URL',
                'PREVIEW_PICTURE',
                'CATALOG_PRICE_1',
                'PROPERTY_P1',
                'PROPERTY_P100',
                'PROPERTY_P200',
                'PROPERTY_P300',
                'PROPERTY_SPEC',
                'PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO',
                'PROPERTY_BRANDS'
            );
            $arFilter = array("IBLOCK_ID" => 4, "ACTIVE" => "Y", 'PROPERTY_P200' => 5);
            $res_best = CIBlockElement::GetList(array("rand" => "asc"), $arFilter, false, array("nPageSize" => 6), $arSelect);
            $total_best = $res_best->SelectedRowsCount();
            if ($total_best) {
                ?>
                <div class="col-xs-12">
                    <div class="title-line">
                        <h2 class="title title_underline">С этим товаром покупают:</h2>
                    </div>
                </div>
                <div class="col-lg-9 col-100-mid-desk">

                    <div class="bask-slider-discount stock-block old-desktop-slider stock-block--bg7 pb-104">
                        <div class="show_two_slider slider-super-discount slider-super-discount-actions">
                            <?php
                            while ($ob = $res_best->GetNextElement()) {
                                $hideForSite = $ob->GetProperty("HIDE_FOR_SITE");
                                if ($hideForSite["VALUE"] == "розница") continue;
                                $arFields = $ob->GetFields();
                                if ($arFields["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"]) {
                                    $arFields["NAME"] = $arFields["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"];
                                }
                                if ($arFields["PROPERTY_BRANDS_VALUE"]) {
                                    $arFields["PROPERTIES"]["BRANDS"]["VALUE"] = $arFields["PROPERTY_BRANDS_VALUE"];
                                }
                                ?>
                                <div class="slider-super-discount__item">
                                    <? // sw_product_cat_cart($arFields); ?>
                                    <? sw_product_cat($arFields); ?>
                                </div>
                            <?php } ?>
                        </div>

                    </div>

                </div>
            <?php } ?>
            <div class="col-lg-3 col-xs-12">
                <?
                // Вывод баннера для десктопа
                $GLOBALS["newsFilter"] = [
                    //"PROPERTY_TYPE_VALUE" => "Вертикальный"
                    "ID" => 110573
                ];
                ?>
                <? $APPLICATION->IncludeComponent(
                    "bitrix:news.list",
                    "banner_info",
                    array(
                        "ACTIVE_DATE_FORMAT" => "d.m.Y",
                        "ADD_SECTIONS_CHAIN" => "N",
                        "AJAX_MODE" => "N",
                        "AJAX_OPTION_ADDITIONAL" => "",
                        "AJAX_OPTION_HISTORY" => "N",
                        "AJAX_OPTION_JUMP" => "N",
                        "AJAX_OPTION_STYLE" => "Y",
                        "CACHE_FILTER" => "N",
                        "CACHE_GROUPS" => "Y",
                        "CACHE_TIME" => "36000000",
                        "CACHE_TYPE" => "N",
                        "CHECK_DATES" => "Y",
                        "DETAIL_URL" => "",
                        "DISPLAY_BOTTOM_PAGER" => "N",
                        "DISPLAY_DATE" => "Y",
                        "DISPLAY_NAME" => "Y",
                        "DISPLAY_PICTURE" => "Y",
                        "DISPLAY_PREVIEW_TEXT" => "Y",
                        "DISPLAY_TOP_PAGER" => "N",
                        "FIELD_CODE" => array("", ""),
                        "FILTER_NAME" => "newsFilter",
                        "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                        "IBLOCK_ID" => "11",
                        "IBLOCK_TYPE" => "content",
                        "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                        "INCLUDE_SUBSECTIONS" => "Y",
                        "MESSAGE_404" => "",
                        "NEWS_COUNT" => "1",
                        "PAGER_BASE_LINK_ENABLE" => "N",
                        "PAGER_DESC_NUMBERING" => "N",
                        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                        "PAGER_SHOW_ALL" => "N",
                        "PAGER_SHOW_ALWAYS" => "N",
                        "PAGER_TEMPLATE" => ".default",
                        "PAGER_TITLE" => "Новости",
                        "PARENT_SECTION" => "",
                        "PARENT_SECTION_CODE" => "",
                        "PREVIEW_TRUNCATE_LEN" => "",
                        "PROPERTY_CODE" => array("LABEL", "HEADER", "TEXT", "BUTTON_TEXT", "BUTTON_LINK", ""),
                        "SET_BROWSER_TITLE" => "N",
                        "SET_LAST_MODIFIED" => "N",
                        "SET_META_DESCRIPTION" => "N",
                        "SET_META_KEYWORDS" => "N",
                        "SET_STATUS_404" => "N",
                        "SET_TITLE" => "N",
                        "SHOW_404" => "N",
                        "SORT_BY1" => "RAND",
                        "SORT_ORDER1" => "ASC",

                        "SORT_BY2" => "RAND",
                        "SORT_ORDER2" => "ASC",
                        "STRICT_SECTION_CHECK" => "N"
                    )
                ); ?>
            </div>

            <!--           end new -->

            <!--            new -->
            <?php
            $arSelect = array(
                "ID",
                "NAME",
                'DETAIL_PAGE_URL',
                'PREVIEW_PICTURE',
                'CATALOG_PRICE_1',
                'PROPERTY_P1',
                'PROPERTY_P100',
                'PROPERTY_P200',
                'PROPERTY_P300',
                'PROPERTY_SPEC',
                'PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO',
                'PROPERTY_BRANDS'
            );
            $arFilter = array("IBLOCK_ID" => 4, "ACTIVE" => "Y", 'PROPERTY_P100' => 4);
            $res_sale = CIBlockElement::GetList(array("rand" => "asc"), $arFilter, false, array("nPageSize" => 8), $arSelect);
            $total_sale = $res_sale->SelectedRowsCount();
            if ($total_sale) {
                ?>
                <div class="col-md-12">
                    <div class="title-line">
                        <h2 class="title  title_underline">Рекомендуем также:</h2>
                    </div>
                    <div class="bask-slider-discount stock-block old-desktop-slider stock-block--bg7 pb-104">
                        <div class="slider-super-discount slider-super-discount-actions">
                            <?php
                            while ($ob = $res_sale->GetNextElement()) {
                                $hideForSite = $ob->GetProperty("HIDE_FOR_SITE");
                                if ($hideForSite["VALUE"] == "розница") continue;
                                $arFields = $ob->GetFields();
                                if ($arFields["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"]) {
                                    $arFields["NAME"] = $arFields["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"];
                                }
                                if ($arFields["PROPERTY_BRANDS_VALUE"]) {
                                    $arFields["PROPERTIES"]["BRANDS"]["VALUE"] = $arFields["PROPERTY_BRANDS_VALUE"];
                                }
                                ?>
                                <div class="slider-super-discount__item">
                                    <? //sw_product_cat_cart($arFields); ?>
                                    <? sw_product_cat($arFields); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <!--          end  new -->

            <!--            new -->
            <?php
            $arSelect = array(
                "ID",
                "NAME",
                'DETAIL_PAGE_URL',
                'PREVIEW_PICTURE',
                'CATALOG_PRICE_1',
                'PROPERTY_P1',
                'PROPERTY_P100',
                'PROPERTY_P200',
                'PROPERTY_P300',
                'PROPERTY_SPEC',
                'PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO',
                'PROPERTY_BRANDS'
            );
            $arFilter = array("IBLOCK_ID" => 4, "ACTIVE" => "Y", 'PROPERTY_P100' => 4);
            $res_sale = CIBlockElement::GetList(array("rand" => "asc"), $arFilter, false, array("nPageSize" => 8), $arSelect);
            $total_sale = $res_sale->SelectedRowsCount();
            if ($total_sale) {
                ?>
                <div class="col-md-12">
                    <div class="title-line">
                        <h2 class="title  title_underline">Ранее вы смотрели:</h2>
                    </div>
                    <div class="bask-slider-discount stock-block old-desktop-slider stock-block--bg7 pb-104">
                        <div class="slider-super-discount slider-super-discount-actions">
                            <?php
                            while ($ob = $res_sale->GetNextElement()) {
                                $hideForSite = $ob->GetProperty("HIDE_FOR_SITE");
                                if ($hideForSite["VALUE"] == "розница") continue;
                                $arFields = $ob->GetFields();
                                if ($arFields["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"]) {
                                    $arFields["NAME"] = $arFields["PROPERTY_ROZNICHNOE_NAIMENOVANIE_SVOYSTVO_VALUE"];
                                }
                                if ($arFields["PROPERTY_BRANDS_VALUE"]) {
                                    $arFields["PROPERTIES"]["BRANDS"]["VALUE"] = $arFields["PROPERTY_BRANDS_VALUE"];
                                }
                                ?>
                                <div class="slider-super-discount__item">
                                    <? sw_product_cat($arFields); ?>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
            <?php } ?>
            <!--          end  new -->
            <?
            CModule::IncludeModule('iblock');
            $arSelect_similar_products = array("ID", "NAME", 'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'DETAIL_PAGE_URL');
            $arFilter_similar_products = array(
                "IBLOCK_ID" => 4,
                "ACTIVE" => "Y",
                '!ID' => $arResult['ID'],
                'SECTION_ID' => $arResult['SECTION']['ID'],
                'INCLUDE_SUBSECTIONS' => 'Y'
            );
            $res_similar_products = CIBlockElement::GetList(array("DATE_ACTIVE_FROM" => "ASC"), $arFilter_similar_products, false, array('nPageSize' => 4), $arSelect_similar_products);
            $total_similar_products = $res_similar_products->SelectedRowsCount();
            if ($total_similar_products) {
                ?>
                <div class="good__add" style="display: none">
                    <h2 class="title">Вам может понравиться</h2>
                    <div class="row">
                        <?
                        while ($ob_similar_products = $res_similar_products->GetNextElement()) {
                            $arFields = $ob_similar_products->GetFields();
                            ?>
                            <div class="col-md-3 col-sm-4 col-xs-6">
                                <? sw_product_cat($arFields, "", true) ?>
                            </div>
                        <? } ?>
                    </div>
                </div>
            <? } ?>

            <?
            $arViewed = array();
            $basketUserId = (int)CSaleBasket::GetBasketUserID(false);
            if ($basketUserId > 0) {
                $viewedIterator = \Bitrix\Catalog\CatalogViewedProductTable::getList(
                    array(
                        'select' => array(
                            'PRODUCT_ID',
                            'ELEMENT_ID'
                        ),
                        'filter' => array(
                            '=FUSER_ID' => $basketUserId,
                            '=SITE_ID' => SITE_ID
                        ),
                        'order' => array('DATE_VISIT' => 'DESC'),
                        'limit' => 10
                    )
                );

                while ($arFields = $viewedIterator->fetch()) {
                    $arViewed[] = $arFields['ELEMENT_ID'];
                }
            }
            if (!empty($arViewed)) { ?>
                <div class="good__recent" style="display: none">
                    <h2 class="title">Вы недавно просматривали</h2>
                    <div class="row">
                        <?
                        $iblock_id = 1;
                        $my_elements = CIBlockElement::GetList(
                            false,
                            array("IBLOCK_ID" => 4, "ID" => $arViewed),
                            false,
                            array("nPageSize" => 3),
                            array(
                                'ID',
                                'NAME',
                                'DETAIL_PAGE_URL',
                                'SHOW_COUNTER',
                                'DETAIL_PICTURE',
                                'PROPERTY_PRICE_METR',
                                "CATALOG_QUANTITY",
                                "PROPERTY_BRANDS",
                                'PREVIEW_PICTURE'
                            )
                        );
                        while ($arItem = $my_elements->GetNext()) {
                            if ($arItem['ID'] != $arResult['ID']) {
                                $product_id = $arItem['ID'];
                                if (!empty($arItem['ITEM_ID'])) {
                                    $product_id = $arItem['ITEM_ID'];
                                }
                                if (!empty($arItem['PRODUCT_ID'])) {
                                    $product_id = $arItem['PRODUCT_ID'];
                                }

                                $img2 = $arItem['PREVIEW_PICTURE'];
                                if (is_array($img2)) {
                                    $img2 = $arItem['PREVIEW_PICTURE']['ID'];
                                }

                                $file = CFile::GetPath($img2); //'width'=>370, 'height' => 504
                                if (!empty($file)) {
                                    $src = $file;
                                } else {
                                    $src = "https://spasimbo.ru/upload/medialibrary/ead/eada1cdc29f3b5421fb3652209f3b2da.jpg";
                                }

                                $sum2 = 0;
                                CModule::IncludeModule('iblock');
                                $arSelect_STARS2 = array(); //"ID", "NAME", "PREVIEW_TEXT" , "PREVIEW_PICTURE"
                                $arFilter_STARS2 = array("IBLOCK_ID" => 2, "ACTIVE" => "Y", 'PROPERTY_ID' => $arItem['ID']);
                                $res_STARS2 = CIBlockElement::GetList(
                                    array("DATE_ACTIVE_FROM" => "DESC"),
                                    $arFilter_STARS2,
                                    false,
                                    array(),
                                    $arSelect_STARS2
                                );

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
                                $prop_p300 = $arItem['PROPERTIES']['P300']['VALUE'];
                                if (empty($prop_p300)) {
                                    $prop_p300 = $arItem["PROPERTY_P300_VALUE"];
                                }
                                ?>
                                <div class="col-md-4">
                                    <div class="good__recent__item">
                                        <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>" class="card__photo">
                                            <img src="<?= resizeImageByWidth($img2, 120); ?>" loading="lazy"
                                                 alt="<?= $arItem['NAME'] ?>">
                                        </a>
                                        <div class="card__content">
                                            <? if (!empty($prop_p1)) : ?>
                                                <div class="card__vendor">Артикул: <?= $prop_p1 ?></div>
                                            <? else : ?>
                                                <br>
                                            <? endif; ?>
                                            <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>"
                                               class="card__title"><?= $arItem['NAME'] ?></a>
                                            <div class="card__review">
                                                <div class="card__rating rating">
                                                    <?$frame = $this->createFrame('dv_'.$arItem["ID"])->begin('');?>
                                                    <?$APPLICATION->IncludeComponent(
                                                        "bitrix:iblock.vote",
                                                        "element_rating_front",
                                                        Array(
                                                            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                                            "IBLOCK_ID" => $arItem["IBLOCK_ID"],
                                                            "ELEMENT_ID" =>$arItem["ID"],
                                                            "MAX_VOTE" => 5,
                                                            "VOTE_NAMES" => array(),
                                                            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                                            "CACHE_TIME" => $arParams["CACHE_TIME"],
                                                            "DISPLAY_AS_RATING" => 'vote_avg'
                                                        ),
                                                        $component, array("HIDE_ICONS" =>"Y")
                                                    );?>
                                                    <?$frame->end();?>
                                                </div>
                                            </div>
                                            <? $price = CCatalogProduct::GetOptimalPrice($arItem["ID"], 1, 'N'); ?>
                                            <div class="card__price"><?= number_format($price['RESULT_PRICE']["DISCOUNT_PRICE"], 0, '.', ' ') ?>
                                                руб
                                                <?php if ($price['RESULT_PRICE']['BASE_PRICE'] != $price['RESULT_PRICE']["DISCOUNT_PRICE"]) { ?>
                                                    <span class="card__price_old"><?= number_format($price['RESULT_PRICE']['BASE_PRICE'], 0, '.', ' ') ?> руб</span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <? }
                        } ?>
                    </div>
                </div>
            <? } ?>

            <?
            $arBrand = is_array($arResult["DISPLAY_PROPERTIES"]["BRANDS"]) ? current($arResult["DISPLAY_PROPERTIES"]["BRANDS"]["LINK_ELEMENT_VALUE"]) : [];
            $brand = $arBrand["NAME"];
            ?>
            <meta id="ecomm" data-category="<?= $arResult["CATEGORY_PATH"]; ?>" data-brand="<?= $brand; ?>"/>

            <? if (count($arResult["OFFERS"]) > 0): ?>
                <script>
                    var SCU = true;
                </script>
            <? else: ?>

            <?
            $arPrice = CCatalogProduct::GetOptimalPrice($arResult["ID"], 1, $USER->GetUserGroupArray(), $renewal);
            if (!$arPrice || count($arPrice) <= 0) {
                if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($productID, $quantity, $USER->GetUserGroupArray())) {
                    $quantity = $nearestQuantity;
                    $arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $USER->GetUserGroupArray(), $renewal);
                }
            }
            ?>

                <script>
                    var SCU = false;

                    dataLayer.push({
                        'event': 'ecomDetail',
                        'eventCategory': 'ecommerce',
                        'eventAction': 'Detail',
                        'ecommerce': {
                            'detail': {
                                'products': [{
                                    'name': '<?=$arResult["NAME"];?>',
                                    'id': '<?=$arResult["ID"];?>',
                                    'price': '<?=$arPrice["DISCOUNT_PRICE"];?>',
                                    'discount': 'NO',
                                    'brand': '<?=$brand;?>',
                                    'category': '<?=$arResult["CATEGORY_PATH"];?>'
                                }]
                            }
                        }
                    });
                </script>
            <? endif; ?>
            <?if($arParams["BUY_IN_1_CLICK"]):?>
                <script>
                    $(document).ready(function () {
                        $('.v1click_container').on('click', '.vclick_submit', function (e) {
                            var phone = $('input.v1click_phone').val();
                            if (phone != '') {
                                if (SCU) {
                                    var count = $('.input-stepper input').val();
                                    if (count > 0) {
                                        console.log('1click 1');
                                        var offerId = $('.good__info__block button.change_element.product-page-button.active').data('id');
                                        var offerName = $('.good__info__block button.change_element.product-page-button.active').data('name');
                                        var offerPrice = $('.good__info__block button.change_element.product-page-button.active').data('price');
                                        dataLayer.push({
                                            'event': 'ecomPurchase',
                                            'eventCategory': 'ecommerce',
                                            'eventAction': 'Purchase',
                                            'eventLabel': 'Заказ в один клик',
                                            'ecommerce': {
                                                'purchase': {
                                                    'actionField': {
                                                        'id': '12345',
                                                        'revenue': offerPrice.replace(/[^0-9]/g, ""),
                                                        'affiliation': 'Заказ в один клик'
                                                    },
                                                    'products': [{
                                                        'name': offerName,
                                                        'id': offerId,
                                                        'price': offerPrice.replace(/[^0-9]/g, ""),
                                                        'discount': 'NO',
                                                        'brand': '<?=$brand;?>',
                                                        'category': '<?=$arResult["CATEGORY_PATH"];?>',
                                                        'quantity': count
                                                    }
                                                    ]
                                                }
                                            }
                                        });
                                    }

                                } else {
                                    $('form[name*="V_1_CLICK"]').submit(function (event) {
                                        var count = $('.input-stepper input').val();
                                        if (count > 0) {
                                            console.log('1click 2');
                                            dataLayer.push({
                                                'event': 'ecomPurchase',
                                                'eventCategory': 'ecommerce',
                                                'eventAction': 'Purchase',
                                                'eventLabel': 'Заказ в один клик',
                                                'ecommerce': {
                                                    'purchase': {
                                                        'actionField': {
                                                            'id': '12345',
                                                            'revenue': '<?=$arPrice["DISCOUNT_PRICE"];?>',
                                                            'affiliation': 'Заказ в один клик'
                                                        },
                                                        'products': [{
                                                            'name': '<?=$arResult["NAME"];?>',
                                                            'id': '<?=$arResult["ID"];?>',
                                                            'price': '<?=$arPrice["DISCOUNT_PRICE"];?>',
                                                            'discount': 'NO',
                                                            'brand': '<?=$brand;?>',
                                                            'category': '<?=$arResult["CATEGORY_PATH"];?>',
                                                            'quantity': 1
                                                        }
                                                        ]
                                                    }
                                                }
                                            });
                                        }
                                    });
                                }
                            }
                        });
                    });
                </script>
            <?endif;?>

