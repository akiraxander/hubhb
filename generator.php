<?php

if (!defined('SITE_DIR')) {
    define('SITE_DIR', str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)));
}
if (!defined('SITE_HOST')) {
    define('SITE_HOST', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST']);
}
if (!defined('SITE_URL')) {
    define('SITE_URL', SITE_HOST . SITE_DIR);
}
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__));
}

/**
 *  ファイルを上書きまたは作成する
 */
if (!function_exists('put_file')) {
    function put_file($file = null, $content = '')
    {
        if ($file === null) {
            return;
        }
        if (!file_exists($file)) {
            file_put_contents($file, $content);
        }
    }
}

/**
 *  .htaccessファイルの作成
 *
 *  @return {string} $str
 */
if (!function_exists('get_htaccess_data')) {
    function get_htaccess_data()
    {
        global $project;

        $base_path = (SITE_DIR === '' ? '/' : SITE_DIR);
        $host_path = $_SERVER['HTTP_HOST'] . $base_path . $project;
        
        $prodir = PRODIR;
        $_prodir = trim($prodir, '/');
        $locdir = LOCDIR;
        $_locdir = trim($locdir, '/');

        $_htaccess = <<<EOD
Options +SymLinksIfOwnerMatch

<IfModule mod_rewrite.c>
RewriteEngine on
RewriteBase {$base_path}

# index系のファイル名を削除
RewriteCond %{THE_REQUEST} ^.*/index\.(html|htm|php)
RewriteRule ^(.*)index\.(html|htm|php)$ $1 [R=301,L]

# パスの最後にスラッシュを追加
RewriteCond %{REQUEST_URI} !/$
RewriteCond %{REQUEST_URI} !\.[^/\.]+$
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .* %{REQUEST_URI}/ [R,L]

EOD;
        if(is_dist()) {

            $_htaccess .= <<<EOD

# ファイルパスの書き換え
RewriteCond %{REQUEST_URI} ^{$base_path}
RewriteCond %{REQUEST_URI} !^{$base_path}{$prodir}
RewriteCond %{REQUEST_URI} !^{$base_path}/index\.php
RewriteRule (.*)\.(.*)$ {$_prodir}/$1.$2 [R=301,L]

EOD;
        } else {

            $_htaccess .= <<<EOD

# ファイルパスの書き換え
RewriteCond %{REQUEST_URI} !^{$base_path}{$prodir}
RewriteRule ^(.+?)/(.*)\.(.*)$ {$_prodir}/$1{$locdir}/$2.$3 [L]

EOD;
        }

        $_htaccess .= <<<EOD

# 動的URLに書き換え
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$  index.php?p=$1 [L,QSA]
</IfModule>

EOD;
        $_htaccess_district = <<<EOD

# 404
ErrorDocument 404 {$base_path}/error/

# .htaccess自身にアクセスさせない
<Files ~ "^\.(htaccess|htpasswd)$">
deny from all
</Files>

# アクセス許可
Order allow,deny
Allow from all

# htmlでphp実行
AddType application/x-httpd-php .php .html
# AddHandler application/x-httpd-php .php .html

<IfModule mod_rewrite.c>
RewriteEngine on
RewriteBase {$base_path}

# gzip
RewriteCond %{HTTP:Accept-Encoding} gzip
RewriteCond %{REQUEST_FILENAME}\.gz -s
RewriteRule .+ %{REQUEST_URI}.gz
</IfModule>

#スタイルシート (.css)
<FilesMatch "\.css\.gz$">
ForceType text/css
AddEncoding x-gzip .gz
</FilesMatch>

#Javascript (.js)
<FilesMatch "\.js\.gz$">
ForceType application/x-javascript
AddEncoding x-gzip .gz
</FilesMatch>

#HTML (.html)
<FilesMatch "\.html\.gz$">
ForceType   text/html
AddEncoding x-gzip .gz
</FilesMatch>

# ETags(Configure entity tags) を無視する設定
<ifModule mod_headers.c>
Header unset ETag
</ifModule>
FileETag None

# Enable Keep-Alive を設定
<IfModule mod_headers.c>
Header set Connection keep-alive
</IfModule>

# MIME Type 追加
<IfModule mime_module>
AddType text/cache-manifest .appcache
AddType image/x-icon .ico
AddType image/svg+xml .svg
AddType application/x-font-ttf .ttf
AddType application/x-font-woff .woff
AddType application/x-font-woff2 .woff2
AddType application/x-font-opentype .otf
AddType application/vnd.ms-fontobject .eot
</IfModule>

# プロクシキャッシュの設定（画像とフォントをキャッシュ）
<IfModule mod_headers.c>
<FilesMatch "\.(ico|jpe?g|png|gif|svg|swf|pdf|ttf|woff|woff2|otf|eot)$">
Header set Cache-Control "max-age=604800, public"
</FilesMatch>
# プロキシサーバーが間違ったコンテンツを配布しないようにする
Header append Vary Accept-Encoding env=!dont-vary
</IfModule>

# ブラウザキャッシュの設定
<IfModule mod_headers.c>
<ifModule mod_expires.c>
ExpiresActive On

# キャッシュ初期化（1秒に設定）
ExpiresDefault "access plus 1 seconds"

# MIME Type ごとの設定
ExpiresByType text/css "access plus 1 weeks"
ExpiresByType text/js "access plus 1 weeks"
ExpiresByType text/javascript "access plus 1 weeks"
ExpiresByType image/gif "access plus 1 weeks"
ExpiresByType image/jpeg "access plus 1 weeks"
ExpiresByType image/png "access plus 1 weeks"
ExpiresByType image/svg+xml "access plus 1 year"
ExpiresByType application/pdf "access plus 1 weeks"
ExpiresByType application/javascript "access plus 1 weeks"
ExpiresByType application/x-javascript "access plus 1 weeks"
ExpiresByType application/x-shockwave-flash "access plus 1 weeks"
ExpiresByType application/x-font-ttf "access plus 1 year"
ExpiresByType application/x-font-woff "access plus 1 year"
ExpiresByType application/x-font-woff2 "access plus 1 year"
ExpiresByType application/x-font-opentype "access plus 1 year"
ExpiresByType application/vnd.ms-fontobject "access plus 1 year"
</IfModule>
</IfModule>

EOD;
        if (is_dist()) {
            $_htaccess .= $_htaccess_district;
        }
        return $_htaccess;
    }
}

