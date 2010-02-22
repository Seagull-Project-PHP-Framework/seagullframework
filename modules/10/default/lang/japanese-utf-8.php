<?php
    $defaultWords = array(
/*  HOME    */
        'Welcome to' => 'ようこそ',
        'Home' => 'ホーム',

/*  Publisher subnav */
        'Categories' => 'カテゴリ',
        'Documents' => 'ドキュメント',
        'Articles' => '記事',
        'Permissions' => '権限',
        'No expire' => '終了期限なし',

/*  MODULE MGR  */
        'Module Manager' => 'モジュールマネージャ',
        'Module Manager :: Add' => 'モジュールマネージャ :: 作成',
        'Module Manager :: Edit' => 'モジュールマネージャ :: 編集',

/*  CONFIG MGR  */
        'Config Manager' => '設定の変更',
        'config info successfully updated' => '設定変更が完了しました。',
        'Deny list' => '拒否リスト',
        'Allow list' => '許可リスト',

/*  FOOTER  */
        'Execution Time' => '処理時間',
        'seconds' => 'sec',
        'ms' => 'ms',
        'queries' => 'クエリー',
        'allocated' => 'アロケート',
        'Powered by' => 'Powered by',
        'Seagull framework homepage' => 'Seagull フレームワーク ホームページ',
        'Seagull Framework' => 'Seagull フレームワーク',

/*  GENERAL MESSAGES    */
        'insufficient rights' => 'このページを閲覧する権限がありません。',
        'authorization required' => 'この機能を使うにはログインする必要があります。名前とパスワードを入力してください。',
        'session timeout' => 'セッションがタイムアウトしました。ログアウトします。',
        'You have been successfully logged out' => 'ログアウトしました。',
        'password emailed out' => '登録されているメールアドレスに、新しいパスワードを送りました',
        'email not in system' => '入力された認証情報は正しくありません。もう一度入力してください。',
        'email submitted successfully' => 'あなたのメールアドレスが登録されました。',
        'There was a problem sending the email' => 'メールを送るときにエラーが発生しました。',
        'message ID not recognised' => '認識できないメッセージIDです',
        'Please fill in the indicated fields' => '必須項目に不足があります。入力・選択しなおして再度登録してください。',
        'Your alert has been sent successfully' => 'アラートが送信されました',
        'Are you sure you want to delete this' => '本当に削除しますか？',

/*  MODULE MGR */
        'Module' => 'モジュール',
        'Module list' => 'モジュールリスト',
        'Active' => '有効',
        'module successfully updated' => 'モジュール情報は更新されました',
        'module successfully removed' => 'モジュールは削除されました',
        'The name of the module must be the exact name of the folder containing files, beware of case sensiveness' => 'モジュールの名前は、ファイルを格納するフォルダ名と同じ名前にする必要があります。大文字小文字に注意してください。',
        'Here you can write what you want' => '好きな名前を入力してください',

/*  NEWSLETTER BLOCK */
        'E-mail' => 'メールアドレス',
        'Lists' => 'リスト',
        'Subscribe' => '購読する',
        'Unsubscribe' => '解除する',
        'Send' => '送信',

/*  VARIOUS */
        'Editor type' => 'WYSIWYG エディタタイプ',
        'user' => 'ユーザー',
        'Username' => 'ユーザー名',
        'Action' => 'アクション',
        'Select' => '選択',
        'delete' => '削除',
        'delete selected' => '選択されたものを削除',
        'Edit' => '編集',
        'View' => 'ビュー',
        'move up' => '上へ',
        'move down' => '下へ',
        'finished' => '完了',
        'back to top' => 'トップへ戻る',
        'currently_logged_on_as' => 'ユーザー名',
        'guest' => 'ゲスト',
        'login' => 'ログイン',
        'logout' => 'ログアウト',
        'session started at' => 'セッション開始時間',
        'logged in at' => 'ログイン時間',
        'displaying results' => '検索結果を表示',
        'to' => '-',
        'from a total of' => '/ トータル数',
        'back' => '戻る',
        'next' => '次へ',
        'finish' => '完了',
        'yes' => 'はい',
        'no' => 'いいえ',
        'Send it' => '送る',
        'Submit' => '登録',
        'Cancel' => 'キャンセル',
        'Reset' => 'リセット',
        'reset' => 'リセット',
        'Save' => '保存',
        'add' => '追加',
        'edit' => '編集',
        'move' => '移動',
        'Manage' => 'Manage',
        'Title' => 'タイトル',
        'Status' => 'ステータス',
        'ID' => 'ID',
        'Name' => '名前',
        'check to activate' => '有効化する',
        'Password' => 'パスワード',
        'Login' => 'ログイン',
        'Forgot Password' => 'パスワードを忘れた',
        'Not Registered' => '登録します',
        'Email' => 'メールアドレス',

/* Bug Reporter */
        'Bug Report' => 'バグ報告',
        'send bug report' => 'バグ報告を送る',
        'First Name' => '名',
        'Last Name' => '姓',
        'You must fill in your description' => '説明文を入力してください',
        'You must fill in your comment' => 'コメントを入力してください',
        'Your email is not correctly formatted' => '正しいメールアドレスを入力してください',
        'You must enter your email' => 'メールアドレスを入力してください',

// Status
        'Enabled' => '有効',
        'Disable' => '無効',
        'Disabled' => '無効',
        'You must select an element to delete' => '削除するアイテムを選択してください',
        'no results found' => '見つかりませんでした',
        'You have been banned' => 'このサイトへのアクセスが禁止されています。管理者へご連絡ください',
        'Invalid POST source' => '不正なリクエスト元からデータがポストされているようです',
        'You are here' => '現在位置',
        'whats this?' => 'これは何？',
        'denotes required field' => '必須項目',

/*  Date and Time  */
/*  'at time' used at Output:showDateSelector  */
        'at time' => 'at',
/*
  Author: Michael willemot <michael@sotto.be>
*/
        'Return to browse' => 'ブラウズに戻る',
        'ModuleManager Mgr' => 'モジュールマネージャ用マネージャ',
        'Add' => '作成',
        'Delete' => '削除',
        'With selected module(s)' => '選択されたモジュールについて', //table footer
        'Add a module' => '新規モジュールの作成',
        'Module successfully added to the manager.' => 'モジュールが追加されました',
        'Module(s) successfully removed.' => 'モジュールは削除されました。',
        'Configurable' => '設定変更可能',
        'Description' => '説明文',
        'Admin URI' => '管理用URI',
        'Icon' => 'アイコン',

        // validation
        'Please, specify a name' => '名前を入力してください',
        'Please, specify a title' => 'タイトルを入力してください',
        'Please, specify a description' => '説明文を入力してください',
        'Please, specify the url to link to' => 'リンク先のURLを入力してください',
        'Please, specify the name of the icon-file' => 'アイコンのファイル名を入力してください',

        //  errors
        'you do not have perms' => 'この機能を使用する権限がありません',
        'you are not allowed to access this data' => 'このデータへのアクセス権限がありません',
        'this element has been deleted' => 'このアイテムは削除されました',

/*

ConfigMrg
Author: Rafael Ferreira Silva <rafael@webphp.com.br>

*/

        'Please use the following form to edit your config file'=>'次のフォームを使って、設定ファイルを編集してください',
        'General Site Options'=>'全般サイト設定',
        'Base URL'=>'ベースURL',
        'Session max lifetime (secs)'=>'セッションの最大存続期間 (秒)',
        'Site name'=>'サイト名',
        'Show logo'=>'ロゴの表示',
        'Gzip compression'=>'gzip 圧縮',
        'Output buffering'=>'出力バッファリング',
        'Enable IP banning'=>'禁止IPリストを有効にする',
        'Enable Tidy html cleaning'=>'Tidy HTML クリーニングを有効にする',
        'Session handler'=>'セッションハンドラ',
        'Extended Session'=>'拡張セッション管理',
        'Enforce Single User'=>'シングルユーザー設定',
        'You are allowed to connect from one computer at a time, other sessions were terminated!'=>'同時に一つのコンピューターからしかアクセスできません。他のセッションを終了します。',
        'You have multiple sessions on this site!'=>'このサイトへ複数セッションのアクセスを同時に行っています',
        'Enables extended session API when using database sessions. This allows the site to enforce one session per user.'=>'データベースを使った拡張セッションAPIを有効化します。この機能を使うと1ユーザーを同時に1セッションしかアクセスできなくすることが可能です。',
        'Enforces one session per user on this site (requires database session handling, and extended session to be on).'=>'サイトに対して1ユーザーが同時に1セッションしかアクセスできなくする (データベースを使ったセッションハンドラと、拡張セッション管理が有効になっている必要があります)。',
        'Guests'=>'ゲスト',
        'Members'=>'メンバー',
        'Total'=>'トータル',
        'Enable Blocks'=>'ブロックの有効化',
        'Default article view type'=>'デフォルトの記事タイプ',
        'Front controller script name'=>'インデックスファイル名',
        'Default module'=>'デフォルトのモジュール名',
        'Default manager'=>'デフォルトのマネージャ名',
        'Navigation Options'=>'ナビゲーション設定',
        'Enable Navigation'=>'ナビゲーションの有効化',
        'Navigation driver'=>'ナビゲーション ドライバ',
        'Navigation Html renderer'=>'ナビゲーションHTMLレンダラー',
        'Navigation menu stylesheet'=>'ナビゲーションメニュー スタイルシート',
        'Debugging Options'=>'デバッグオプション',
        'Enable authorisation'=>'認証管理の有効化',
        'Enable custom error handler'=>'エラーハンドラのカスタマイズの有効化',
        'Enable debug session'=>'デバッグ用セッションの有効化',
        'Production website'=>'運用モード',
        'Show backtrace'=>'バックトレースの表示',
        'Enable Profiling'=>'プロファイリングの有効化',
        'Email admin threshold'=>'管理者宛てメールの送信レベル',
        'Mark words which were not translated'=>'未翻訳ワードのマーキング',
        'Caching Options'=>'キャッシュ設定',
        'Enable caching'=>'キャッシュの有効化',
        'Cache lifetime (secs)'=>'キャッシュの存続期間 (秒)',
        'Database Options'=>'データベース設定',
        'Type'=>'タイプ',
        'Host'=>'ホスト',
        'Port'=>'ポート番号',
        'Protocol'=>'プロトコル',
        'Socket'=>'ソケット',
        'DB username'=>'DB ユーザー名',
        'DB password'=>'DB パスワード',
        'DB name'=>'データベース名',
        'Post-connection query'=>'接続後実行クエリー',
        'Database Table Mappings'=>'データベース テーブルマッピング',
        'Logging options'=>'ログイン設定',
        'Enable logs'=>'ログの有効化',
        'Log name'=>'ログファイル名／ログテーブル名',
        'Priority'=>'プライオリティー',
        'Identifier'=>'識別子',
        'Target username'=>'ターゲット ユーザー名',
        'Target password'=>'ターゲット パスワード',
        'Email options'=>'メールアドレス設定',
        'Admin contact'=>'管理者メールアドレス',
        'Support contact'=>'サポート用メールアドレス',
        'Info contact'=>'インフォメーション用メールアドレス',
        'Popup window options'=>'ポップアップウィンドウ設定',
        'Default popup window height'=>'デフォルトのポップアップウィンドウの高さ',
        'Default popup window width'=>'デフォルトのポップアップウィンドウの幅',
        'Cookie options'=>'Cookie設定',
        'Path'=>'パス',
        'Domain'=>'ドメイン',
        'Secure'=>'セキュア',
        'Censorship'=>'検閲',
        'Mode'=>'モード',
        'Replace word with'=>'置換ワード',
        'Disallowed word'=>'許可しない単語',
        'P3P privacy policy'=>'P3P プライバシー ポリシー',
        'Policies'=>'ポリシー',
        'Policy location'=>'ポリシー参照ファイル',
        'Compact policy'=>'コンパクト ポリシー',
        'Zero means until the browser is closed'=>'「0」を指定すると、ブラウザを閉じるまで有効になります',
        'If path to image is specified, image will be shown; if left blank, Site name from above will appear as text'=>'画像のファイル名が指定されると、指定された画像が表示されます。空欄の場合、サイト名が代わりに表示されます',
        'Handy if you dont have access to Apache configuration'=>'Apacheの設定ファイルを編集できない場合に便利です',
        'This way no content items are really deleting from DB, just marked as deleted'=>'「はい」を指定すると、データベースから実際のデータは削除されなくなり、代わりに削除フラグが立ちます',
        'Requires the tidy extension to be installed'=>'Tidy拡張モジュールがインストールされている必要があります',
        'Use the database session handler if youre running a load-balanced environment'=>'ロードバランシング環境で動作させる場合、データベースを使ったセッションハンドラを利用してください',
        'You can turn the blocks off globally'=>'サイト全体のブロックを無効化できます',
        'This options allows you to change the default type of article displayed. Default Article View Type: Html Articles (2)'=>'このオプションで、デフォルトで表示される記事タイプを変更できます。デフォルトの記事タイプ: HTML記事(2)',
        'The name of your Seagull index file'=>'Seagullサイトのインデックスファイル名',
        'Currently supported editors are xinha, fck and htmlarea, and you must have the relevant libs in your www dir'=>'現在サポートされているエディタは、xinha、fck、もしくはhtmlareaです。www ディレクトリに適切なライブラリを配置する必要があります',
        'This is the module that will be loaded if none are specified, ie, when you call index.php'=>'何も指定されていない場合（index.phpをリクエストした場合）にロードされるモジュールです。',
        'This is the manager class that will be loaded if none are specified'=>'何も指定されていない場合にロードされるマネージャクラスです。',
        'Disable navigation altogether with this switch'=>'このオプションで全てのナビゲーションを無効にできます',
        'Use this option to choose from various menu types - currently only 1 provided'=>'メニューの種類を切り替えたい場合に選択してください。- 現在は1つのみ提供されています',
        'Defines the appearance of the navigation menu. Preview and make additional changes in the navigation module manager'=>'ナビゲーションメニューの表示制御を設定します。ナビゲーションマネージャを開いてから設定の変更を行ってください。',
        'Debugging easier when this is disabled'=>'これを無効にするとデバッグを行いやすくなります',
        'Customise the way errors are handled'=>'エラーハンドリングの方法をカスタマイズします',
        'If your IP appears in the TRUSTED_IPS array, you will be able to view system errors on screen even in production mode (see below)'=>'「信頼できるIPアドレス」のリストに入っているIPアドレスの場合、運用モード（下を参照）の場合でも画面上にシステムエラーが表示されます',
        'Setting this to true will disable all screen-based error messages'=>'「はい」にすると、エラーメッセージのスクリーン表示は行われなくなります。',
        'Requires to Xdebug extension to be installed'=>'Xdebug拡張モジュールがインストールされている必要があります',
        'Errors must be >= this level before they are emailed to the site admin'=>'選択したレベル以上のエラー発生時に、管理者宛てにメールが送られます',
        'It is recommended to disable this while developing'=>'デバッグ中は無効化しておくことをおすすめします',
        'Default is 24 hours'=>'デフォルトは24時間（86400秒）です',
        'Make sure you load the relevant schema'=>'適切なスキーマを決定してください。 - 「mysql_SGL」は全てのシーケンスを一つのテーブルで管理し（ごみテーブルが少ない）、対して「mysql」はシーケンスごとにテーブルを1つ作るため、全テーブル数は2倍になります (パフォーマンスはより優れている)',
        'It is recommended to disable logging if you are running < PHP 4.3.x'=>'PHP 4.3.x 以下で動作させる場合、ロギングを無効にすることをおすすめします',
        'If sql is used, use log_table as the log table name below'=>'「sql」を選択した場合、下のログテーブル名には「log_table」を指定してください',
        'Use an absolute path or one relative to the Seagull root dir'=>'絶対パス、もしくはSeagullのルートディレクトリからの絶対パスを指定してください',
        'Error messages get sent here'=>'エラーメッセージはここに送られます',
        'Contact us enquiries get sent here'=>'「Contact us」の問合せ先メールアドレス',
        'This will be your session identifier'=>'セッション識別子になります',
        'Disallowed words'=>'許可しない単語',
        'Enable Safe deleting'=>'論理削除の有効化',
        'Default params'=>'デフォルトパラメータ',
        'Use these params to specify, eg, a static article for your homepage'=>'ホームページにスタティック記事を使いたいときなどに指定してください',
        'file'=>'ファイル',
        'database'=>'データベース',
        'never'=>'なし',
        'Show debug reporting link'=>'バグ報告用のリンク表示',
        'Send feedback to project for bugs'=>'プロジェクトへのフィードバックバグ報告を送ります',
        'Words which system was unable to translate will be enclosed in "> <" marks'=>'翻訳できないワードを「> <」で囲みます',
        'Output URL handler'=>'出力URLハンドラ',
        'What format would you like your output URLs, Seagull Search Engine Friendly is the default'=>'出力するURLのフォーマットを選択してください。デフォルトはSeagull Search Engine Friendlyです',
        'The classic URL handler has not been implemented yet'=>'クラシックURLハンドラはまだ実装されていません',
        'Template Engine'=>'テンプレートエンジン',
        'Seagull allows you to use the template engine of your choice'=>'Seagullはあなたが選択するテンプレートエンジンを利用できます',
        'The Smarty template hooks have not been implemented yet'=>'Smartyテンプレート用のhookはまだ実装されていません',
        'This query is used to set the default character set for the current connection (MySQL 4.1 or higher). For example: SET NAMES utf8'=>'このクエリーは、コネクションに対するデフォルト文字セットを指定するために使われます (MySQL 4.1 以上). 例: SET NAMES utf8',

/*
 ConfigMgr: MTA options
*/
        'MTA options' => 'MTA設定',
        'Backend' => 'バックエンド',
        'PEAR::Mail backend' => 'PEAR::Mailのバックエンド',
        'Sendmail path' => 'Sendmailのインストールパス',
        'Mandatory if you use Sendmail as Backend' => 'バックエンドに「Sendmail」を指定している場合必須です',
        'Sendmail arguments' => 'Sendmail arguments',
        'Optional if you use Sendmail as Backend' => 'Optional if you use \'Sendmail\' as Backend',
        'SMTP host' => 'SMTP ホスト',
        'Optional if you use SMTP as Backend. Default: localhost' => 'バックエンドに「SMTP」を指定している場合のオプション設定。デフォルト: localhost',
        'SMTP port' => 'SMTP ポート番号',
        'Optional if you use SMTP as Backend. Default: 25' => 'バックエンドに「SMTP」を指定している場合のオプション設定。デフォルト: 25',
        'Use SMTP authentication' => 'SMTP認証を使用する',
        'SMTP username' => 'SMTP ユーザー名',
        'SMTP password' => 'SMTP パスワード',
        'Mandatory if you use SMTP as Backend and SMTP authentication is enabled' => 'バックエンドに「SMTP」を指定していて、「SMTP認証」を有効にしている場合に必須です。',
        'If users have cookies disabled, this will allow them to use sessions with Seagull' => 'Cookieを無効にしているユーザーの場合に、Seagullでのセッション利用を可能にします',
        'Allow Session in URL' => 'URLでのセッション管理を許可',
        'Check for Latest Version' => '最新バージョンをチェック',
        'Check Now' => '確認する',
        'Your current version is up to date' => '現在のバージョンは最新版です',
        'remote interface problem' => 'リモートインターフェースへの接続時にエラーが発生しました',
    );

