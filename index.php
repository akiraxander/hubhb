<?php

/** エラーの表示 */
ini_set('display_errors', On);
error_reporting(E_ALL);

/** ベースとなる設定ファイルの読み込み */
require_once dirname(__FILE__) . '/load.php';

/** 関数関係のファイルを読み込み */
require_once INC . '/includes.php';

/** .htaccessの作成 */
if (!file_exists(ABSPATH . '/.htaccess')) {
    require_once ABSPATH . '/generator.php';
    $file = ABSPATH . '/.htaccess';
    $content = get_htaccess_data();
    put_file($file, $content);
}

/** 書き出し用のファイルを読み込み */
require_once INC . '/generator-html.php';

if (preg_match('/^\/(html|php)$/', $call)) {

    header('Refresh: 3; URL=./wip?output_type=' . trim($call, '/'));
    echo '<!DOCTYPE html><html><head><meta charset="' . get_bloginfo('charset') . '"><title>ファイル作成中...</title></head><body><h1>ファイル作成中...</h1><p>ファイル作成には時間がかかります。<br>終了後は完成ファイルにリダイレクトされます。</p></body></html>';

} else if (preg_match('/^\/(html|php)\/wip/', $call)) {

    header('Content-type: text/html; charset=' . get_bloginfo('charset'));
    
    $new_dir = OPT;
    $type = isset($_GET['output_type']) && $_GET['output_type'] !== '' ? $_GET['output_type'] : 'html';
    $output = new outputProject();
    $output->run($new_dir, $type);

    /** 終了 */
    register_shutdown_function(function ($new_dir) {
        header('Location: ' . str_replace(ABSPATH, SITE_HOST . '/' . basename(__DIR__), OPT) . '/' . TRG);
        exit;
    });

} else {

    if (!file_exists(get_template_directory_uri())) {

        /** テンプレートが存在せずエラーページもない時は一覧に戻る */
        echo '<!DOCTYPE html><html><head><meta charset="' . get_bloginfo('charset') . '"><title>PROJECTS</title></head><body><h1>PROJECTS</h1><ul>';
        foreach (scandir(ABSPATH . PRODIR) as $file => $value):
            if ($value == '.' || $value == '..' || $value == '.DS_Store') {
                continue;
            }
            echo '<li><a href="./' . $value . '/">' . $value . '</a></li>';
        endforeach;
        echo '</ul></body></html>';

    } else {

        /** テンプレートファイルの読み込み */
        get_content();

    }
}