/**
 *  robots.txtファイルの作成
 *
 *  @return {string} $str
 */
if (!function_exists('get_robots_data')) {
    function get_robots_data()
    {
        $site_url = SITE_URL;

        $str = <<<EOD
User-agent: *
Sitemap: {$site_url}/sitemap-index.xml

EOD;
        return $str;
    }
}

/**
 *  サイト一覧を取得
 *
 *  @param  $dir 作成するディレクトリの場所を指定
 *  @return {array} $sitemap
 */
if (!function_exists('get_sitemap_lists')) {
    function get_sitemap_lists($dir)
    {
        $sitemap = array();
        $count = 1;
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..' || $file == 'error') {
                    continue;
                }
                if (is_dir($dir . '/' . $file)) {
                    $sitemap = array_merge($sitemap, get_sitemap_lists($dir . '/' . $file));
                } else {
                    if (preg_match('/(index.(php|htm|html))$/', $file)) {

                        /** sitemap-index.xml */
                        if (defined('PRO')) {
                            $path = str_replace(PRO, '', $dir) . '/';
                        } else {
                            $path = str_replace(ABSPATH, '', $dir) . '/';
                        }

                        $hierarchy = substr_count($path, '/');

                        $sitemap[] = array(
                            'loc' => SITE_URL . $path,
                            'lastmod' => date('c', filemtime($dir . '/' . $file)),
                            'changefreq' => ($hierarchy === 1 ? 'daily' : 'weekly'),
                            /** sitemap-index.xmlではpriorityの値は無視される */
                            'priority' => ($hierarchy === 1 ? '1.0' : '1.0'-('0.' . $hierarchy)),
                        );
                    }
                }
                $count++;
            }

            asort($sitemap);
            closedir($dh);
        }

        return $sitemap;
    }
}

/**
 *  検索エンジン用のxmlサイトマップ
 *
 *  @param  $dir サイトマップを作成する対象ディレクトリ
 */
if (!function_exists('put_sitemap_xml')) {
    function put_sitemap_xml($dir = null)
    {
        if ($dir === null) {
            return;
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $urlset = $dom->createElement('urlset');
        $urlset->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute('xmlns:mobile', 'http://www.google.com/schemas/sitemap-mobile/1.0');
        $urlset->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        $urlset->setAttribute('xsi:schemalocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

        /** 配列を[loc]キーで再配列 */
        $arr = get_sitemap_lists($dir);
        $sort = array();
        foreach ((array) $arr as $key => $value) {
            $sort[$key] = $value['loc'];
        }
        array_multisort($sort, SORT_ASC, $arr);

        foreach ((array) $arr as $key) {
            $url = $urlset->appendChild($dom->createElement('url'));
            $url->appendChild($dom->createElement('loc', $key['loc']));
            $url->appendChild($dom->createElement('lastmod', $key['lastmod']));
            $url->appendChild($dom->createElement('changefreq', $key['changefreq']));
            $url->appendChild($dom->createElement('priority', $key['priority']));
        }
        $dom->appendChild($urlset);
        $dom->save(ABSPATH . '/sitemap-index.xml');
    }
}

/** ファイルの作成 */
if (!function_exists('is_localhost') || function_exists('is_localhost') && !is_localhost()) {
    /** .htaccess */
    $file = ABSPATH . '/.htaccess';
    $content = get_htaccess_data();
    put_file($file, $content);

    /** robots.txt */
    $file = ABSPATH . '/robots.txt';
    $content = get_robots_data();
    put_file($file, $content);

    /** sitemap-index.xml */
    put_sitemap_xml(ABSPATH);
}

/** ファイル作成後にトップページへリダイレクト */
if (!function_exists('is_localhost') || function_exists('is_localhost') && !is_localhost()) {
    header('Location: ' . SITE_URL);
    exit();
}