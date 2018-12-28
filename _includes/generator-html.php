<?php

/**
 * 書き出し用のCLASS
 */
if (!method_exists('outputFunc', 'read')) {
    class outputFunc
    {
        public function __construct()
        {}
        /** 公開ディレクトリの作成 */
        public function createDir($new_dir)
        {
            remove_dir($new_dir, false);
            make_dir($new_dir);
        }
        /** 根幹となるファイルをコピー */
        public function copyBasisFiles($new_dir, $type)
        {
            copy_dir(INC, $new_dir . INCDIR);
            copy_file(ABSPATH . '/load.php', $new_dir . '/load.php');
            add_line_file($new_dir . '/load.php', "<?php define('IS_DIST', true); ?>");
        }
        /** 必要ないファイルやディレクトリを削除 */
        public function deleteGarbageFiles($new_dir, $type)
        {
            delete_ja_file($new_dir); // 日本語ファイルを削除
            remove_empty_dir($new_dir); // 空のディレクトリを削除
        }
        /** メインファイルの作成 */
        public function createSiteFiles($new_dir, $type)
        {
            if($type === 'html') {
                put_htmls(LOC, $new_dir);
                copy_file(get_template_directory_uri() . '/functions.php', $new_dir . '/functions.php');
                add_line_file($new_dir . '/functions.php', '<?php require_once dirname(__FILE__) . "' . INCDIR . '/includes.php"; ?>');
                add_line_file($new_dir . '/functions.php', '<?php require_once dirname(__FILE__) . "/load.php"; ?>');
            } else {
                $new_projexts_dir = $new_dir . PRODIR;
                remove_dir($new_projexts_dir);
                make_dir($new_projexts_dir);
                put_phps(LOC, $new_projexts_dir);
                copy_file(ABSPATH . '/activate.php', $new_dir . '/index.php');
                replace_line_file($new_dir . INCDIR . '/define.php', "set_define('SITE_DOMAIN', '');", "set_define('SITE_DOMAIN', 'SITE_URL');");
            }
        }
        /** その他、必要なファイルを作成 */
        public function createLeftFiles($new_dir, $type)
        {
            make_config_file($new_dir);
            make_gzip($new_dir);
            copy_file(ABSPATH . '/generator.php', $new_dir . '/generator.php');
            add_line_file($new_dir . '/generator.php', "<?php require_once dirname(__FILE__) . '/load.php';?>");
        }
        /** 完成したファイルを公開ディレクトリにコピー */
        public function publicProject($new_dir, $type)
        {
            remove_dir(PUB, false);
            copy_dir($new_dir, PUB);
        }
    }
}

if (!method_exists('outputProject', 'read')) {
    class outputProject
    {
        protected $outputFunc;
        public function __construct()
        {
            $this->outputFunc = new outputFunc();
        }
        public function run($new_dir, $type = 'html')
        {
            $this->outputFunc->createDir($new_dir, $type);
            $this->outputFunc->copyBasisFiles($new_dir, $type);
            $this->outputFunc->createSiteFiles($new_dir, $type);
            $this->outputFunc->deleteGarbageFiles($new_dir, $type);
            $this->outputFunc->createLeftFiles($new_dir, $type);
            $this->outputFunc->publicProject($new_dir, $type);
        }
    }
}

/**
 *  サイト全体のhtmlファイルの作成
 *
 *  @param {string} $dir htmlを作成する対象ディレクトリ
 *  @param {string} $new_dir 書き出し先
 */
