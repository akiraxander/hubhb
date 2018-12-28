<?php

set_define('SITE_CHARSET', 'UTF-8');

/**
 * 公開先のドメイン名
 * 設定するとHTMLで書き出した場合にOGPタグなどが自動で設定される
 */
set_define('SITE_DOMAIN', '');

/** サイト名 */
set_define('SITE_NAME', 'functions.phpににサイト名をセットしてください');

/** キーワード */
set_define('SITE_KEYWORDS', '');

/** 説明文 */
set_define('SITE_DESCRIPTION', 'functions.phpに説明文をセットしてください');

/** <title></title>のページタイトルとサイト名のセパレート */
set_define('SITE_TITLE_SEPARATE', '｜');

/** METAタグのSNS関係を追加するか */

// twitter
set_define('TW_PAGE', '');
set_define('TW_ACCOUNT', '');

// facebook
set_define('FB_PAGE', '');
set_define('FB_APPID', '');

// Instagram
set_define('IG_PAGE', '');

/** METAタグのNOINDEXを追加するか */
set_define('NOINDEX', false);

/** テーマカラーを設定（アンドロイドのみ有効） */
set_define('THEME_COLOR', '');

/** 絶対パスに変更する場合 */
set_define('ABSOLUTE_PATH', false);

/** 画像やスタイルシートなどのファイルの最後にパラメータをつけてキャッシュをクリアするか */
set_define('FILE_CACHE', true);
set_define('TRG_FILE_CACHE', 'jpg, jpeg, apng, png, gif, svg, ico, css, js'); // 対象にするファイルの拡張子名

/** 画像を比率（padding-bottom）をセットしたdivで囲むIMG要素のCLASS名 */
set_define('RATIO_IMG_CLASS', 'ratio-img');

/** 画像遅延ロードを使用するか（自動でメディアファイルタグをlazyload用に置き換え） */
set_define('FORMAT_LAZYLOAD', true);
set_define('ADD_LAZYLOAD_CLASS', 'lazyload'); // 自動で追加されるCLASS名
set_define('EXC_LAZYLOAD_CLASS', 'no-lazy, nolazy, no-lazyload, nolazyload'); // 除外する場合のCLASS名

/** IFRAMEタグを整形 = <div class="iframe-container"><div class="iframe-wrapper"><iframe></iframe></div></div> */
set_define('FORMAT_IFRAME', true);
set_define('EXC_IFRAME_WRAP_HTML', 'noscript'); // 指定のタグで囲まれたものは除外する
set_define('IFRAME_RATE_WIDE', true); // iFrameの比率[false]で[4:3]に変更（デフォルトは[16:9]）

/** PREタグを整形 = <div class="pre-container"><div class="pre-wrapper"><pre></pre></div></div> */
set_define('FORMAT_PRE', true);
set_define('NUMBERING_PRE', true); // FORMAT_PREが'true'の場合のみ
set_define('HIGHLIGHT_PRE', true); // コードをハイライト（重くなるの）
