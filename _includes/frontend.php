<?php

/**
 * ヘッダーに書き出し
 * <?php if (function_exists('head')) {head();}?>
 */
if (!function_exists('head')) {
    function head()
    {
        global $call;
        $output = array();

        // 基本のMETA情報
        $output[] = meta_output('charset', 'UTF-8', false);
        $output[] = meta_output('http-equiv', 'X-UA-Compatible', 'IE=edge,chrome=1');
        $output[] = meta_output('name', 'viewport', 'width=device-width, initial-scale=1');
        $output[] = meta_output('name', 'theme-color', get_theme_setting('THEME_COLOR'));

        // サイト情報
        $output[] = '<title>' . strip_tags(get_site_title()) . '</title>';
        $output[] = meta_output('name', 'keywords', get_the_keywords());
        $output[] = meta_output('name', 'description', get_the_description());
        $output[] = meta_output('name', 'author', get_bloginfo('name'));

        // Googleの検索対象外
        if (get_theme_setting('NOINDEX')) {
            $output[] = meta_output('name', 'robots', 'noindex');
        }
        // Googleの検索対象
        else {
            if (!is_404()) {
                // OGP関係の出力
                if (get_theme_setting('SITE_DOMAIN')) {
                    $output[] = '<link rel="canonical" href="' . get_absolute_permalink() . '">';
                    $output[] = meta_output('name', 'twitter:card', 'summary_large_image');
                    $output[] = meta_output('name', 'twitter:sited', get_theme_setting('TW_ACCOUNT'));
                    $output[] = meta_output('property', 'og:url', get_absolute_permalink());
                    if (file_exists(get_template_directory_uri() . '/assets/images/ogp.png')) {
                        $output[] = meta_output('property', 'og:image', get_absolute_path() . '/assets/images/ogp.png');
                    }
                }
                $output[] = meta_output('property', 'fb:app_id', get_theme_setting('FB_APPID'));
                $output[] = meta_output('property', 'og:title', get_site_title());
                $output[] = meta_output('property', 'og:description', get_the_description());
                $output[] = meta_output('property', 'og:type', 'website');
                $output[] = meta_output('property', 'og:locale', 'ja_JP');
                $output[] = meta_output('property', 'og:site_name', get_bloginfo('name'));
            }
        }

        if (isset($GLOBALS["h_metas"])) {
            $output[] = implode("\n", $GLOBALS["h_metas"]);
        }

        if (isset($GLOBALS["h_tags"])) {
            $output[] = implode("\n", $GLOBALS["h_tags"]);
        }

        if (isset($GLOBALS["h_styles"])) {
            $output[] = implode("\n", $GLOBALS["h_styles"]);
        }

        if (isset($GLOBALS["h_scripts"])) {
            $output[] = implode("\n", $GLOBALS["h_scripts"]);
        }

        /**
         *  DNSプリフェッチ用に外部リンクのドメインの配列
         *
         *  @return 一覧を配列で返す
         */
        function get_dns_lists($path)
        {
            $dns_array = array();
            $js_array = array();

            if($content = @file_get_contents($path)) {
                if (preg_match_all('((https?:|)//[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', $content, $result) !== false) {
                    foreach ((array) $result[0] as $value) {
                        /** jsファイルのみ */
                        if (preg_match('/(js)$/', preg_replace('/(.+?)\?.*$/', '$1', $value))) {
                            $url = parse_url($value);
                            $js_array[] = '//' . $url['host'];
                        }
                    }
                }
            }

            $js_array = array_unique($js_array); // 重複するものをまとめる
            $js_array = array_diff($js_array, array('http://schema.org', SITE_HOST)); // 不要なものを削除
            if (!empty($js_array)) {
                foreach ((array) $js_array as $dns):
                    $dns_array[] = '<link rel="dns-prefetch" href="' . $dns . '">';
                endforeach;
            }

            return $dns_array;
        }
        
        $arr = array();
        $arr = array_merge($arr, get_dns_lists(get_template_directory_uri() . '/header.php'));
        $arr = array_merge($arr, get_dns_lists(get_template_directory_uri() . '/sidebar.php'));
        $arr = array_merge($arr, get_dns_lists(get_template_directory_uri() . '/footer.php'));
        if(is_page(get_the_slug())) {
            $arr = array_merge($arr, get_dns_lists(get_template_directory_uri($call) . '/index.php'));
        }
        if (!empty($arr)) {
            $output[] = meta_output('http-equiv', 'x-dns-prefetch-control', 'no');
            $output = array_merge($output, $arr);
        }

        /** ファビコン */
        if (file_exists(get_template_directory_uri() . '/favicon.ico')) {
            $output[] = '<link rel="shortcut icon" href="' . home_url('/') . 'favicon.ico">';
        } else if (file_exists(get_template_directory_uri() . '/assets/favicon.ico')) {
            $output[] = '<link rel="shortcut icon" href="' . home_url('/') . 'assets/favicon.ico">';
        } else if (file_exists(get_template_directory_uri() . '/assets/images/favicon.ico')) {
            $output[] = '<link rel="shortcut icon" href="' . home_url('/') . 'assets/images/favicon.ico">';
        }

        /** デバイスチェック */
        $output[] = <<<EOD
<script>
    var html = document.getElementsByTagName("html")[0];
    var ua = navigator.userAgent.toLowerCase();
    var mobile = ((ua.indexOf("android") > 0 && ua.indexOf("mobile") > 0) || ua.indexOf("iphone") > 0 || ua.indexOf("ipod") > 0);
    var tablet = ((ua.indexOf("android") > 0 && ua.indexOf("mobile") < 0) || ua.indexOf("ipad") > 0);
    var desktop = (!mobile && !tablet);
    if(html.classList) {
        if (mobile) html.classList.add("mobile");
        if (tablet) html.classList.add("tablet");
        if (desktop) html.classList.add("desktop");
        html.classList.remove("no-js");
    }
</script>
EOD;

        /** noscript */
        $output[] = <<< EOD
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var target = document.querySelectorAll('.nojs-show');
        var targetLength = target.length;
        if (targetLength) {
            for (var i = 0; i < targetLength; ++i) {
                target[i].style.display = "none";
            }
        }
    });
