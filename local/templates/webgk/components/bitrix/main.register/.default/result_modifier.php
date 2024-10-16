<?php
$arResult['SHOW_FIELDS'] = array_filter($arResult['SHOW_FIELDS'], fn($field) => $field != 'LOGIN');