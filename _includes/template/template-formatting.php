<?php

/**
 * サイトのオプションを設定
 */

$theme_defines = array();

if (!function_exists('set_define')) {
    function set_define($constname = '', $newvalue = '')
    {
        global $theme_defines;
        return $theme_defines[$constname] = $newvalue;
    }
}

/**
 * サイトのオプションを設定がされているか調べる関数
 *
 * @return {boolean} 値がなければ'false'を返す
 */
if (!function_exists('set_defined')) {
    function set_defined($constname = '')
    {
        global $theme_defines;

        if (isset($theme_defines[$constname]) && $theme_defines[$constname] !== '') {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * set_defineを取得する関数
 *
 * @return {boolean} 値がなければ'空'を返す
 */
if (!function_exists('get_theme_setting')) {
    function the_theme_setting($constname)
    {
        echo get_theme_setting($constname);
    }
}
if (!function_exists('get_theme_setting')) {
    function get_theme_setting($constname = null)
    {
        global $theme_defines;

        if ($constname === null) {
            return '';
        }
        if (set_defined($constname)) {
            return $theme_defines[$constname];
        } else {
            return '';
        }
    }
}

/** インクルードファイルまでのパス */
if (!function_exists('get_projects_path')) {
    function get_projects_path($scheme = '')
    {
        return PRO . $scheme;
    }
}

/** プロジェクトファイルまでのパス */
if (!function_exists('get_includes_path')) {
    function get_includes_path($scheme = '')
    {
        return INC . $scheme;
    }
}

/** ローカル（テーマ）ファイルまでのパス */
if (!function_exists('get_local_path')) {
    function get_local_path($scheme = '')
    {
        return LOC . $scheme;
    }
}

/** 書き出し先までのパス */
if (!function_exists('get_dist_path')) {
    function get_dist_path($scheme = '')
    {
        return OPT . $scheme;
    }
}

/** サイトのターゲットファイル（index.php） */
if (!function_exists('get_target_file')) {
    function get_target_file($scheme = '')
    {
        return TRG . $scheme;
    }
}

/** 除外ファイルを整形して返す */
if (!function_exists('get_perg_exclude_files')) {
    function get_perg_exclude_files()
    {
        /** 正規表現用にエスケープして返す */
        return implode('|', explode(',', preg_quote(preg_replace('/\s+?/', '', EXCLUDE_FILES), '/')));
    }
}

/**
 * ------------------------------
 * エスケープ関係
 * ------------------------------
 */

/** escape attr */
if (!function_exists('esc_attr')) {
    function esc_attr($val)
    {
        $safe_val = htmlspecialchars($val, ENT_QUOTES);
        return $safe_val;
    }
}

/** escape html */
if (!function_exists('esc_html')) {
    function esc_html($str)
    {
        $safe_str = htmlspecialchars($str, ENT_QUOTES);
        return $safe_str;
    }
}

/** escape url */
if (!function_exists('esc_url')) {
    function esc_url($url)
    {
        $safe_url = urlencode($url);
        return $safe_url;
    }
}

/** escape script */
if (!function_exists('esc_js')) {
    function esc_js($str)
    {
        $safe_str = mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');
        return $safe_str;
    }
}

/**
 * ------------------------------
 * パス関係
 * ------------------------------
 */

/**
 *  パスとパスを安全につなぐ
 */
if (!function_exists('connect_path')) {
    function connect_path($first = '', $next = '')
    {
        return rtrim($first, '/') . '/' . trim($next, '/');
    }
}

/**
 *  サイトのURL
 */
if (!function_exists('site_url')) {
    function site_url($scheme = '')
    {
        return get_site_url($scheme);
    }
}
if (!function_exists('get_site_url')) {
    function get_site_url($scheme = '')
    {
        return SITE_URL . $scheme;
    }
}

/**
 *  サイトのホームURLを取得
 *
 *  @param $scheme
 */
if (!function_exists('home_url')) {
    function home_url($scheme = '')
    {
        return get_home_url($scheme);
    }
}
if (!function_exists('get_home_url')) {
    function get_home_url($scheme = '')
    {
        if (get_theme_setting('ABSOLUTE_PATH')) {
            return get_absolute_path() . $scheme;
        } else {
            return get_relative_path() . $scheme;
        }
    }
}

/**
 *  プロジェクトのディレクトリのURI
 *
 *  @return プロジェクトのディレクトリのURI
 */
if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri($path = '')
    {
        if ($path !== '') {
            $path = trim($path, '/');
            $path = '/' . $path;
        }
        return get_local_path() . $path;
    }
}

/**
 *  プロジェクトのディレクトリまでの相対パス
 */
if (!function_exists('get_template_directory')) {
    function get_template_directory($path = '')
    {
        if ($path !== '') {
            $path = '/' . trim($path, '/');
        }
        return home_url() . $path;
    }
}

/**
 *  TOPまでの相対パス
 */
if (!function_exists('get_relative_path')) {
    function get_relative_path($call = null)
    {
        $path = './';

        if ($call === null) {
            $call = get_call();
        }

        $level = substr_count($call, '/');
        if ($level > 0) {
            for ($i = 0; $i < $level; $i++) {
                $path .= '../';
            }
        }

        return rtrim($path, '/');
    }
}

/**
 *  TOPのパス
 */
if (!function_exists('get_absolute_path')) {
    function get_absolute_path()
    {
        return get_site_url();
    }
}

/**
 *  相対パスをルートパスに変換
 */
function relative_to_root_path($path = null)
{
    global $call;
    $level = mb_substr_count($path, "../");
    $root = explode('/', get_template_directory_uri() . $call);
    for ($i = 0; $i < $level; $i++) {
        array_pop($root);
    }
    $root = implode('/', $root);
    $path = $root . "/" . ltrim(preg_replace('/(\.\.\/|\.\/)/', '', $path), '/');
    return $path;
}

/**
 * ------------------------------
 * サイト情報関係
 * ------------------------------
 */

/**
 *  サイト情報を取得
 *
 *  @return マッチする文字列を返す
 */
if (!function_exists('bloginfo')) {
    function bloginfo($show = '')
    {
        echo get_bloginfo($show);
    }
}
if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '')
    {
        $output = '';
        switch ($show) {
            case 'home':/** DEPRECATED */
            case 'siteurl':
            case 'url':
                $output = site_url();
                break;
            case 'description':
                $output = get_theme_setting('SITE_DESCRIPTION');
                break;
            case 'keywords':
                $output = get_theme_setting('SITE_KEYWORDS');
                break;
            case 'tel':
                $output = get_theme_setting('SITE_TEL');
                break;
            case 'address':
                $output = get_theme_setting('SITE_ADDRESS');
                break;
            case 'charset':
                $output = get_theme_setting('SITE_CHARSET');
                break;
            case 'name':
            default:
                $output = get_theme_setting('SITE_NAME');
                break;
        }
        return $output;
    }
}