</script>
EOD;
        $output[] = '<style type="text/css">.nojs-show {visibility: hidden !important}</style>';
        $output[] = '<noscript><style type="text/css">.nojs-hidden {display: none !important} .nojs-show {visibility: visible !important}</style></noscript>';
        
        if (get_theme_setting('NOINDEX') === true) {
            $output[] = '<meta name="robots" content="noindex, nofollow">';
        }

        /** サイト構造 */
        if (get_theme_setting('SITE_DOMAIN')) {
            
            $ldjson = array();
            $ldjson["@context"] = "http://schema.org";
            $ldjson["@type"] = "WebSite";
            $ldjson["name"] = get_bloginfo('name');
            $ldjson["url"] = get_bloginfo('url');
            $output[] = '<script type="application/ld+json">' . json_encode($ldjson, JSON_PRETTY_PRINT) . '</script>';

            /** 構造 */
            $ldjson = array();
            $ldjson["@context"] = "http://schema.org";
            $ldjson["@type"] = "BreadcrumbList";

            $ldjson_top["@type"] = "ListItem";
            $ldjson_top["position"] = "1";
            $ldjson_top["item"]["@id"] = get_bloginfo('url');
            $ldjson_top["item"]["@name"] = "TOP";
            $ldjson["itemListElement"][] = $ldjson_top;

            $path = '';
            $hierarchy  = array_filter(explode('/', trim($call, '/')));
            $level = count($hierarchy);
            for ($i = 1; $i <= $level; ++$i) {
                $path .= '/' . $hierarchy[$i - 1];
                $position = $i + 1;
                ${"ldjson_" . $i}["@type"] = "ListItem";
                ${"ldjson_" . $i}["position"] = "{$position}";
                ${"ldjson_" . $i}["item"]["@id"] = get_the_title($path);
                ${"ldjson_" . $i}["item"]["@name"] = get_absolute_permalink($path);
                $ldjson["itemListElement"][] = ${"ldjson_" . $i};
            }
            $output[] = '<script type="application/ld+json">' . json_encode($ldjson, JSON_PRETTY_PRINT) . '</script>';

            if(set_defined('TW_PAGE') || set_defined('FB_PAGE') || set_defined('IG_PAGE')) {
                $ldjson = array();
                $ldjson["@context"] = "http://schema.org";
                $ldjson["@type"] = "Organization";
                if(set_defined('TW_PAGE')) $ldjson["sameAs"][] = set_define('TW_PAGE');
                if(set_defined('FB_PAGE')) $ldjson["sameAs"][] = set_define('FB_PAGE');
                if(set_defined('IG_PAGE')) $ldjson["sameAs"][] = set_define('IG_PAGE');
                $ldjson["name"] = get_bloginfo('name');
                $ldjson["url"] = get_bloginfo('url') . '/#organization';
                $output[] = '<script type="application/ld+json">' . json_encode($ldjson, JSON_PRETTY_PRINT) . '</script>';
            }
        }

        /** 空の配列などを削除 */
        $output = array_filter($output, "strlen");
        $output = array_diff($output, array(null));
        $output = array_values($output);

        echo "\n" . implode("\n", $output) . "\n";
    }
}

 /**
 * フッターに書き出し
 * <?php if (function_exists('foot')) {foot();}?>
 */
if (!function_exists('foot')) {
    function foot()
    {
        $output = array();

        if (isset($GLOBALS["f_styles"])) {
            $output[] = implode("\n", $GLOBALS["f_styles"]);
        }
        if (isset($GLOBALS["f_scripts"])) {
            $output[] = implode("\n", $GLOBALS["f_scripts"]);
        }

        /** 空の配列などを削除 */
        $output = array_filter($output, "strlen");
        $output = array_diff($output, array(null));
        $output = array_values($output);

        echo "\n" . implode("\n", $output) . "\n";
    }
}
