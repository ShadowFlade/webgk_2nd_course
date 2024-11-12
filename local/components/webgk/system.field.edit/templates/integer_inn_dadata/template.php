<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$inputName = $arParams['input_name'] ?: $arParams["arUserField"]["FIELD_NAME"]
?>
<div id="main_<?= $arParams["arUserField"]["FIELD_NAME"] ?>"
     class="fields integer <?= $arParams['extra_input_class'] ?> js-dadata-inn"><?
    foreach ($arResult["VALUE"] as $res):?>
        <div class="fields integer">
        <input
                onkeypress="return /[0-9]/i.test(event.key)"
                max="12"
                type="text" list="<?= $inputName ?>"
               name="<?= $inputName ?>" <?= $arParams["arUserField"]["EDIT_IN_LIST"] != "Y" ? 'disabled="disabled"' : '' ?>
               value="<?= $res ?>">
        <datalist class="js-dadata-inn__datalist" id="<?= $inputName ?>">
            <!--        <option value="Boston">-->
            <!--        <option value="Cambridge">-->
        </datalist>
        </div><?
    endforeach; ?>
</div>
<? if ($arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] != "N"): ?>
    <input type="button" value="<?= GetMessage("USER_TYPE_PROP_ADD") ?>"
           onClick="addElement('<?= $arParams["arUserField"]["FIELD_NAME"] ?>', this)">
<? endif; ?>