/**
 *  ページ情報を取得
 *
 *  @todo 無理やりなのでいい感じにしたい
 *  @return マッチする文字列を返す
 */
if (!function_exists('pageinfo')) {
    function pageinfo($show = '')
    {
        echo get_pageinfo($show);
    }
}
if (!function_exists('get_pageinfo')) {
    function get_pageinfo($key = 'title', $url = null)
    {
        global $call;

        $output = '';

        /** 指定された値がURL形式の場合 */
        if ($url !== null && is_url($url)) {

            /** タグから無理やり取得 */
            if ($source = file_get_contents($url)) {

                if (preg_match('/<' . $key . '>(.*?)<\/' . $key . '>/i', $source, $result)) {
                    /** 専用のタグがある場合<title></title> */
                    $output = $result[1];
                } else if (preg_match('/<meta\s+?name=[\"|\']\s*?' . $key . '\s*?[\"|\']\s+?content=[\"|\'](.*?)[\"|\']>/i', $source, $result)) {
                    /** METAタグがある場合 */
                    $output = $result[1];
                }
            }

        } else {

            // パスを取得
            if ($url !== null) {
                if (!preg_match('/' . get_target_file() . '$/', $url)) {
                    $url = connect_path($url, get_target_file());
                }
                $url = connect_path(get_template_directory_uri(), $url);
            } else {
                $url = connect_path(get_template_directory_uri() . $call, get_target_file());
            }

            // 指定されたURLの'$title'変数を無理やり取得
            $file = file($url);
            foreach ((array) $file as $line):
                if (preg_match('/\$' . $key . '.+?[\'|\"](.*?)[\'|\"].+?$/i', $line, $matches)) {
                    $output = $matches[1];
                }
            endforeach;
        }

        return $output;
    }
}

/**
 *  サイトのタイトルを取得
 *
 *  @return サイトのタイトルを返す
 */
