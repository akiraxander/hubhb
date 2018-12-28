<?php

/** エラーの表示 */
ini_set('display_errors', On);
error_reporting(E_ALL);

/** デフォルトの設定ファイルの読み込み */
require_once dirname(__FILE__) . '/load.php';

/** 関数関係の読み込み */
require_once INC . '/includes.php';

/** 設定ファイルの読み込み functions.phpで上書き可能 */
if (file_exists(INC . '/define.php')) {
    require_once INC . '/define.php';
}

/** .htaccessの作成 */
if (!file_exists(ABSPATH . '/.htaccess')) {

        echo '<!DOCTYPE html><html><head><meta charset="' . get_bloginfo('charset') . '"><title>' . get_bloginfo('name') . '</title></head><body>';
        echo '<form action="./generator.php">';
        echo '<h1>' . get_bloginfo('name') . '</h1><p>このサイトには.htaccessが必要です。<br>.htaccessを作成してください。</p><p><button type="submit">.htaccessを作成</a></button></body></html>';
        echo '</form>';
        echo '</body></html>';

} else {

    /** テンプレートファイルの読み込み */
    get_content();

}
exit;