if (!function_exists('put_htmls')) {
    function put_htmls($dir, $new_dir)
    {
        // ヘッダーオプション
        $header_options = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'User-Agent: hubhb/html',
            ),
        );

        if (!is_dir($dir) || !is_dir($new_dir)) {
            return false;
        }
        if ($dh = @opendir($dir)) {
            while (($file = readdir($dh)) !== false):

                // 無視するディレクトリ名
                if ($file == '.' || $file == '..' || preg_match('/(' . get_perg_exclude_files() . ')/i', $file)) {
                    continue;
                }

                $_file = $dir . '/' . $file;
                $_new_file = $new_dir . '/' . $file;

                // ディレクトリの場合、書き出し先に同じディレクトリを作って再帰
                if (is_dir($_file)) {
                    if (!is_dir($_new_file)) {
                        make_dir($_new_file);
                    }
                    put_htmls($_file, $_new_file);
                } else { // ファイルの場合

                    // パスをurl形式に変換（トップページは最後にスラッシュを付与）
                    $path = str_replace(get_template_directory_uri(), '', $dir);
                    $hierarchy = mb_substr_count($path, '/');
                    $url = rtrim(SITE_URL . $path, '/');

                    // ターゲットファイルの場合
                    if (preg_match('/(' . TRG . ')$/', $file)) {

                        // 階層を書き出し先に渡す
                        $data = http_build_query(
                            array(
                                'hierarchy' => $hierarchy,
                            )
                        );
                        if ($contents = file_get_contents($url . '/?' . $data, false, stream_context_create($header_options))) {
                            $contents = preg_replace('/<\!--clear-->([\s\S]*?)<\!--\/clear-->/is', '', $contents);
                            $contents = preg_replace('/(<\!--\?php)([\s\S]*?)(\?-->)/is', '<?php$2?>', $contents);
                            $contents = file_compress($contents);

                            // ファイルを作成
                            file_put_contents($new_dir . '/' . TRG, $contents);
                        }
                    } else if (

                    // ファイル名の最初がアンダースコアのphpファイル
                    preg_match('/^(_).*\.(php)$/', $file)) {

                    // 設定ファイルを自動で読み込む書き込み
                    $hierarchy = count(explode('/', trim(str_replace(get_dist_path(), '', $new_dir), '/')));
                    $hierarchy_path = '/';
                    for ($i = 0; $i < $hierarchy; $i++) {
                        $hierarchy_path .= '../';
                    }
                    if($content = file_get_contents($_file)) {
                        file_put_contents($_new_file, $content);

                        // 関数をセットしたファイルを共通で読み込ませる
                        add_line_file($_new_file, "<?php require_once dirname(__FILE__) . '{$hierarchy_path}functions.php'; ?>\n");
                    }
                } else {
                    if (!preg_match('/\.(php)$/', $file)) {
                        copy_file($_file, $_new_file, stream_context_create($header_options));
                    }
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
 *  サイト全体のphpファイルの作成
 *
 *  @param {string} $file phpを作成する対象ディレクトリ
 *  @param {string} $new_dir 書き出し先
 */
if (!function_exists('put_phps')) {
    function put_phps($dir, $new_dir)
    {
        // ヘッダーオプション
        $header_options = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'User-Agent: hubhb/php',
            ),
        );
        if (!is_dir($dir) || !is_dir($new_dir)) {
            return false;
        }
        if ($dh = @opendir($dir)):
            while (($file = readdir($dh)) !== false):
                if ($file == '.' || $file == '..' || preg_match('/(' . get_perg_exclude_files() . ')/i', $file)) {
                    continue;
                }
                $_file = $dir . '/' . $file;
                $_new_file = $new_dir . '/' . $file;
                if (is_dir($_file)) {
                    if (!is_dir($_new_file)) {
                        make_dir($_new_file);
                    }
                    put_phps($_file, $_new_file);
                } else {
                    if ($contents = file_get_contents($_file, false, stream_context_create($header_options))) {
                        $contents = preg_replace('/(<\!--clear-->|<\!--\/clear-->)/is', '', $contents);
                        $contents = preg_replace('/(<\!--\?php)([\s\S]*?)(\?-->)/is', '', $contents);
                        file_put_contents($_new_file, $contents);
                    }
                }
            endwhile;
            closedir($dh);
            return true;
        endif;
        return false;
    }
}

/**
 *  サイトの情報をjsonファイルに書き出し
 *
 *  @param  $dir jsonファイルを作成する対象ディレクトリ
 */
if (!function_exists('make_config_file')) {
    function make_config_file($dir)
    {
        $arr = array();
        $difines = get_defined_constants(true);
        $difines = $difines['user'];

        /** いらない配列を削除 */
        unset($difines['SITE_DIR']);
        unset($difines['SITE_HOST']);
        unset($difines['SITE_URL']);
        unset($difines['ABSPATH']);
        unset($difines['INC']);
        unset($difines['PRO']);
        unset($difines['LOCDIR']);
        unset($difines['LOC']);
        unset($difines['DSTDIR']);
        unset($difines['DST']);
        unset($difines['OPT']);

        $difines = json_encode($difines);
        if (make_file($dir . '/_config.json', $difines)) {
            return true;
        }
        return false;
    }
}

/**
 *  js,cssのgzipファイルの作成
 *
 *  @param  $dir サイトマップを作成する対象ディレクトリ
 */
if (!function_exists('make_gzip')) {
    function make_gzip($dir)
    {
        if ($dh = @opendir($dir)) {
            while (($file = @readdir($dh)) !== false):
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $_file = $dir . '/' . $file;
                if (is_dir($_file)) {
                    make_gzip($_file);
                } else {
                    if (preg_match('/\.(js|css|html|htm)$/i', $file)) {
                        $code = file_get_contents($_file);
                        $gzip = gzopen($_file . '.gz', 'w9');
                        gzwrite($gzip, $code);
                        gzclose($gzip);
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
 *  thanks
 *  http://www.basiclue.com/2016/04/minify-html-javascript-css-without-plugin.html
 */
if (!method_exists('WP_HTML_Compression', 'read')) {
    class WP_HTML_Compression
    {
        // Settings
        protected $compress_css = true;
        protected $compress_js = false;
        protected $info_comment = false;
        protected $remove_comments = true;

        // Variables
        protected $html;
        public function __construct($html)
        {
            if (!empty($html)) {
                $this->parseHTML($html);
            }
        }
        public function __toString()
        {
            return $this->html;
        }
        protected function bottomComment($raw, $compressed)
        {
            $raw = strlen($raw);
            $compressed = strlen($compressed);
            $savings = ($raw - $compressed) / $raw * 100;
            $savings = round($savings, 2);
            return '<!-- ' . $savings . '% Compression Succeeded (' . $raw . ' bytes = ' . $compressed . ' bytes) -->';
        }
        protected function minifyHTML($html)
        {
            $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
            preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
            $overriding = false;
            $raw_tag = false;
            // Variable reused for output
            $html = '';
            foreach ($matches as $token) {
                $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;

                $content = $token[0];

                if ($tag === null) {
                    if (!empty($token['script'])) {
                        $strip = $this->compress_js;
                    } else if (!empty($token['style'])) {
                        $strip = $this->compress_css;
                    } else if ($content == '<!-- No Compression -->') {
                        $overriding = !$overriding;
                        // Don't print the comment
                        continue;
                    } else if ($this->remove_comments) {
                        if (!$overriding && $raw_tag != 'textarea') {
                            // Remove any HTML comments, except MSIE conditional comments
                            $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                        }
                    }
                } else {
                    if ($tag == 'pre' || $tag == 'textarea') {
                        $raw_tag = $tag;
                    } else if ($tag == '/pre' || $tag == '/textarea') {
                        $raw_tag = false;
                    } else {
                        if ($raw_tag || $overriding) {
                            $strip = false;
                        } else {
                            $strip = true;
                            // Remove any empty attributes, except:
                            // action, alt, content, src
                            $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);

                            // Remove any space before the end of self-closing XHTML tags
                            // JavaScript excluded
                            $content = str_replace(' />', '/>', $content);
                        }
                    }
                }
                if ($strip) {
                    $content = $this->removeWhiteSpace($content);
                }
                $html .= $content;
            }
            return $html;
        }

        public function parseHTML($html)
        {
            $this->html = $this->minifyHTML($html);
            if ($this->info_comment) {
                $this->html .= "\n" . $this->bottomComment($html, $this->html);
            }
        }

        protected function removeWhiteSpace($str)
        {
            $str = str_replace("\t", ' ', $str);
            $str = str_replace("\n", '', $str);
            $str = str_replace("\r", '', $str);
            while (stristr($str, '  ')) {
                $str = str_replace('  ', ' ', $str);
            }
            return $str;
        }
    }
}

/**
 *  データを圧縮
 *
 *  @param $contents 圧縮するファイル
 */
if (!function_exists('file_compress')) {
    function file_compress($contents)
    {
        return new WP_HTML_Compression($contents);
    }
}