if (!function_exists('site_title')) {
    function site_title()
    {
        echo get_site_title();
    }
}
if (!function_exists('get_site_title')) {
    function get_site_title()
    {
        $output = '';
        if (is_front_page()) {
            if (get_the_title()) {
                $output .= get_the_title();
            } else {
                $output .= get_bloginfo('name');
            }
        } else {
            if (get_the_title()) {
                $output .= get_the_title() . get_theme_setting('SITE_TITLE_SEPARATE');
            }
            $output .= get_bloginfo('name');
        }
        return $output;
    }
}

/**
 *  現在のページのスラッグを取得
 *
 *  @return 現在のページのスラッグを返す
 */
if (!function_exists('the_slug')) {
    function the_slug()
    {
        echo get_the_slug();
    }
}
if (!function_exists('get_the_slug')) {
    function get_the_slug()
    {
        $call = get_call();

        $output = trim($call, '/');
        $output = explode('/', $output);
        $count = count($output) - 1;
        $output = $output[$count];

        /** 空の場合'home'をセット */
        if ($output === '') {
            $output = 'home';
        }

        return $output;
    }
}

/**
 *  現在のページの親スラッグを取得
 *
 *  @return 現在のページの親スラッグを返す
 */
if (!function_exists('the_parent_slug')) {
    function the_parent_slug()
    {
        echo get_the_parent_slug();
    }
}
if (!function_exists('get_the_parent_slug')) {
    function get_the_parent_slug()
    {
        $call = get_call();

        $output = trim($call, '/');
        $output = explode('/', $output);
        $output = array_shift($output);

        /** 空の場合'home'をセット */
        if ($output === '') {
            $output = 'home';
        }

        return $output;
    }
}

/**
 *  説明文を取得
 *
 *  @return 説明文を返す
 */
if (!function_exists('the_description')) {
    function the_description()
    {
        echo get_the_description();
    }
}
if (!function_exists('get_the_description')) {
    function get_the_description($url = null)
    {
        return get_pageinfo('description', $url);
    }
}

/**
 *  キーワードを取得
 *
 *  @return キーワードを返す
 */
if (!function_exists('the_keywords')) {
    function the_keywords()
    {
        $output = '';
        $output = join(',', get_the_keywords());
        $output = trim($output, ',');
        echo $output;
    }
}
if (!function_exists('get_the_keywords')) {
    function get_the_keywords($url = null)
    {
        $add = array();
        $add[] = trim(str_replace('、', ',', get_pageinfo('keywords', $url)), ',');
        $output = explode(',', get_bloginfo('keywords'));
        $output = array_merge($add, $output);
        $output = implode(',', $output);

        $output = rtrim($output, ',');
        return $output;
    }
}

/**
 *  ページのタイトルを取得
 *
 *  @return タイトルを返す、指定したURLがあればそのURLのタイトルを返す
 */
if (!function_exists('the_title')) {
    function the_title($url = null)
    {
        echo get_the_title($url);
    }
}
if (!function_exists('get_the_title')) {
    function get_the_title($url = null)
    {
        return get_pageinfo('title', $url);
    }
}

/**
 *  ページの英語タイトルを取得
 *
 *  @return 英語タイトル（頭文字だけ大文字）
 */
if (!function_exists('the_en_title')) {
    function the_en_title()
    {
        echo get_the_en_title();
    }
}
if (!function_exists('get_the_en_title')) {
    function get_the_en_title()
    {
        $output = get_the_slug();
        $output = trim($output, '/');
        $output = str_replace(array('-', '_'), array(' ', ' '), $output);
        $output = ucwords($output);
        $output = preg_replace('/^(.)/u', '<span>$1</span>', $output);
        $output = preg_replace('/\s(.)/u', ' <span>$1</span>', $output);
        return $output;
    }
}

/**
 *  現在のURIをリンク形式で取得
 *
 *  @param {string} $call
 *  @return 現在のURL、指定があるときは指定のURL
 */
if (!function_exists('the_permalink')) {
    function the_permalink($call = null)
    {
        echo get_permalink($call);
    }
}
if (!function_exists('get_permalink')) {
    function get_permalink($call = null)
    {
        if ($call === null) {
            $call = get_call();
        }
        if ($call !== '' && !preg_match('/^\//', $call)) {
            $call = '/' . $call;
        }
        return get_home_url() . $call;
    }
}

