<?php
$arResult["AMOUNT"] = array_reduce($arResult["JS"]['SKU'], function ($result, $item) {
    return $result + $item[0];
},0);