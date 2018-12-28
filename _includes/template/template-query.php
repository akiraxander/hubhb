<?php

/**
 *  タブレット（PC以外）をチェック
 *
 *  @return {boolean}
 */
if (!function_exists('is_tablet')) {
    function is_tablet()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $is_mobile = false;
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false) {
            $is_mobile = true;
        } else {
            $is_mobile = false;
        }
        return $is_mobile;
    }
}

/**
 *  スマホをチェック
 *
 *  @return {boolean}
 */
if (!function_exists('is_mobile')) {
    function is_mobile()
    {
        $useragents = array(
            'iPhone', // iPhone
            'iPod', // iPod touch
            'Android.*Mobile', // 1.5+ Android Only mobile
            'Windows.*Phone', // Windows Phone
            'dream', // Pre 1.5 Android
            'CUPCAKE', // 1.5+ Android
            'blackberry9500', // Storm
            'blackberry9530', // Storm
            'blackberry9520', // Storm v2
            'blackberry9550', // Storm v2
            'blackberry9800', // Torch
            'webOS', // Palm Pre Experimental
            'incognito', // Other iPhone browser
            'webmate', // Other iPhone browser
        );
        $pattern = '/' . implode('|', $useragents) . '/i';
        return preg_match($pattern, $_SERVER['HTTP_USER_AGENT']);
    }
}

/**
 *  ローカル環境か判別
 *
 *  @return {boolean}
 */
if (!function_exists('is_localhost')) {
    function is_localhost()
    {
        $S_AD = $_SERVER['SERVER_ADDR'];
        $R_AD = $_SERVER['REMOTE_ADDR'];

        if (substr($S_AD, 0, mb_strrpos($S_AD, '.')) == substr($R_AD, 0, mb_strrpos($R_AD, '.'))) {
            return true;
        } else {
            return false;
        }
        
    }
}

/**
 *  公開ファイル作成時
 *
 *  @return {boolean}
 */
if (!function_exists('is_hubhb')) {
    function is_hubhb($key = null)
    {
        if ($key !== null) {
            $useragents = 'hubhb/' . $key;
        } else {
            $useragents = 'hubhb';
        }
        $pattern = '/' . preg_quote($useragents, '/') . '/i';
        return preg_match($pattern, $_SERVER['HTTP_USER_AGENT']);
    }
}

/**
 *  トップページ判別
 *
 *  @return {boolean}
 */
if (!function_exists('is_front_page')) {
    function is_front_page()
    {
        $call = get_call();

        if (empty($call)) {
            return true;
        } else {
            return false;
        }
        
    }
}

/**
 *  各ページの判別
 *
 *  @param {string} $name
 *  @return {boolean}
 */
if (!function_exists('is_page')) {
    function is_page($name = '')
    {
        $call = get_call();

        if ($name === '') {

            if (is_front_page()) {
                return false;
            } else {
                return true;
            }
            

        } else {

            $call = trim($call, '/');
            $name = trim($name, '/');

            if ($call == $name) {
                return true;
            } else {
                return false;
            }
            
            
        }
    }
}

/**
 *  親ページの判別
 *    自身を含む
 *
 *  @param {string} $name
 *  @return {boolean}
 */
if (!function_exists('is_parent')) {
    function is_parent($name = '')
    {
        $call = get_call();

        $call = trim($call, '/');
        $arr = explode('/', $call);
        $name = trim($name, '/');

        $flag = false;
        $counts = count($arr);
        if ($counts > 1) {
            for ($i = 0; $i < $counts - 1; $i++) {
                if ($name === $arr[$i]) {
                    $flag = true;
                }

            }
        }

        if ($flag === true || $call === $name) {
            return true;
        } else {
            return false;
        }
        
    }
}

/**
 *  子ページの判別
 *    自身を含まない
 *
 *  @param {string} $name
 *  @return {boolean}
 */
if (!function_exists('is_child')) {
    function is_child($name = '')
    {
        $call = get_call();

        $call = trim($call, '/');
        $arr = explode('/', $call);
        $name = trim($name, '/');

        $flag = false;
        $counts = count($arr);
        if ($counts > 1) {
            for ($i = 0; $i < $counts - 1; $i++) {
                if ($name === $arr[$i]) {
                    $flag = true;
                }

            }
        }

        if ($flag === true) {
            return true;
        } else {
            return false;
        }
        
    }
}

/**
 *  各ページの判別
 *
 *  @return {boolean}
 */
if (!function_exists('is_404')) {
    function is_404()
    {
        $call = get_call();

        if (is_page('error')) {
            return true;
        } else {
            return false;
        }
        
    }
}

/**
 *  テキストが英語か判別
 *
 *  @return {boolean}
 */
if (!function_exists('is_english')) {
    function is_english($str)
    {
        if (strlen($str) == mb_strlen($str, 'utf8')) {
            return true;
        } else {
            return false;
        }
        
    }
}

/**
 *  URL形式か判別
 *
 *  @return {boolean}
 */
if (!function_exists('is_url')) {
    function is_url($url)
    {
        if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $url)) {
            return true;
        } else {
            return false;
        }
        
    }
}

/**
 *  COOKIEが有効か判別
 *
 *  @return {boolean}
 */
if (!function_exists('is_cookie')) {
    function is_cookie()
    {
        $url = basename($_SERVER['SCRIPT_NAME']);

        if (!isset($_GET['do'])) {
            setcookie('cookiecheck', true);
            header("Location: {$url}?do=check");
        } else {
            $cookie = $_COOKIE['cookiecheck'];
            $ret = $cookie ? true : false;
            setcookie('cookiecheck', '', time() - 36);
        }
        return $ret;
    }
}

/**
 *  絶対パスかチェック
 */
if (!function_exists('is_absolute')) {
    function is_absolute($url)
    {
        return
        ($purl = parse_url($url)) !== false
        && isset($purl['scheme'])
        && isset($purl['host'])
        && filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
    }
}