// 絶対パス
if (!function_exists('the_absolute_permalink')) {
    function the_absolute_permalink($call = null)
    {
        echo get_absolute_permalink($call);
    }
}
if (!function_exists('get_absolute_permalink')) {
    function get_absolute_permalink($call = null)
    {
        if ($call === null) {
            $call = get_call();
            if ($call !== '' && !preg_match('/^\//', $call)) {
                $call = '/' . $call;
            }
        }
        return get_site_url() . $call;
    }
}

/**
 * ------------------------------
 * フロントエンド関係
 * ------------------------------
 */

/**
 *  自動でpタグ、改行をセット
 */
if (!function_exists('autop')) {
    function autop($str = '', $xhtml = false)
    {
        if ($str == '') {
            return;
        }

        $arr = preg_split("/\r\r+/", $str, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($arr as $value) {
            $value = htmlspecialchars($value, ENT_QUOTES);
            $result .= '<p>' . nl2br($value, $xhtml) . "</p>\n";
        }
        if ($xhtml === true) {
            $result = preg_replace('/<br><\/p>/', '</p>', $result);
        }
        return $result;
    }
}

/**
 *  bodyにclassを付与
 *
 *  @param {string} $addclass
 *  @return {string} クラス名
 */
if (!function_exists('body_class')) {
    function body_class($addclass = '')
    {
        $output = '';
        $output = join(' ', get_body_class($addclass));
        $output = trim($output, ' ');
        echo ' class="' . $output . '"';
    }
}
if (!function_exists('get_body_class')) {
    function get_body_class($addclass = '')
    {
        $call = get_call();

        $output = array();

        // 追加
        if ($addclass !== '') {
            $output[] = $addclass;
        }

        // ページの属性をセット
        if (is_front_page()) {
            $output[] = 'home';
        } else {
            $output[] = 'page';
        }

        // ページノスラッグをセット
        if ($call !== '' || $call !== '/') {
            $call = trim($call, '/');
            $level = substr_count($call, '/');
            if ($level === 0) {
                $output[] = 'page-parent';
            } else {
                $output[] = 'page-child';
            }
        }

        // ページの'class'を取得してセット
        $output[] = get_pageinfo('class');

        // class配列を結合
        $output = array_merge($output, explode('/', $call));

        return array_filter($output, 'strlen');
    }
}

/**
 * enqueue_tag
 *
 * @param {string} $tag
 * @return {array} $h_tags
 */
if (!function_exists('enqueue_tag')) {
    function enqueue_tag($tag = '')
    {
        global $h_tags;
        if ($tag !== '') {
            $h_tags[] = $tag;
        }
        return $h_tags;
    }
}

/**
 * enqueue_meta
 *
 * @param {string} $type, $type_val, $content
 */
if (!function_exists('enqueue_meta')) {
    function enqueue_meta($type = 'name', $type_val = '', $content = '')
    {
        global $h_metas;
        $output = meta_output($type, $type_val, $content);
        return $h_metas[] = $output;
    }
}

/**
 * enqueue_style
 * スタイルシートを追加
 *
 * @param $path ファイルパス, $head ヘッダーかフッターを選択
 * @return {array} $h_styles, $f_styles
 */
if (!function_exists('enqueue_style')) {
    function enqueue_style($path, $head = true)
    {
        global $h_styles, $f_styles;
        $output = "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$path}\">";
        if ($head === true) {
            return $h_styles[] = $output;
        } else {
            return $f_styles[] = $output;
        }
    }
}

/**
 * enqueue_script
 * スクリプトを追加
 *
 * @param $path ファイルパス
 * @param $head ヘッダーかフッターを選択
 * @return {array} $h_scripts, $f_scripts
 */
if (!function_exists('enqueue_script')) {
    function enqueue_script($path, $head = true, $type = "")
    {
        global $h_scripts, $f_scripts;
        $type = $type ? " type=\"{$type}\"" : "";
        $output = "<script{$type} src=\"{$path}\"></script>";
        if ($head === true) {
            return $h_scripts[] = $output;
        } else {
            return $f_scripts[] = $output;
        }
    }
}

if (!function_exists('meta_output')) {
    function meta_output($type = null, $type_val = null, $content = '')
    {
        // 不備がある場合は無視
        if (
            $type === null || $type_val === null || $content === '') {
            return;
        }

        // contentを整形
        $content = preg_replace("/\n/", '', strip_tags($content));
        if ($content !== false) {
            $content = $content ? " content=\"{$content}\"" : "";
        }

        return "<meta {$type}=\"{$type_val}\"{$content}>";
    }
}

/**
 * ------------------------------
 * アクション関係
 * ------------------------------
 */

/**
 *  ファイルの作成または上書き
 *
 * @param {string} $file 作成するファイル
 * @param {string} $content 作成するファイルの内容
 */
if (!function_exists('make_file')) {
    function make_file($file = null, $content = '')
    {
        if (file_put_contents($file, $content) !== false) {
            return true;
        }
        return false;
    }
}

/**
 * ディレクトリの作成
 *
 * @param {string} $new_dir 作成するディレクトリのパス
 */
function make_dir($new_dir)
{
    if (mkdir($new_dir, FILE_PERMISSION, true)) {
        chmod($new_dir, FILE_PERMISSION);
        return true;
    }
    return false;
}

/**
 * ファイルのコピー
 *
 * @param {string} $file コピーするファイル
 * @param {string} $new_file コピーするファイル名（パス含む）
 */
function copy_file($file, $new_file)
{
    if (copy($file, $new_file)) {
        chmod($new_file, FILE_PERMISSION);
        return true;
    }
    return false;
}

/**
 *  ディレクトリのコピー（ファイルごと）
 *
 *  @param {string} $dir コピー元のディレクトリ
 *  @param {string} $new_dir コピー先のディレクトリ
 */
if (!function_exists('copy_dir')) {
    function copy_dir($dir, $new_dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        if (!is_dir($new_dir)) {
            make_dir($new_dir);
        }
        if ($dh = @opendir($dir)):
            while (($file = @readdir($dh)) !== false):
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $_file = $dir . '/' . $file;
                $_new_file = $new_dir . '/' . $file;
                if (is_dir($_file)) {
                    if (!is_dir($_new_file)) {
                        make_dir($_new_file);
                    }
                    copy_dir($_file, $_new_file);
                } else {
                    copy_file($_file, $_new_file);
                }
            endwhile;
            closedir($dh);
            return true;
        endif;
        return false;
    }
}

/**
 *  ディレクトリの削除
 *
 *  @param {string} $dir 削除するディレクトリ
 *  @param {boolean} $myselef 自身を消すか デフォルトは'true'
 */
if (!function_exists('remove_dir')) {
    function remove_dir($dir, $myselef = true)
    {
        if ($dh = @opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($dir . '/' . $file)) {
                    remove_dir($dir . '/' . $file);
                } else {
                    unlink($dir . '/' . $file);
                }
            }
            closedir($dh);
            if ($myselef) {
                rmdir($dir); // 自身を削除
            }
            return true;
        }
        return false;
    }
}

