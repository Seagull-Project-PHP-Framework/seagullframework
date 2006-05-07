<?php
$words = array(

/* Account MGR */

    // title
    'My Account'                   => 'マイアカウント',
    'My Profile :: Edit'           => 'マイプロフィール :: 編集',

    // summary form (admin template)
    'Role'                         => 'ロール',
    'Date Registered'              => '登録日',
    'Last Login'                   => '最終ログイン日',
    'first login in progress'      => 'ログインセッション中',
    'Current IP Address'           => '接続元ＩＰアドレス',
    'change password'              => 'パスワード変更',
    // + user template
    'My Profile'                   => 'マイプロフィール',
    'Preferences'                  => '環境設定',
    'Password'                     => 'パスワード',
    'edit preferences'             => '環境設定の変更',
    'view profile'                 => 'プロフィール確認',
    'No results found for that ID' => 'このIDのレコードが見つかりません',

    // profile form (admin template)
    'Personal Details'        => 'ユーザー情報',
    'Username'                => 'ユーザー名',
    'First Name'              => '名',
    'Last Name'               => '姓',
    'Contact'                 => '連絡先',
    'Location'                => '住所',
    'Address 1'               => '住所 1',
    'Address 2'               => '住所 2',
    'Address 3'               => '住所 3',
    'City'                    => '都市',
    'Country'                 => '国',
    'Telephone'               => '電話番号',
    'Mobile'                  => '携帯電話番号',
    'County/State/Province'   => '州',
    'ZIP/Postal Code'         => '郵便番号',
    // + user template
    'Company'                 => '会社名',
    'edit these values'       => '編集',

    // edit form
    'edit user'          => 'ユーザーの編集',
    'Confirm Password'   => 'パスワード確認',
    'Organisation name'  => '会社（組織）名',
    'Is Active?'         => '有効ですか？',
    'Security'           => 'パスワードを忘れたときの質問',
    'Security question'  => '質問事項',
    'Answer'             => '答え',

    'aSecurityQuestions' => array(
        0 => '-- 質問事項 --',
        1 => '好きなペットの名前は？',
        2 => '忘れられない日は？ (dd/mm/yyyy)',
        3 => '忘れられない場所は？',
        4 => 'お母さんの旧姓は？',
        5 => '好きな映画の名前は？',
        6 => '好きな歌の名前は？',
        7 => '好きなカクテルの名前は？'
    ),

    // messages
    'profile successfully updated' => 'プロフィール情報は更新されました',

    // validate
    'You must enter a username'             => 'ユーザー名を入力してください',
    'username min length'                   => 'ユーザー名は5文字以上の半角英数字で入力してください',

    'You must enter at least address 1'     => '住所1を入力してください',
    'You must enter your city'              => '都道府県名を入力してください',
    'You must enter your state/province'    => '州を選択してください',

    'Please enter a company name'           => '会社名を入力してください',
    'You must enter your ZIP/Postal Code'   => '郵便番号を入力してください',
    'You must enter your country'           => '国名を選択してください',
    'Your email is not correctly formatted' => 'メールアドレスは正しく入力してください',
    'You must enter your email'             => 'メールアドレスを入力してください',
    'You must choose a security question'   => '質問事項を選択してください',
    'Please enter a telephone number'       => '電話番号を入力してください',
    'You must provide a security answer'    => '質問事項に対する答えを入力してください',


/* Register MRG */

    // title
    'Register' => 'ユーザー登録',

    // validation
    'You must enter a password'                                    => 'パスワードを入力してください',
    'Please confirm password'                                      => 'パスワード確認を入力してください',
    'Password must be between 5 to 10 characters'                  => 'パスワードは5～10文字の半角英数字で入力してください',
    'Passwords are not the same'                                   => '異なるパスワードです',
    'This email already exist in the DB, please choose another'    => 'すでに登録済みのメールアドレスです',
    'This username already exist in the DB, please choose another' => 'すでに登録済みのユーザー名です',

    // messages
    'user successfully registered' => '仮登録が完了しました。登録内容をメールで送りましたのでご確認ください。',

    // XXX mail templates here


/* Password MRG */

    // titles
    'Retrieve password' => 'パスワード再発行',

    // list
    'Enter the email address you registered with' => '登録済みのメールアドレスを入力してください',
    'retrieve' => '再取得',


/* UserPassword */

    // titles
    'Change Password' => 'パスワード変更',

    // form
    'Original password'  => '元のパスワード',
    'New password'       => '新しいパスワード',
    'Confirm'            => 'パスワード確認',
    'Notify me by email' => '変更をメールで送信',

    // messages
    'Password updated successfully' => 'パスワードが更新されました',

    // validation
    'You must enter your original password'                 => '元のパスワードを入力してください',
    'You must enter a new password'                         => '新しいパスワードを入力してください',
    'You have entered your original password incorrectly'   => '元のパスワードが正しくありません',


/* Login MRG */

    // title
    'Login' => 'ログイン',

    // form
    'Authorisation Required' => '認証情報の入力',

    // validation
    'username/password not recognized' => '入力されたユーザー名とパスワードは認証できませんでした。ご確認のうえ再度お試しください。',

    // XXX: other messages are defined in default lang file for now :/


/* UserPreference MGR */

    // title
    'User Preferences' => 'ユーザー環境設定',

    // form (admin template)
    'Theme'                                        => 'テーマ',
    'Change what this site looks like'             => 'サイトの見た目を変更',
    'Date format'                                  => '日付表示フォーマット',
    'UK format is dd/mm/yyyy and US is mm/dd/yyyy' => 'UKフォーマットはdd/mm/yyyy、USフォーマットはmm/dd/yyyy',
    'Interface language'                           => '言語',
    'Session timeout'                              => 'セッション存続時間',
    'Session timeout tooltip'                      => 'セキュリティを重視する場合、ブラウザを閉じたときにセッションが終了するのが好ましいです。必要に応じて、セッションの最大存続時間を設定してください。',
    'Locale'                                       => 'ロケール',
    'Timezone'                                     => 'タイムゾーン',
    'Results per page'                             => '一ページあたりの結果表示数',
    'Show execution times?'                        => '処理時間を表示しますか？',
    // + user template
    'General'                                      => '一般',

    // messages
    'details successfully updated' => 'ユーザー情報は更新されました',


/* Profile MGR */

    // titile
    'User Profile' => 'ユーザープロフィール',

    // form
    'Real Name'                => '本名',
    'Lives in'                 => '住んでいる場所',
    'Member Since'             => '入会時期',
    'none given'               => '指定なし',
    'Posting Stats for User'   => 'ユーザー書き込み状況:',
    'Total Articles'           => 'トータル記事数',
    'Total Comments'           => 'トータルコメント数',
    'coming soon ...'          => 'coming soon ...',

     // messaging
    'back to contacts' => '連絡先リストへ戻る',
    'Message'          => 'メッセージ',
    'send message'     => 'メッセージを送る',
    'Contacts'         => '連絡先',
    'add to contacts'  => '連絡先に追加する',


/* OrgPreferences MGR */

    // title
    'Organisation Preferences' => '会社（組織）環境設定',

    // form
    'Preferences for org' => '会社（組織）用環境設定',

    // messages
    'org details successfully updated' => '会社（組織）情報は更新されました',


/* OrgType MGR */

    // titles
    'OrgType Manager'           => '会社（組織）種別管理',
    'OrgType Manager :: Browse' => '会社（組織）種別管理 :: ブラウズ',
    'OrgType Manager :: Add'    => '会社（組織）種別管理 :: 新規作成',
    'OrgType Manager :: Edit'   => '会社（組織）種別管理 :: 編',

    // list
    'Organisation Type list'    => '会社（組織）種別 リスト',
    'Add organisation type'     => '新しい会社（組織）種別の追加',
    'organisation type'         => '会社（組織）種別',
    'Be Careful!'               => '注意！',

    // form
    'New organisation'          => '新しい会社（組織）の作成',
    'Edit organisation'         => '会社（組織）の編集',

    // validation
    'You must enter an organisation type name' => '会社（組織）種別名を入力してください',

    // messages
    'No data was updated'                             => 'データは更新されませんでした',
    'Org type(s) deleted successfully'                => '会社（組織）種別は削除されました',
    'Organisation type saved successfully'            => '会社（組織）種別情報は保存されました',
    'Organisation type has been updated successfully' => '会社（組織）種別情報は更新されました',


/* Org MGR */

    // title
    'Organisation Manager'           => '会社（組織）管理',
    'Organisation Manager :: Browse' => '会社（組織）管理 :: ブラウズ',
    'Organisation Manager :: Add'    => '会社（組織）管理 :: 新規作成',
    'Organisation Manager :: Edit'   => '会社（組織）管理 :: 編集',

    // list
    'Organisation list'    => '会社（組織）リスト',
    'Website'              => 'ウェブサイト',
    'change'               => '変更',
    'organisation'         => '会社（組織）',

    // form
    'New organisation'     => '新しい会社（組織）の作成',
    'Edit organisation'    => '会社（組織）の編集',
    'Details'              => '詳細',
    'Description'          => '説明文',
    'Parent Org'           => '親会社（組織）',
    'Default Role'         => 'デフォルトのロール',

    // validation
    'You must enter an organisation name' => '会社（組織）名を入力してください',

    // messages
    'organisation successfully added'                                                     => '会社（組織）情報が作成されました',
    'The selected organisation(s) have successfully been deleted'                         => '選択された会社（組織）情報は削除されました',
    'The selected organisation cannot be deleted because there are users relating to it'  => '選択された会社（組織） %s は削除できません。この会社（組織）に関連付けられたユーザーがいます。',
    'The organisation has successfully been updated'                                      => '会社（組織）情報は更新されました',
    'The organisation has successfully been updated, no data changed'                     => '会社（組織）情報は更新されました（データの変更なし）',


/* Preference MGR */

    // titles
    'Preference Manager'           => '環境設定管理',
    'Preference Manager :: Browse' => '環境設定管理 :: ブラウズ',
    'Preference Manager :: Add'    => '環境設定管理 :: 追加',
    'Preference Manager :: Edit'   => '環境設定管理 :: 編集',

    // list
    'Default value' => 'デフォルト値',
    'preference'    => '環境設定',

    // form
    'New preference'     => '新しい環境設定',
    'Editing preference' => '環境設定の編集',

    // validation
    'You must enter a default value'   => 'デフォルト値を入力してください',
    'You must enter a preference name' => '環境設定の項目名を入力してください',

    // messages
    'pref successfully deleted' => '環境設定項目を削除しました',
    'pref successfully updated' => '環境設定項目が更新されました',
    'pref successfully added'   => '環境設定項目が追加されました',


/* Permission MGR */

    // titles
    'Permission Manager'                    => '権限管理',
    'Permission Manager :: Browse'          => '権限管理 :: ブラウズ',
    'Permission Manager :: Add'             => '権限管理 :: 新規作成',
    'Permission Manager :: Edit'            => '権限管理 :: 編集',
    'Permission Manager :: Detect & Add'    => '権限管理 :: 検出＆追加',
    'Permission Manager :: Detect Orphaned' => '権限管理 :: 不要な権限情報の検出',

    // list
    'New permission'           => '新規権限の作成',
    'Editing permission'       => '権限の編集',
    'detect & add permissions' => '検出＆追加',
    'remove orphaned'          => '不要な情報の削除',
    'filter by module'         => 'モジュールで絞り込み',
    'all'                      => '全て',
    'permission'               => '権限',

    // add / edit form
    'Module'                   => 'モジュール',

    // detect perms / remove orphaned
    'Detected Perms'             => '権限情報の検出',
    'Detected Orphaned Perms'    => '不要な権限情報の検出',
    'orphaned perms description' => 'データベース上に、モジュールには使われていない次の権限情報があります。',
    'detected perms description' => 'このモジュールの中に次の権限情報が見つかりましたが、これらの権限情報はまだシステムに追加されていません',

    // validation
    'You must enter a permission name' => '権限名を入力してください',

    // messages
    'perm already defined'                      => '権限はすでに定義されています',
    'perm successfully added'                   => '権限情報が追加されました',
    'perm(s) successfully added'                => '権限情報が追加されました',
    'perm successfully deleted'                 => '権限情報は削除されました',
    'perm successfully updated'                 => '権限情報は更新されました',
    'perm successfully deleted'                 => '権限情報は削除されました',

/* Role MGR */

    // titles
    'Role Manager'                => 'ロール管理',
    'Role Manager :: Browse'      => 'ロール管理 :: ブラウズ',
    'Role Manager :: Add'         => 'ロール管理 :: 新規作成',
    'Role Manager :: Edit'        => 'ロール管理 :: 編集',
    'Role Manager :: Permissions' => 'ロール管理 :: 権限',

    // list
    'New Role'   => '新規ロールの作成',
    'duplicate'  => 'コピーする',
    'role'       => 'ロール',
    'permission' => '権限',

    // add / edit / perms assignment
    'Editing role'                        => 'ロールの編集',
    'Changing permission assignments for' => '権限の割り当てを変更',
    'Remaining permissions'               => '未指定の権限',
    'Selected permissions for'            => '設定済みの権限:',
    'remove'                              => '削除',

    // validation
    'You must enter a role name' => 'ロール名を入力してください',

    // messages
    'role successfully added'                                                             => 'ロール情報が追加されました',
    'role successfully updated'                                                           => 'ロール情報は更新されました',
    'role successfully deleted'                                                           => 'ロールは削除されました',
    'role successfully deleted but please note, admin/unassigned roles cannot be deleted' => 'ロールは削除されました。ただし、adminもしくはunassignedのロールは削除できません。',
    'role successfully duplicated'                                                        => 'ロールをコピーしました',
    'role assignments successfully updated'                                               => 'ロールの割り当ては更新されました',

/* UserSearch MGR */

    // titles
    'User Manager :: Search' => 'ユーザー管理 :: 検索',

    // list
    'Search Criteria' => '検索条件',
    'Role name'       => 'ロール名',
    'User ID'         => 'ユーザーID',
    'Organisation'    => '会社（組織）',
    'Register Date'   => '登録日',

    // validation
    'You must select a valid date' => '正しい日付を入力してください',
    'You must select a date'       => '日付を入力してください',


/* UserImport MGR */

    // titles
    'User Import Manager' => 'ユーザーインポート マネージャ',

    // list
    'userfile'                                                         => '入力ファイル',
    'Use the document manager to upload CSV files.'                    => 'ドキュメントマネージャでCSVファイルをアップロードしてください。',
    'Import users into selected organisation.'                         => '選択された会社（組織）にユーザーをインポートします。',
    'User will be assigned to selected role.'                          => 'ユーザーは選択されたロールに割り当てられます。',
    'You should first upload at least one csv file in your upload dir' => 'アップロード用ディレクトリに最低1つのCSVファイルをアップロードしてください',

    // validation
    'Please select a file.'              => 'ファイルを指定してください。',
    'Please select the organisation'     => '会社（組織）を選択してください。',
    'Please select the role'             => 'ロールを選択してください。',


/* User MGR */

    // titles
    'User Manager'                     => 'ユーザー管理',
    'User Manager :: Browse'           => 'ユーザー管理 :: 閲覧',
    'User Manager :: Login Data'       => 'ユーザー管理 :: ログインデータ',
    'User Manager :: Edit'             => 'ユーザー管理 :: 編集',
    'User Manager :: Edit permissions' => 'ユーザー管理 :: 権限変更',
    'User Manager :: Add'              => 'ユーザー管理 :: 追加',
    'User Manager :: Reset password'   => 'ユーザー管理 :: パスワードリセット',
    'User Manager :: Change status'    => 'ユーザー管理 :: ステータス変更',

    // list
    'New User'             => '新規ユーザー',
    'Search'               => '検索',
    'Import users'         => 'ユーザー情報のインポート',
    'User list'            => 'ユーザーリスト',
    'user(s) found'        => 'ユーザーが見つかりました',
    'clear search'         => '検索条件をクリア',
    'Logins'               => 'ログイン',
    'list'                 => 'リスト',
    'Change status'        => 'ステータスの変更',
    'No users found'       => 'ユーザーは見つかりません',
    'Synchronise'          => '同期化',
    '(each users current)' => '(ユーザーごとの現在のロール)',
    'sync perms with role' => 'ロールの権限と同期する',
    'add missing perms'    => '存在しない権限情報を追加',
    'remove extra perms'   => '余分な権限情報を削除',
    'complete sync'        => '完全同期',

    // add / edit
    'add user'             => '新規ユーザーを追加',
    'check if active'      => '有効化する',

    // login data
    'Connection list'      => '関係リスト',
    'Login Time'           => 'ログイン時間',
    'Remote IP'            => 'リモートIP',
    'Are you sure?'        => '本当によろしいですか？',

    // status change
    'Disable Now'          => '無効にする',
    'Enable Now'           => '有効にする',
    'active'               => '有効',
    'disabled'             => '無効',
    'Changing status for'  => 'ステータスの変更',
    'Current status is'    => '現在のステータス',
    'Enable user'          => 'ユーザーを有効化',
    'Disable user'         => 'ユーザーを無効化',
    'Notify user'          => 'ユーザーに通知する',

    // reset
    'Reset password now'                   => 'パスワードをリセットする',
    'Resetting password for'               => '次のユーザーのパスワードをリセット:',
    'Reset password info'                  => '管理ユーザーのパスワードを変更する場合、正しくメールの送受信が行えるように、（管理者ユーザーの編集画面にて）新しいパスワードが届くメールアドレスが正しく設定されていることを必ず確認してください。',

    // change permissions
    'for user id'            => 'ユーザーID:',
    'Select a module'        => 'モジュールの選択',

    // messages
    'user successfully added'              => 'ユーザーが追加されました',
    'Deleted successfully'                 => '正常に削除されました',
    'Status changed successfully'          => 'ステータスは更新されました',
    'user successfully deleted'            => '選択されたユーザー情報は削除されました',
    'The user has been successfully added' => 'ユーザーは正常に追加されました',

/**
 * Mail templates
 */
    'Thanks for registering with'            => '仮登録メール :',
    'username'                               => 'ユーザー名',
    'Dear'                                   => '登録者名:',
    'You are being sent this email because your new account status is now' => 'あなたのアカウントのステータスが更新されたことをお知らせします。新しいステータス:',
    'Click'                                  => '',
    'here'                                   => 'ここをクリック',
    'to logon to the'                        => 'してログインしてください。',
    'site now'                               => '',
    'You are being sent this email because you just registered, your logon details are as follows' => '仮登録の完了メールです。下記登録内容をご確認ください。',
    'Your username is'                       => 'ユーザー名',
    'Your password is'                       => 'パスワード',
    'Your registration is being reviewed'    => '登録が承認されるまでお待ちください。',
    'Password Reminder'                      => 'パスワードリマインダー',
    'You are being sent this email because'  => 'パスワードリマインダーによってリクエストされたメールです。',
    'Your new password is'                   => '新しいパスワード',
    'New Registration at'                    => '新規登録:',
    'The following user has just registered' => '下記ユーザーが新規登録されました。',
    'to enable the new users account'        => 'をクリックしてユーザーアカウントを有効にしてください',

    // not found anywhere, but I suppose, that somewhere it is used
    'There was a problem with your session, please login again'      => 'セッションがタイムアウトしました。再度ログインしてください',
    'Only logged on users have access to this area, please register' => 'このページはログインユーザー以外アクセスできません。まず登録を行ってください。',
    'Registration has been disabled'                                 => '登録は行えません',
);

?>