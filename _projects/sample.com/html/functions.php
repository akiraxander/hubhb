<?php

// ファイル読み込み時にパラメータを付与しキャッシュをクリア
set_define('FILE_CACHE', false);

// サイト情報
// set_define('SITE_NAME', '');
// set_define('SITE_KEYWORDS', '');
// set_define('SITE_DESCRIPTION', '');

// Load files
enqueue_style(esc_attr(get_home_url('/')) . 'assets/dist/css/all.min.css');
enqueue_script(esc_attr(get_home_url('/')) . 'assets/dist/js/all.min.js', false);
