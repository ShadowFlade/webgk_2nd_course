<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

if(!empty($arResult["STORES"]) && $arParams["MAIN_TITLE"] != ''):?>
	<h4><?=$arParams["MAIN_TITLE"]?></h4>
<?endif;?>
<div class="bx_storege" id="catalog_store_amount_div">
	<?if(!empty($arResult["STORES"])):?>
        <span class="balance" id="<?=$arResult['JS']['ID']?>_<?=$arProperty['ID']?>"><?=$arResult["AMOUNT"]?></span><br />
	<?endif;?>
</div>