/**
 *  空のディレクトリを削除
 *
 *  @param {string} $type
 */
if (!function_exists('remove_empty_dir')) {
    function remove_empty_dir($dir)
    {
        if ($dh = @opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($dir . $file)) {
                    remove_empty_dir($dir . $file);
                }
            }
            closedir($dh);
            rmdir($dir); // 自身を削除
            return true;
        }
        return false;
    }
}

/**
 *  ファイル一覧の取得
 *    css, js, images, ogp
 *
 *  @param {string} $type
 *  @return {array} $arr 一覧を配列で返す
 */
if (!function_exists('get_files')) {
    function get_files($dir = null, $type = null)
    {
        $arr = array();
        if ($dh = @opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($dir . '/' . $file)) {
                    $arr = array_merge($arr, get_files($dir . '/' . $file, $type));
                } else {
                    if (preg_match('/(\.' . $type . ')$/', $file)) {
                        $path = str_replace(get_template_directory_uri(), get_home_url(), $dir);
                        $arr[] = $path . '/' . $file;
                    }
                }
            }
            closedir($dh);
            return $arr;
        }
        return false;
    }
}

/**
 *  ファイルに内容を一部追加
 *
 *  @param {string} $file 書き込むファイル
 *  @param {string} $text 書き込む内容
 */
if (!function_exists('add_line_file')) {
    function add_line_file($file, $text)
    {
        $array_data = file($file); // ファイル内容を一行毎に配列にする

        if (is_array($text)) {
            foreach ($text as $line):
                array_unshift($array_data, trim($line, "\n") . "\n");
            endforeach;
        } else {
            array_unshift($array_data, trim($text, "\n") . "\n");
        }
        $contents = join('', $array_data);
        if (file_put_contents($file, $contents) !== false) {
            return true;
        }
        return false;
    }
}

