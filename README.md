# hubhb

WordPressライクなHTMLジェネレーターです。  
とってもシンプルなので高機能ではありません m(_ _)m  
そのままサーバーにアップしても使えます。

## 1. インストール
ダウンロード後、「設置した場所（localhost）/hubhb/」一度ブラウザでアクセスしてください。  
.httaccessが作られます。

例）http://localhost/hubhb/

## 2. プロジェクトフォルダ
/_projects/内にプロジェクトディレクトリを作成してください。  
基本はWordPressと同じです。

### sample.com

-- header.php  
-- footer.php  
-- index.php  
-- functions.php  
---- /page01  
---- index.php  
---- /page02  
---- index.php  

表示確認は「設置した場所（localhost）/hubhb/プロジェクト名/」で確認できます。

例）http://localhost/hubhb/sample.com/

## 3. サイト設定について
define.phpでサイトの設定ができます。  
プロジェクト内のfunctions.phpに'set_define'を追加すればプロジェクトごとの設定がきます。

## 4. 関数
関数については「/hubhb/_includes/template/, /hubhb/_includes/plugins/」の各ファイルを確認してください。  
プロジェクト内のfunctions.phpに追加すればプロジェクトごとの追加できます。（ WordPressと同じです）

## 5. テーマごとのカスタム
`
// 各ディレクトリのcss/*.cssファイルを自動で読み込む  
if (function_exists('get_files') && get_files(get_template_directory_uri() . get_call() . '/css', 'css')) {
    foreach ((array) get_files(get_template_directory_uri() . get_call() . '/css', 'css') as $item):  
        if (is_english($item)) {
            enqueue_style($item);
        }
    endforeach; 
}`

`
// 各ディレクトリのjs/*.jsファイルを自動で読み込む  
if (function_exists('get_files') && get_files(get_template_directory_uri() . get_call() . '/js', 'js')) {  
    foreach ((array) get_files(get_template_directory_uri() . get_call() . '/js', 'js') as $item):  
        if (is_english($item)) {  
            enqueue_script($item, false);  
        }
    endforeach;
}`

## 6. 公開について
### htmlファイルの書き出し（静的サイト）
設置した場所（localhost）/hubhb/プロジェクト名/html  
上記にアクセスすると公開ファイルが日付ごとに書き出されます。  
同じ日に書き出された場合は上書きされます。

例）http://localhost/hubhb/sample.com/html  
例）http://localhost/hubhb/sample.com/dist/sample.com-20180101

* 拡張子は.phpです。
* 書き出し時にimgタグに自動でwidth, height, 画像があればsrcsetなどの属性をセット。  制作時は重くなるので書き出し時のみに実行
* 書き出し時「css、js」ファイルのgzipファイルを生成します。
* サイトのdefine情報を「_config.json」に生成します。

公開後に「https://公開ドメイン.com/generator.php」にアクセスすると、  
.htaccess、sitemap.xml、robots.txtが自動で生成されます。  
.htaccessはエラーが出る場合がありますので、その都度調整してください。

### phpファイルの書き出し（動的サイト）
設置した場所（localhost）/hubhb/プロジェクト名/php  
上記にアクセスすると公開ファイルが日付ごとに書き出されます。  
同じ日に書き出された場合は上書きされます。

* 書き出し時「css、js」ファイルのgzipファイルを生成します。 
* サイトのdefine情報を「_config.json」に生成します。

.htaccessファイル作成が必須です。  
ボタンをクリックしてhtaccessファイルを作成してください。  
同時にsitemap.xml、robots.txtが自動で生成されます。
