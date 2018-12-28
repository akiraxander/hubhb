<?php
/**
 *  サイトの基本設定
 *
 *  ディレクトリ名、ファイル名は先頭に'/'（スラッシュ）を必ず入れる
 *  後ろには'/'（スラッシュ）を入れない
 */

/** サイトのタイムゾーン */
date_default_timezone_set('Asia/Tokyo');

/** インストールディレクトリ名 */
define('SITE_DIR', str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)));

/** インストールディレクトリのパス */
define('ABSPATH', $_SERVER['DOCUMENT_ROOT'] . SITE_DIR);

/** 古いブラウザで'__DIR__'が使えない場合 */
if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
}

/**
 *  公開ファイルか判別
 *
 *  @return {boolean}
 */
function is_dist()
{
    if (defined('IS_DIST') && IS_DIST === true) {
        return true;
    } else {
        return false;
    }
}

/**
 * URIからパラメータを全部削除
 * 
 * @return {string} $url
 */
function remove_query_args($url)
{
    $url = preg_replace('/^(.+?)\?(.+?)$/', '$1', $url);
    $url = rtrim($url, '/');
    return $url;
}

/**
 *  現在のプロジェクト名を取得
 *
 *  @return {string} $project
 */
function get_project()
{
    $project = '/' . basename(__DIR__); // プロジェクトディレクトリまでのパス

    if (!is_dist()) {
        $call = preg_replace('/^' . preg_quote($project, '/') . '/', '', $_SERVER['REQUEST_URI']);
        $call = trim($call, '/') . '/';
        $call = preg_replace('/^(.*?)\/(.*?)$/', '$1', $call);
        $project = '/' . $call;
    }

    $project = preg_replace('/^' . preg_quote(SITE_DIR, '/') . '(.*?)$/', '$1', $project);

    return $project;
}
$project = get_project();

/**
 *  現在のページスラッグを返す
 *  トップの場合は空
 *
 *  @return {string} $call
 */
function get_call()
{
    global $project;

    $call = preg_replace('/^(.*?)' . preg_quote($project, '/') . '(.*?)$/', '$2', $_SERVER['REQUEST_URI']);
    $call = preg_replace('/^(.*?)' . preg_quote($project, '/') . '(.*?)$/', '$2', $call);
    $call = rtrim($call, '/');
    $call = preg_replace('/^' . preg_quote(SITE_DIR, '/') . '(.*?)$/', '$1', $call);
    
    // パラメータを全部削除
    $call = remove_query_args($call);
    
    return $call;
}
$call = get_call();

/** ファイルパーミッション（ディレクトリ） */
define('FILE_PERMISSION', 0777);

/** 公開時に除外するファイル（ディレクトリ） */
define('EXCLUDE_FILES', 'node_modules, .DS_Store');

/** 関数関係を格納しているディレクトリ */
define('INCDIR', '/_includes');
define('INC', ABSPATH . INCDIR);

/** プロジェクト毎の最新の公開ファイルがあるディレクトリ */
define('PUBDIR', '/_public');
define('PUB', ABSPATH . PUBDIR . $project);

/** プロジェクト用ディレクトリ */
define('PRODIR', '/_projects');
if (is_dist()) {
    define('PRO', ABSPATH . PRODIR);
} else {
    define('PRO', ABSPATH . PRODIR . $project);
}

/** ローカル用ディレクトリ */
if (is_dist()) {
    define('LOCDIR', '');
} else {
    define('LOCDIR', '/html');
}
define('LOC', PRO . LOCDIR);

/** 書き出し用ディレクトリ */
define('DSTDIR', '/dist');
define('DST', PRO . DSTDIR);

/** 書き出し用のディレクトリ名 */
define('OPT', DST . $project . '_' . date('Ymd'));

/** ファイル名 */
define('TRG', 'index.php');

/** サイトのHOSTを取得 */
define('SITE_HOST', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST']);

/** サイトのURLを取得 */
if (is_dist()) {
    define('SITE_URL', SITE_HOST . SITE_DIR);
} else {
    define('SITE_URL', SITE_HOST . SITE_DIR . $project);
}
