<?php
/**
 * サイト全体のブッファーの管理
 */
if (!function_exists('buffer_page')) {
    function buffer_page($content)
    {
        $buffer = new bufferContent();

        if (get_theme_setting('FILE_CACHE')) {
            $content = $buffer->clearFileCache($content);
        }

        /** iframe */
        if (get_theme_setting('FORMAT_IFRAME')) {
            $content = $buffer->formatIframeHtml($content);
        }

        /** pre */
        if (get_theme_setting('FORMAT_PRE')) {
            $content = $buffer->formatPreHtml($content);
        }

        /** code */
        $content = $buffer->formatCodeHtml($content);

        /** img */
        $content = $buffer->formatImgHtml($content);

        return $content;
    }
}

/**
 * コンンテンツ内のみのバッファー管理
 * header.php, footer.php内は除外
 */
if (!function_exists('buffer_content')) {
    function buffer_content($content)
    {
        $buffer = new bufferContent();

        /** lazyload */
        if (get_theme_setting('FORMAT_LAZYLOAD')) {
            $content = $buffer->formatLazyloads($content);
            $content = $buffer->formatBgLazyloads($content);
        }

        return $content;
    }
}

/**
 * サイト全体のブッファーCLASS
 */
if (!method_exists('bufferContent', 'read')) {
    class bufferContent
    {
        public function clearFileCache($content)
        {
            $target = get_theme_setting('TRG_FILE_CACHE') === '' ? 'jpg, jpeg, apng, png, gif, svg, ico, css, js' : get_theme_setting('TRG_FILE_CACHE');
            $target = convert_preg_search($target);
            $search = '/\.(' . $target . ')(\"|\'|\s)/i';
            $content = preg_replace_callback($search, '_clearFileCache', $content);
            return $content;
        }

        public function formatIframeHtml($content)
        {
            $search = '/(<.*?>)?(<iframe)(.*?>.*?<\/iframe>)(<.*?>)?/si';
            $content = preg_replace_callback($search, '_formatIframeHtml', $content);
            return $content;
        }

        public function formatPreHtml($content)
        {
            $search = '/(<pre.*?>[\n|\r\n|\r]?)(<code.*?>)(.*?)(<\/code>)([\n|\r\n|\r]?<\/pre>)/si';
            $content = preg_replace_callback($search, '_formatPreHtml', $content);
            return $content;
        }

        public function formatCodeHtml($content)
        {
            $search = '/(<code.*?>)(.*?)(<\/code>)/si';
            $content = preg_replace_callback($search, '_formatCodeHtml', $content);
            return $content;
        }

        public function formatImgHtml($content)
        {
            $search = '/(<img)(.*?)(>)/si';
            $content = preg_replace_callback($search, '_formatImgHtml', $content);
            return $content;
        }

        public function formatLazyloads($content)
        {
            $search = '/(<(img|source|iframe))(.*?)(>)/si';
            $content = preg_replace_callback($search, '_formatLazyloads', $content);
            return $content;
        }

        public function formatBgLazyloads($content)
        {
            $search = '/(<(div|p|a|span|li))([^>]+)(background-image)(.+?url\s?\([\"|\']?)(.+?)([\"|\']?\s?\)\s?;)(.+?>)/si';
            $content = preg_replace_callback($search, '_formatBgLazyloads', $content);
            return $content;
        }
    }
}

/**
 * ファイル関係のキャッシュをクリア
 * $search = '/\.(jpe?g|a?png|gif|svg|ico|css|js)(\"|\'|\s)/i';
 */
function _clearFileCache($m)
{
    return ".{$m[1]}?cache=" . date('Ymds') . $m[2];
}

/**
 * <iframe></iframe>を整形
 * <div class="iframe-container"><div class="iframe-wrapper"><iframe></iframe></div></div>
 * $search = '/(<.*?>)?(<iframe)(.*?>.*?<\/iframe>)(<.*?>)?/si';
 */
