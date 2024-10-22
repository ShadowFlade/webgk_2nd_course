<?php
if (!function_exists('array_find')) {
    function array_find($arr, $userCompareFunc) {
        foreach ($arr as $x) {
            if (call_user_func($userCompareFunc, $x) === true)
            {
                return $x;
            }
        }
        return null;
    }
}