/**
 *  ファイルの内容を一部削除
 *
 *  @param {string} $file 書き込むファイル
 *  @param {string} $text 削除する内容
 */
if (!function_exists('remove_line_file')) {
    function remove_line_file($file, $text)
    {
        $text = preg_quote($text, '/');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\s/', '\s*?', $text);
        $contents = file_get_contents($file);
        $contents = preg_replace('/' . $text . '/ui', '', $contents);
        if (file_put_contents($file, $contents) !== false) {
            return true;
        }
        return false;
    }
}

/**
 *  ファイルの内容を一部置き換える
 *
 *  @param {string} $file 書き込むファイル
 *  @param {string} $search 削除する内容
 *  @param {string} $replace 置き換える内容
 */
if (!function_exists('replace_line_file')) {
    function replace_line_file($file, $search, $replace)
    {
        $search = preg_quote($search, '/');
        $search = preg_replace('/\s+/', ' ', $search);
        $search = preg_replace('/\s/', '\s*?', $search);
        $contents = file_get_contents($file);
        $contents = preg_replace_callback('/(' . $search . ')/ui', function ($a) use ($replace) {
            return $replace;
        }, $contents);
        if (file_put_contents($file, $contents) !== false) {
            return true;
        }
        return false;
    }
}

/**
 *  ファイルに内容を一部追加
 *
 *  @param {string} $file 書き込むファイル
 *  @param {string} $text 書き込む内容
 */
if (!function_exists('add_line_file')) {
    function add_line_file($file, $text)
    {
        $array_data = file($file);
        if (is_array($text)) {
            foreach ($text as $line):
                array_unshift($array_data, trim($line, "\n") . "\n");
            endforeach;
        } else {
            array_unshift($array_data, trim($text, "\n") . "\n");
        }
        $content = join('', $array_data);
        if (file_put_contents($file, $content) !== false) {
            return true;
        }
        return false;
    }
}

/**
 *  ファイルの内容を一部削除
 *
 *  @param {string} $file 書き込むファイル
 *  @param {string} $text 削除する内容
 */
if (!function_exists('remove_line_file')) {
    function remove_line_file($file, $text)
    {
        $text = preg_quote($text, '/');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\s/', '\s*?', $text);
        $contents = file_get_contents($file);
        $contents = preg_replace('/' . $text . '/ui', '', $contents);
        if (file_put_contents($file, $contents) !== false) {
            return true;
        }
        return false;
    }
}

/**
 *  ファイルの内容を一部置き換える
 *
 *  @param {string} $file 書き込むファイル
 *  @param {string} $search 削除する内容
 *  @param {string} $replace 置き換える内容
 */
if (!function_exists('replace_line_file')) {
    function replace_line_file($file, $search, $replace)
    {
        $search = preg_quote($search, '/');
        $search = preg_replace('/\s+/', ' ', $search);
        $search = preg_replace('/\s/', '\s*?', $search);
        $contents = file_get_contents($file);
        $contents = preg_replace_callback('/(' . $search . ')/ui', function ($a) use ($replace) {
            return $replace;
        }, $contents);
        if (file_put_contents($file, $contents) !== false) {
            return true;
        }
        return false;
    }
}

/**
 *  日本語名のファイルを削除
 *
 *  @param  $dir サイトマップを作成する対象ディレクトリ
 */
if (!function_exists('delete_ja_file')) {
    function delete_ja_file($dir)
    {
        if ($dh = @opendir($dir)) {
            while (($file = @readdir($dh)) !== false):
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $_file = $dir . '/' . $file;
                if (is_dir($_file)) {
                    delete_ja_file($_file);
                } else {
                    if (preg_match("/[一-龠]+|[ぁ-ん]+|[ァ-ヴー]+|[ａ-ｚＡ-Ｚ０-９]+/u", $file) === 1) {
                        unlink($_file);
                    }
                }
            endwhile;
            closedir($dh);
            return true;
        }
        return false;
    }
}

/**
 *  日本語名のファイルを削除
 *
 *  @param  $dir サイトマップを作成する対象ディレクトリ
 */
if (!function_exists('convert_preg_search')) {
    function convert_preg_search($str)
    {
        $array = array();
        $exclude = array();
        $str = str_replace(' |　', '', $str);
        $array = explode(',', $str);
        foreach ((array) $array as $item):
            $exclude[] = preg_quote(trim($item), '/');
        endforeach;
        $exclude = implode('|', $exclude);
        return $exclude;
    }
}