function _formatIframeHtml($m)
{
    $exc_iframe_wrap_html = get_theme_setting('EXC_IFRAME_WRAP_HTML') === '' ? 'noscript' : get_theme_setting('EXC_IFRAME_WRAP_HTML');
    $exc_iframe_wrap_html = convert_preg_search($exc_iframe_wrap_html);

    if (preg_match('/' . $exc_iframe_wrap_html . '/', $m[1]) && preg_match('/' . $exc_iframe_wrap_html . '/', $m[4])) {
        return $m[0];
    }
    $iframe_rate_wide = "4:3";
    if (get_theme_setting('IFRAME_RATE_WIDE')) {
        $iframe_rate_wide = "16:9";
    }
    return "<div class=\"iframe-container\"><div class=\"iframe-wrapper\">{$m[2]} data-screen-rate=\"{$iframe_rate_wide}\"{$m[3]}</div></div>";
}

/**
 * <pre></pre>を整形
 * <div class="pre-container"><div class="pre-wrapper"><pre></pre></div></div>
 * $search = '/(<pre.*?>[\n|\r\n|\r]?)(<code.*?>)(.*?)(<\/code>)([\n|\r\n|\r]?<\/pre>)/si';
 */
function _formatPreHtml($m)
{
    $pre = "{$m[3]}";

    // コメントをハイライトを実行
    if (get_theme_setting('HIGHLIGHT_PRE')) {
        $rows = "";
        $comment = false;
        foreach ((array) explode("\n", trim("{$pre}", "\n")) as $row):

            // シングルコメントの置き換え
            if (preg_match('/(\/\/)/', $row)) {
                $rows .= preg_replace('/(\/\/)/', '###singlecomment%%%', $row) . "\n";

                // マルチコメントの置き換え
            } else {
                if (preg_match('/\/\*|<!--/', $row)) {
                    $comment = true;
                    $rows .= preg_replace('/(\/\*|<!--)/', '###startcomment%%%$1', $row) . "\n";
                } else {
                    if (preg_match('/\*\/|-->/', $row)) {
                        $comment = false;
                        $rows .= preg_replace('/(\*\/|-->)/', '$1###endcomment%%%', $row) . "\n";
                    } else {
                        if ($comment) {
                            $rows .= "###betweencomment%%%{$row}\n";
                        } else {
                            $rows .= "{$row}\n";
                        }
                    }

                }
            }

        endforeach;
        $pre = $rows;
    }

    // 一行毎にtable-row
    if (get_theme_setting('NUMBERING_PRE')) {
        $rows = explode("\n", trim("{$pre}", "\n"));
        $table = '';
        $num = 1;
        foreach ((array) $rows as $row):
            $row = "{$m[2]}{$row}{$m[4]}";
            $table .= "<tr><th>{$num}</th><td>{$row}</td></tr>";
            ++$num;
        endforeach;
        $pre = "<table class=\"table-pre\">{$table}</table>";

    } else {
        $pre = "{$m[2]}{$pre}{$m[4]}";
    }

    return "<div class=\"pre-container\"><div class=\"pre-wrapper\">{$m[1]}{$pre}{$m[5]}</div></div>";
}

/**
 * <pre></pre>をエンティティー
 * $search = '/(<code.*?>)(.*?)(<\/code>)/si';
 */
function _formatCodeHtml($m)
{
    $code = $m[2];
    $code = htmlspecialchars($code, ENT_QUOTES);

    if (get_theme_setting('HIGHLIGHT_PRE')) {
        $code = highlight($code);
    }

    return "{$m[1]}{$code}{$m[3]}";
}

