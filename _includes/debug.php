<?php

/**
 * デバッグ用関数
 */
if (!function_exists('pr')) {
    function pr($arg)
    {
        print '<pre>' . "\n";
        print_r($arg);
        print '</pre>' . "\n";
    }
}
