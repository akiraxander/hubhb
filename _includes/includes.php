<?php

/** デバッグ */
require_once INC . '/debug.php';

/** 関数 */
require_once INC . '/template/template-formatting.php';
require_once INC . '/template/template-query.php';
require_once INC . '/template/template-include.php';

/** 設定ファイルの読み込み functions.phpで上書き可能 */
require_once INC . '/define.php';

/** フロントエンド */
require_once INC . '/frontend.php';
require_once INC . '/buffer.php';

/** テーマのfunctions.phpの読み込み */
if (file_exists(LOC . '/functions.php')) {
    require_once LOC . '/functions.php';
}

/** 追加機能 読み込み対象：index.php */
foreach (glob(INC . '/plugin/**/{index.php}', GLOB_BRACE) as $file) {
    if(is_file($file)) {
        require_once $file;
    }
}