function highlight($keywords)
{
    // この時点で'$keywords'エンティティはされている

    $symbols_1 = array('&', '|', '<', '>', '.', ',', '=', '-', '+', '/', '*', ':', ';', '!', '?');
    $symbols_2 = array('(', ')', '[', ']', '{', '}');
    $symbols = array_merge($symbols_1, $symbols_2);
    $datas = array('true', 'false', 'array', 'integer', 'int', 'boolean', 'float', 'string', 'null', 'continue', 'this', 'function', 'use');
    $funcs = array('else', 'endif', 'if', 'endforeach', 'foreach', 'endfor', 'for', 'endwhile', 'while', 'return');
    $charas = array('as');
    $html4s = array('html', 'head', 'body', 'title', 'isindex', 'base', 'meta', 'link', 'script', 'hn', 'hr', 'br', 'p', 'center', 'div', 'pre', 'blockquote', 'address', 'noscript', 'font', 'basefont', 'i', 'tt', 'b', 'u', 'strike', 'big', 'small', 'sub', 'sup', 'em', 'strong', 'code', 'samp', 'kbd', 'var', 'cite', 'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'table', 'tr', 'th', 'td', 'caption', 'a', 'img', 'map', 'area', 'form', 'input', 'select', 'option', 'textarea', 'applet', 'param', 'frameset', 'frame', 'noframes');
    $html5s = array('article', 'aside', 'audio', 'bdi', 'canvas', 'datalist', 'embed', 'figcaption', 'figure', 'footer', 'header', 'keygen', 'main', 'mark', 'meter', 'nav', 'output', 'progress', 'rb', 'rp', 'rt', 'rtc', 'ruby', 'section', 'source', 'time', 'track', 'video', 'wbr', 'details', 'menu', 'menuitem', 'picture', 'summary');
    $htmls = array_merge($html4s, $html5s);
    $csss = array("font", "font-family", "font-size", "font-weight", "font-style", "font-variant", "font-size-adjust", "font-stretch", "font-effect", "font-emphasize", "font-emphasize-position", "font-emphasize-style", "font-smooth", "line-height", "position", "z-index", "top", "right", "bottom", "left", "display", "visibility", "float", "clear", "overflow", "overflow-x", "overflow-y", "-ms-overflow-x", "-ms-overflow-y", "clip", "zoom", "flex-direction", "flex-order", "flex-pack", "flex-align", "-webkit-box-sizing", "-moz-box-sizing", "box-sizing", "width", "min-width", "max-width", "height", "min-height", "max-height", "margin", "margin-top", "margin-right", "margin-bottom", "margin-left", "padding", "padding-top", "padding-right", "padding-bottom", "padding-left", "table-layout", "empty-cells", "caption-side", "border-spacing", "border-collapse", "list-style", "list-style-position", "list-style-type", "list-style-image", "content", "quotes", "counter-reset", "counter-increment", "resize", "cursor", "-webkit-user-select", "-moz-user-select", "-ms-user-select", "user-select", "nav-index", "nav-up", "nav-right", "nav-down", "nav-left", "-webkit-transition", "-moz-transition", "-ms-transition", "-o-transition", "transition", "-webkit-transition-delay", "-moz-transition-delay", "-ms-transition-delay", "-o-transition-delay", "transition-delay", "-webkit-transition-timing-function", "-moz-transition-timing-function", "-ms-transition-timing-function", "-o-transition-timing-function", "transition-timing-function", "-webkit-transition-duration", "-moz-transition-duration", "-ms-transition-duration", "-o-transition-duration", "transition-duration", "-webkit-transition-property", "-moz-transition-property", "-ms-transition-property", "-o-transition-property", "transition-property", "-webkit-transform", "-moz-transform", "-ms-transform", "-o-transform", "transform", "-webkit-transform-origin", "-moz-transform-origin", "-ms-transform-origin", "-o-transform-origin", "transform-origin", "-webkit-animation", "-moz-animation", "-ms-animation", "-o-animation", "animation", "-webkit-animation-name", "-moz-animation-name", "-ms-animation-name", "-o-animation-name", "animation-name", "-webkit-animation-duration", "-moz-animation-duration", "-ms-animation-duration", "-o-animation-duration", "animation-duration", "-webkit-animation-play-state", "-moz-animation-play-state", "-ms-animation-play-state", "-o-animation-play-state", "animation-play-state", "-webkit-animation-timing-function", "-moz-animation-timing-function", "-ms-animation-timing-function", "-o-animation-timing-function", "animation-timing-function", "-webkit-animation-delay", "-moz-animation-delay", "-ms-animation-delay", "-o-animation-delay", "animation-delay", "-webkit-animation-iteration-count", "-moz-animation-iteration-count", "-ms-animation-iteration-count", "-o-animation-iteration-count", "animation-iteration-count", "-webkit-animation-direction", "-moz-animation-direction", "-ms-animation-direction", "-o-animation-direction", "animation-direction", "text-align", "-webkit-text-align-last", "-moz-text-align-last", "-ms-text-align-last", "text-align-last", "vertical-align", "white-space", "text-decoration", "text-emphasis", "text-emphasis-color", "text-emphasis-style", "text-emphasis-position", "text-indent", "-ms-text-justify", "text-justify", "letter-spacing", "word-spacing", "-ms-writing-mode", "text-outline", "text-transform", "text-wrap", "text-overflow", "-ms-text-overflow", "text-overflow-ellipsis", "text-overflow-mode", "-ms-word-wrap", "word-wrap", "word-break", "-ms-word-break", "-moz-tab-size", "-o-tab-size", "tab-size", "-webkit-hyphens", "-moz-hyphens", "hyphens", "pointer-events", "opacity", "filter:progid:DXImageTransform.Microsoft.Alpha(Opacity", "-ms-filter:\\'progid:DXImageTransform.Microsoft.Alpha", "-ms-interpolation-mode", "color", "border", "border-width", "border-style", "border-color", "border-top", "border-top-width", "border-top-style", "border-top-color", "border-right", "border-right-width", "border-right-style", "border-right-color", "border-bottom", "border-bottom-width", "border-bottom-style", "border-bottom-color", "border-left", "border-left-width", "border-left-style", "border-left-color", "-webkit-border-radius", "-moz-border-radius", "border-radius", "-webkit-border-top-left-radius", "-moz-border-radius-topleft", "border-top-left-radius", "-webkit-border-top-right-radius", "-moz-border-radius-topright", "border-top-right-radius", "-webkit-border-bottom-right-radius", "-moz-border-radius-bottomright", "border-bottom-right-radius", "-webkit-border-bottom-left-radius", "-moz-border-radius-bottomleft", "border-bottom-left-radius", "-webkit-border-image", "-moz-border-image", "-o-border-image", "border-image", "-webkit-border-image-source", "-moz-border-image-source", "-o-border-image-source", "border-image-source", "-webkit-border-image-slice", "-moz-border-image-slice", "-o-border-image-slice", "border-image-slice", "-webkit-border-image-width", "-moz-border-image-width", "-o-border-image-width", "border-image-width", "-webkit-border-image-outset", "-moz-border-image-outset", "-o-border-image-outset", "border-image-outset", "-webkit-border-image-repeat", "-moz-border-image-repeat", "-o-border-image-repeat", "border-image-repeat", "outline", "outline-width", "outline-style", "outline-color", "outline-offset", "background", "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader", "background-color", "background-image", "background-repeat", "background-attachment", "background-position", "background-position-x", "-ms-background-position-x", "background-position-y", "-ms-background-position-y", "-webkit-background-clip", "-moz-background-clip", "background-clip", "background-origin", "-webkit-background-size", "-moz-background-size", "-o-background-size", "background-size", "box-decoration-break", "-webkit-box-shadow", "-moz-box-shadow", "box-shadow", "filter:progid:DXImageTransform.Microsoft.gradient", "-ms-filter:\\'progid:DXImageTransform.Microsoft.gradient", "text-shadow");

    // エスケープ文字を置き換え
    $keywords = preg_replace('/\\\(' . preg_quote('&quot;') . ')/i', '###escapequote%%%', $keywords);
    $keywords = preg_replace('/\\\(' . preg_quote('&#039;') . '|' . preg_quote("&apos;") . ')/i', '###escapeapos%%%', $keywords);
    $keywords = preg_replace('/\\\([a-zA-Z])/', '###escape$1%%%', $keywords);

    // クオートを一旦置き換え
    $quotes = array("'", '"');
    $requotes = array("singlequote", 'doublequote');
    $keywords = str_replace($quotes, $requotes, $keywords);
    $count = 0;
    foreach ((array) $quotes as $quote):
        $search = htmlspecialchars($quote, ENT_QUOTES);
        $key = array_search($quote, $quotes);
        $keywords = str_replace($search, '###' . $requotes[$key] . '%%%', $keywords);
        $count++;
    endforeach;

    // クオートが入れ子の場合（配列とか）
    $keywords = str_replace('###singlequote%%%###doublequote%%%###singlequote%%%', '###singlequotenest%%%', $keywords);
    $keywords = str_replace('###doublequote%%%###singlequote%%%###doublequote%%%', '###doublequotenest%%%', $keywords);

    // 記号を一旦置き換え
    $count = 0;
    foreach ((array) $symbols as $target):
        $search = htmlspecialchars($target, ENT_QUOTES);
        if (

        !preg_match('/([a-zA-Z]+?)' . preg_quote($search, '/') . '([a-zA-Z]+?)/i', $keywords)
    ) {
            $keywords = preg_replace('/' . preg_quote($search, '/') . '/i', '###' . $count . '%%%', $keywords);
        }
        $count++;
    endforeach;

    // スペースをブロックで囲む
    $keywords = preg_replace('/(\s+)/', '<span class="precode-space">$1</span>', $keywords);

    // 記号をハイライト
    foreach ((array) $symbols as $target):
        $key = array_search($target, $symbols);
        $key = "###{$key}%%%";
        $keywords = str_replace($key, '<span class="precode-keyword">' . $key . '</span>', $keywords);
    endforeach;

    // 文字列をハイライト
    $count = 0;
    foreach ($quotes as $quote):
        $key = '###' . $requotes[$count] . '%%%';
        $keywords = preg_replace_callback('/(' . preg_quote($key) . '.*?' . preg_quote($key) . ')/', function ($match) {
            $strings = $match[0];
            $strings = strip_tags($strings);
            return "<span class=\"precode-string\">{$strings}</span>";
        }, $keywords);
        $count++;
    endforeach;

    // 置き換えた記号を戻す
    $count = 0;
    foreach ((array) $symbols as $target):
        $search = htmlspecialchars($target, ENT_QUOTES);
        $keywords = str_replace('###' . $count . '%%%', $search, $keywords);
        $count++;
    endforeach;

    // 置き換えたクオートを戻す
    $count = 0;
    foreach ((array) $quotes as $quote):
        $search = htmlspecialchars($quote, ENT_QUOTES);
        $key = array_search($quote, $quotes);
        $keywords = str_replace('###' . $requotes[$key] . '%%%', $search, $keywords);
        $count++;
    endforeach;

    // 置き換えた入れ子のクオートを戻す
    $keywords = str_replace('###singlequotenest%%%', "<span class=\"precode-string\">" . htmlspecialchars("'\"'", ENT_QUOTES) . "</span>", $keywords);
    $keywords = str_replace('###doublequotenest%%%', "<span class=\"precode-string\">" . htmlspecialchars("\"'\"", ENT_QUOTES) . "</span>", $keywords);

    // 数字の関数をハイライト
    $keywords = preg_replace_callback('/(>|\s)([0-9.,]+?)(<|\s)/i', function ($match) {
        $strings = $match[2];
        $strings = strip_tags($strings);
        return "{$match[1]}<span class=\"precode-num\">{$strings}</span>{$match[3]}";
    }, $keywords);

    // 関数をハイライト '/(>|^|\s)([a-zA-Z_]+)(<|$|\s)/'
    $keywords = preg_replace_callback('/(^|>)([a-zA-Z_-]+?)($|<)/', function ($match) use ($datas, $funcs, $charas, $htmls, $csss) {
        $strings = $match[2];
        $strings = (string) strip_tags($strings);
        if (in_array($strings, $funcs)) {
            return "{$match[1]}<span class=\"precode-query\">{$strings}</span>{$match[3]}";
        } else if (in_array($strings, $datas)) {
            return "{$match[1]}<span class=\"precode-data\">{$strings}</span>{$match[3]}";
        } else if (in_array($strings, $charas)) {
            return "{$match[1]}<span class=\"precode-keyword\">{$strings}</span>{$match[3]}";
        } else if (in_array($strings, $htmls)) {
            return "{$match[1]}<span class=\"precode-html\">{$strings}</span>{$match[3]}";
        } else if (in_array($strings, $csss)) {
            return "{$match[1]}<span class=\"precode-prop\">{$strings}</span>{$match[3]}";
        } else {
            return "{$match[1]}<span class=\"precode-func\">{$strings}</span>{$match[3]}";
        }
    }, $keywords);

    // 置き換えたエスケープ文字を戻す
    $keywords = str_replace('###escapequote%%%', '<span class="precode-escape">\&quot;</span>', $keywords);
    $keywords = str_replace('###escapeapos%%%', '<span class="precode-escape">\&#039;</span>', $keywords);
    $keywords = preg_replace('/###escape([a-zA-Z])%%%/', '<span class="precode-escape">\\\$1</span>', $keywords);

    // 置き換えたシングルコメントを戻す
    $keywords = preg_replace_callback('/###singlecomment%%%(.*?)$/mi', function ($match) {
        $strings = $match[1];
        $strings = strip_tags($strings);
        return "<span class=\"precode-comment\">//{$strings}</span>";
    }, $keywords);

    // 置き換えたマルチコメントを戻す
    $keywords = preg_replace_callback('/###(start|between)comment%%%(.*?)$/mi', function ($match) {
        $strings = $match[2];
        $strings = strip_tags($strings);
        return "<span class=\"precode-comment\">{$strings}</span>";
    }, $keywords);

    $keywords = preg_replace_callback('/^(.*?)###(end)comment%%%/mi', function ($match) {
        $strings = $match[1];
        $strings = strip_tags($strings);
        return "<span class=\"precode-comment\">{$strings}</span>";
    }, $keywords);

    // ファイルキャッシュされたものを解除
    $target = get_theme_setting('TRG_FILE_CACHE') === '' ? 'jpg, jpeg, apng, png, gif, svg, ico, css, js' : get_theme_setting('TRG_FILE_CACHE');
    $target = convert_preg_search($target);
    $search = '/\.(' . $target . ')(\?cache=[0-9]+)/i';
    $keywords = preg_replace_callback($search, function ($match) {
        return ".{$match[1]}";
    }
        , $keywords);

    return $keywords;
}

/**
 * <img>にサイズなどの属性をセット
 * $search = '/(<img.*?>)/si';
 */
function _formatImgHtml($m)
{
    $img = "{$m[2]}";
    $ratio = 0;
    $wrap_img = false;
    
    $ratio_target_class = get_theme_setting('RATIO_IMG_CLASS') ? get_theme_setting('RATIO_IMG_CLASS') : 'ratio-img';
    if (preg_match('/[\"|\'|\s]' . $ratio_target_class . '[\"|\'|\s]/', $img)) {
        $wrap_img = true;
    }

    // width, height属性
    if (preg_match('/src\s?=\s?[\"|\'](.+?)\.(jpe?g|a?png|gif|svg)[\?|\"|\']/', $img, $match)) {

        $src = $match[1] . "." . $match[2];
        if ($match[2] === 'svg') {

            if ($svg = file_get_contents(relative_to_root_path($src))) {
                $svg = simplexml_load_string($svg);
                $svg = $svg->attributes();
                $width = $svg->width;
                $height = $svg->height;
                if ($width && !preg_match('/\swidth\s?=\s?/', $img)) {
                    $img .= " width=\"{$width}\"";
                }
                // height属性
                if ($height && !preg_match('/\sheight\s?=\s?/', $img)) {
                    $img .= " height=\"{$height}\"";
                }
            }

            if ($wrap_img) {
                $ratio = $height / $width * 100;
            }

        } else {

            $src1x = $src;
            $src2x = $match[1] . "@2x." . $match[2];
            $src3x = $match[1] . "@3x." . $match[2];
            if ($size = getimagesize(relative_to_root_path($src))) {
                list($width, $height, $type, $attr) = $size;
                // width属性
                if (!preg_match('/\swidth\s?=\s?/', $img)) {
                    $img .= " width=\"{$width}\"";
                }
                // height属性
                if (!preg_match('/\sheight\s?=\s?/', $img)) {
                    $img .= " height=\"{$height}\"";
                }

                if ($wrap_img) {
                    $ratio = $height / $width * 100;
                }
            }

            // srcset属性
            $srcset = array();
            if (!preg_match('/\ssrcset\s?=\s?/', $img)) {
                if (file_exists(relative_to_root_path($src2x))) {
                    $srcset[] = "{$src2x} 2x";
                }
                if (file_exists(relative_to_root_path($src3x))) {
                    $srcset[] = "{$src3x} 3x";
                }
            }
            if (!empty($srcset)) {
                $srcset = implode(',', array_merge("{$src1x} 1x", $srcset));
                $img .= " srcset=\"{$srcset}\"";
            }
        }

    }

    // alt属性
    if (!preg_match('/(\salt\s?=\s?[\"|\'].*?[\"|\'])/i', $img, $alt)) {
        $img .= " alt=\"\"";
    }

    if($ratio > 0) {
        return "<div class=\"ratio-wrapper\" style=\"padding-bottom: {$ratio}%;\">{$m[1]}{$img}{$m[3]}</div>";
    } else {
        return "{$m[1]}{$img}{$m[3]}";
    }
}

/**
 * Lazyload用に整形
 * $search = '/(<(img|source|iframe))(.*?)(>)/si';
 */
function _formatLazyloads($m)
{
    $noscript = '';
    $src_default = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    $lazyload = $m[3];
    $lazyload_class = get_theme_setting('ADD_LAZYLOAD_CLASS') === '' ? 'lazyload' : get_theme_setting('ADD_LAZYLOAD_CLASS');

    $exclude = get_theme_setting('EXC_LAZYLOAD_CLASS') === '' ? 'no-lazy, no-lazyload' : get_theme_setting('EXC_LAZYLOAD_CLASS');
    $exclude = convert_preg_search($exclude);

    if (preg_match('/[\"|\'|\s](' . $exclude . ')[\"|\'|\s]/i', $lazyload)) {
        return $m[0];
    }

    // src属性
    if (!preg_match('/\sdata-src\s?=\s?/', $lazyload)) {
        $lazyload = preg_replace('/\s(src\s?=\s?)/', ' data-$1', $lazyload);
        if ($m[2] === 'img') {
            $lazyload .= " src=\"{$src_default}\"";
        }
    }

    // class属性
    if (!preg_match('/[\s|\"|\']' . $lazyload_class . '[\s|\"|\']/', $lazyload)) {
        if (preg_match('/\sclass=[\"|\'](.*?)/', $lazyload)) {
            $lazyload = preg_replace('/(\sclass=[\"|\'])(.*?[\"|\'])/', '$1' . $lazyload_class . ' $2', $lazyload);
        } else {
            $lazyload .= " class=\"{$lazyload_class}\"";
        }
    }

    // <noscript></noscript>
    if ($m[2] === 'img') {
        $noscript = "<noscript>{$m[0]}</noscript>";
    } else {
        $noscript = "<noscript>{$m[0]}</$m[2]></noscript>";
    }

    return "{$m[1]}{$lazyload}{$m[4]}{$noscript}";
}

/**
 * Lazyload用に整形（背景）
 * $search = '/(<(div|p|a|span|li))([^>]+)(background-image)(.+?url\s?\([\"|\']?)(.+?)([\"|\']?\s?\)\s?;)(.+?>)/si';
 */
function _formatBgLazyloads($m)
{
    $noscript = '';
    $lazyload = "{$m[1]} data-src=\"{$m[6]}\"{$m[3]}{$m[8]}";
    $lazyload_class = get_theme_setting('ADD_LAZYLOAD_CLASS') === '' ? 'lazyload' : get_theme_setting('ADD_LAZYLOAD_CLASS');

    $exclude = get_theme_setting('EXC_LAZYLOAD_CLASS') === '' ? 'no-lazy, no-lazyload' : get_theme_setting('EXC_LAZYLOAD_CLASS');
    $exclude = convert_preg_search($exclude);

    if (preg_match('/[\"|\'|\s](' . $exclude . ')[\"|\'|\s]/i', $m[0])) {
        return $m[0];
    }

    // class属性
    if (!preg_match('/[\s|\"|\']lazyload[\s|\"|\']/', $lazyload)) {
        if (preg_match('/\sclass=[\"|\'](.*?)/', $lazyload)) {
            $lazyload = preg_replace('/(\sclass=[\"|\'])(.*?[\"|\'])/', '$1' . $lazyload_class . ' $2', $lazyload);
        } else {
            $lazyload .= " class=\"{$lazyload_class}\"";
        }
    }

    // style属性（空の場合は削除）
    $lazyload = preg_replace('/\sstyle\s?=\s?[\"|\'](|\s*?)[\"|\']/', '', $lazyload);

    return "{$lazyload}";
}