$defaultWords['Maintenance'] = 'メンテナンス';
$defaultWords['Maintenance Manager'] = 'メンテナンス マネージャ';
$defaultWords['Back to Maintenance'] = 'メンテナンスに戻る';
$defaultWords['Congratulations, the target translation appears to be up to date'] = '言語ファイルに異常はありません。';
$defaultWords['translation successfully updated'] = 'アップデート完了です。';
$defaultWords['There was a problem updating the translation'] = '言語ファイルの更新中にエラーが発生しました';
$defaultWords['Data Objects rebuilt successfully'] = 'データオブジェクトを再構築しました';
$defaultWords['Cache files successfully deleted'] = 'キャッシュファイルを削除しました';
$defaultWords['Manage Translations'] = '翻訳マネージャ';
$defaultWords['Check all modules for'] = '全モジュールをチェック:';
$defaultWords['check all modules'] = '全モジュールをチェック';
$defaultWords['update'] = '更新';
$defaultWords['Module Name'] = 'モジュール名';
$defaultWords['ok'] = '○';
$defaultWords['no file'] = 'ファイルがありません';
$defaultWords['new strings'] = '新しい文字列';
$defaultWords['old strings'] = '古い文字列';
$defaultWords['File not writeable'] = 'ファイルが書き込み可能になっていません';
$defaultWords['Sequences rebuilt successfully'] = 'シーケンスを再構築しました';
$defaultWords['Rebuild DB Sequences'] = 'データベースのシーケンスを再構築';
$defaultWords['Rebuild Sequences Now'] = 'シーケンスを再構築する';
$defaultWords['validate'] = 'バリデート';
$defaultWords['Process'] = '実行';
$defaultWords['Manage Caches'] = 'キャッシュの管理';
$defaultWords['Templates'] = 'テンプレート';
$defaultWords['navigation'] = 'ナビゲーション';
$defaultWords['blocks'] = 'ブロック';
$defaultWords['categories'] = 'カテゴリ';
$defaultWords['permissions'] = '権限';
$defaultWords['Clear Selected Caches Now'] = '選択されたキャッシュをクリアする';
$defaultWords['Rebuild Data Objects'] = 'データオブジェクトの再構築';
$defaultWords['Rebuild Dataobjects Now'] = 'データオブジェクトを再構築する';
$defaultWords['You are editing: Module'] = '編集中のモジュール:';
$defaultWords['You are updating: Module'] = '更新中のモジュール:';
$defaultWords['Master Value'] = '元の文字列';
$defaultWords['Translated Value'] = '翻訳文字列';
$defaultWords['Save Translation'] = '言語ファイルの保存';
$defaultWords['Create a module'] = 'モジュールの作成';
$defaultWords['Manager Name'] = 'マネージャ名';
$defaultWords['Create Templates'] = 'テンプレートの作成';
$defaultWords['Create ini file'] = 'iniファイルの作成';
$defaultWords['Create language files'] = '言語ファイルの作成';
$defaultWords['Create Module Now'] = 'モジュールを作成する';
$defaultWords['Module files successfully created'] = 'モジュールファイルが作成されました';
$defaultWords['The source translation has'] = '元の言語ファイルの翻訳データ数:';
$defaultWords['elements'] = 'アイテム';
$defaultWords['The target translation has'] = 'ターゲットの言語ファイルの翻訳データ数:';
$defaultWords['Please add'] = '追加してください:';
$defaultWords['values for the following keys which appear to be missing from the'] = 'の中で、次のモジュールに値が足りないようです:';
$defaultWords['module'] = 'モジュール';
$defaultWords['please specify an option'] = 'オプションを指定してください';
$defaultWords['please check at least one box'] = '少なくとも1つのチェックボックスを選択してください';
$defaultWords['please enter module name'] = 'モジュール名を入力してください';
$defaultWords['please enter manager name'] = 'マネージャ名を入力してください';
$defaultWords['module already exists. Please choose another module name'] = 'モジュールはすでに存在しています。別のモジュール名を指定してください。';
$defaultWords['Extended locale support'] = '拡張ロケールのサポート';
$defaultWords['locale support info'] = '拡張された I18Nv2 API へのアクセス機能を有効化します。しかし性能面は劣ります。';
$defaultWords['Locale category'] = 'ロケールのカテゴリ';
$defaultWords['Paths'] = 'パス';
$defaultWords['Install Root'] = 'インストールしたルートディレクトリ';
$defaultWords['Web Root'] = 'HTTPアクセス用ルートディレクトリ';
$defaultWords['With selected record(s)'] = '選択されたレコードについて';
$defaultWords['config options'] = 'オプション設定';
$defaultWords['action'] = 'アクション';
$defaultWords['preferences'] = '環境設定';
$defaultWords['Section ID'] = 'セクションID';
$defaultWords['Manager'] = 'マネージャ';
$defaultWords['None'] = 'なし';
$defaultWords['Please supply full nav info'] = '全ナビゲーション情報を入力してください';
$defaultWords['Add module'] = '新規モジュールの作成';
$defaultWords['New section'] = '新規セクション';
$defaultWords['manage'] = '管理';
$defaultWords['BodyHtml'] = 'コンテンツHTML';
$defaultWords['Translation options'] = '翻訳設定';
$defaultWords['Container'] = 'コンテナ';
$defaultWords['Fallback Language'] = '代替用の言語';
$defaultWords['Add Missing Translations'] = '不明な翻訳語を登録';
$defaultWords['General'] = '全般';
$defaultWords['Navigation'] = 'ナビゲーション';
$defaultWords['Debug'] = 'デバッグ';
$defaultWords['Caching'] = 'キャッシュ';
$defaultWords['DB'] = 'データベース';
$defaultWords['Logs'] = 'ログ';
$defaultWords['MTA'] = 'メール送信';
$defaultWords['Popup'] = 'ポップアップ';
$defaultWords['Translation'] = '翻訳';
$defaultWords['Cookie'] = 'Cookie';
$defaultWords['P3P'] = 'P3P';
$defaultWords['Admin GUI Feature'] = '管理者向け画面';
$defaultWords['allow backend to display in separate GUI'] = 'バッグエンド用に別の画面表示を行います';
$defaultWords['Configuration'] = '設定';
$defaultWords['Sort by'] = 'ソート順:';
$defaultWords['Publishing'] = 'パブリッシング';
$defaultWords['Admin GUI theme'] = '管理者向け画面テーマ';
$defaultWords['before'] = 'より前';
$defaultWords['after'] = 'より後';
$defaultWords['is'] = '＝';
$defaultWords['between'] = 'の間';
$defaultWords['active'] = '有効';
$defaultWords['inactive'] = '無効';
$defaultWords['page'] = 'ページ';
$defaultWords['Session'] = 'セッション';
$defaultWords['top'] = 'トップ';
$defaultWords['check all'] = '全てをチェック';
$defaultWords['uncheck all'] = '全てのチェックをはずす';
$defaultWords['Add following methods'] = 'メソッドの作成';
$defaultWords['Editing options'] = 'オプション設定';
$defaultWords['Publish'] = '公開';
$defaultWords['user profile'] = 'ユーザーのプロフィール';
$defaultWords['PEAR Manager'] = 'PEAR マネージャ';
$defaultWords['Choose channel'] = 'チャンネルの選択';
$defaultWords['List installed packages'] = 'インストール済みパッケージのリストアップ';
$defaultWords['List remote packages'] = 'リモートパッケージのリストアップ';
$defaultWords['Search package'] = 'パッケージの検索';
$defaultWords['Pear Manager Notice'] = 'PEAR パッケージのフルリストをリクエストするのに、初回は300以上のパッケージをREST/XML-RPC経由で取得しますので、しばらく時間がかかります。512kbpsの回線であれば30秒くらいお待ちください。';
$defaultWords['Package Name'] = 'パッケージ名';
$defaultWords['Local'] = 'ローカル';
$defaultWords['Latest'] = '最新版';
$defaultWords['Install'] = 'インストール';
$defaultWords['Uninstall'] = 'アンインストール';
$defaultWords['edit'] = '編集';
$defaultWords['Translation Maintenance'] = '言語設定';
$defaultWords['Coming Soon - The ability to switch between translation storage containers.'] = '翻訳ストレージのコンテナを切り替えられます（近々公開予定）。';
$defaultWords['Language to use when the current language does not have a translation.'] = '現在の言語用の翻訳データがない場合に使われる言語です。';
$defaultWords['Add missing translations to the database.'] = '実験的な設定 - 不明な翻訳語をデータベースに登録します。';
$defaultWords['the target lang file'] = '言語ファイル';
$defaultWords['is not writeable.'] = 'が書込可能になっていません。';
$defaultWords['does not exist.'] = 'が存在しません。';
$defaultWords['Please change file permissions before editing.'] = '権限を変更してください。';
$defaultWords['Please create it.'] = '新規に言語ファイルを作成してください。';
$defaultWords['Default theme'] = 'デフォルトのテーマ';
$defaultWords['Additional Include Path'] = '追加インクルードパス';
$defaultWords['Custom filter chain'] = 'カスタム フィルターチェイン';
$defaultWords['Create CRUD actions'] = 'CRUDアクションの作成';
$defaultWords['Broadcast message'] = '全ページ用の同報メッセージ';
$defaultWords['Rebuild Seagull'] = 'Seagullの再構築';
$defaultWords['Module Manager :: Discovered'] = 'モジュール管理 :: 新規モジュールが見つかりました';
$defaultWords['Register this module?'] = 'このモジュールを登録しますか？';
$defaultWords['DataObject debug level'] = 'データオブジェクトのデバッグレベル';
$defaultWords['Please choose a simple, single word'] = 'モジュール名はURIにも使われますので、シンプルな名前を選んでください。';
$defaultWords['Please give the webserver write permissions to the modules directory'] = 'モジュールディレクトリには、Webサーバーが書き込み可能なパーミッションを設定してください。';
$defaultWords['The manager, which can be'] = 'マネージャ（コントローラー）は、1つのモジュールにつき複数作ることが出来ますが、そのうちの1つをここで設定してください。ピザ（pizza）の配達を任せたいのならピザマネージャ（PizzaMgr）という名前にしてください。';
$defaultWords['aMonths']['01'] =  '1月';
$defaultWords['aMonths']['02'] =  '2月';
$defaultWords['aMonths']['03'] =  '3月';
$defaultWords['aMonths']['04'] =  '4月';
$defaultWords['aMonths']['05'] =  '5月';
$defaultWords['aMonths']['06'] =  '6月';
$defaultWords['aMonths']['07'] =  '7月';
$defaultWords['aMonths']['08'] =  '8月';
$defaultWords['aMonths']['09'] =  '9月';
$defaultWords['aMonths']['10'] =  '10月';
$defaultWords['aMonths']['11'] =  '11月';
$defaultWords['aMonths']['12'] =  '12月';


?>
