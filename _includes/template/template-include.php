<?php

/**
 * headerの読み込み
 *
 * @param $path ファイル名
 */
if (!function_exists('get_content')) {
    function get_content()
    {
        $call = get_call();

        if (file_exists(get_template_directory_uri($call) . '/' . TRG)) {
            /** テンプレートの読み込み */
            require_once get_template_directory_uri($call) . '/' . TRG;
        } else {
            if (file_exists(get_template_directory_uri() . '/error/' . TRG)) {
                /** テンプレートが存在しないとき エラーページを表示 */
                require_once get_template_directory_uri() . '/error/' . TRG;
            }
        }
    }
}

/**
 * headerの読み込み
 *
 * @param $path ファイル名
 */
if (!function_exists('get_header')) {
    function get_header($path = '')
    {
        // フロントエンド用のブッファーの開始
        if (function_exists('buffer_page')) {ob_start('buffer_page');}

        // ファイル読み込み（重複する場合は最初のファイルのみ読み込み）
        require_once get_template_directory_uri() . '/header' . (empty($path) ? '' : '-' . $path) . '.php';

        // コンテンツのブッファーの開始
        if (function_exists('buffer_content')) {ob_start('buffer_content');}
    }
}

/**
 * sidebarの読み込み
 *
 * @param $path ファイル名
 */
if (!function_exists('get_sidebar')) {
    function get_sidebar($path = '')
    {
        // ファイル読み込み（重複する場合は最初のファイルのみ読み込み）
        require_once get_template_directory_uri() . '/sidebar' . (empty($path) ? '' : '-' . $path) . '.php';
    }
}

/**
 * footerの読み込み
 *
 * @param $path ファイル名
 */
if (!function_exists('get_footer')) {
    function get_footer($path = '')
    {
        // コンテンツのブッファーの開始
        if (function_exists('buffer_content')) {ob_end_flush();}

        // ファイル読み込み（重複する場合は最初のファイルのみ読み込み）
        require_once get_template_directory_uri() . '/footer' . (empty($path) ? '' : '-' . $path) . '.php';

        // フロントエンド用のブッファーの終了
        if (function_exists('buffer_page')) {ob_end_flush();}
    }
}

/**
 * ファイルの読み込み
 *
 * @param $slug テンプレート以降のパスまたは、ファイル名
 */
if (!function_exists('get_template_part')) {
    function get_template_part($slug, $name = null)
    {
        // 拡張子を取り除き、パスを作成
        $slug = preg_replace("/(.+)(\.[^.]+$)/", "$1", $slug);
        $name = (string) preg_replace("/(.+)(\.[^.]+$)/", "$1", $name);
        if ('' !== $name) {
            $template = "{$slug}-{$name}.php";
        } else {
            $template = "{$slug}.php";
        }

        // ファイル読み込み（同じファイルも重複して読み込む）
        include get_template_directory_uri() . '/' . $template;
    }
}

/**
 * ファイルの読み込み
 * 公開時にphpをそのまま実行する場合（書き出し時にphpをそのまま出力）
 *
 * @param $path 一度相対パスでトップまで戻り、指定
 */
if (!function_exists('get_compatibility_template_part')) {
    function get_compatibility_template_part($path)
    {
        $path = '/' . trim($path, '/');

        // 書き出し時に実行
        if (is_hubhb('html')) {

            $hierarchy_path = '/';
            if (isset($_GET['hierarchy']) && $_GET['hierarchy'] !== '') {
                for ($i = 0; $i < (int) $_GET['hierarchy']; $i++) {
                    $hierarchy_path .= '../';
                }
            }
            echo '<!--?php require_once dirname(__FILE__) . "' . $hierarchy_path . ltrim($path, '/') . '";?-->';
        }
        // 通常時実行
        else {
            require_once get_template_directory_uri() . preg_replace('/^[\.|\/]+/', '/', $path);
        }
    }
}
