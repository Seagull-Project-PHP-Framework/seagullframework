<?php
    $words = array(

/* Account MGR */

    // title
    'My Account'                   => 'Мой аккаунт',
    'My Profile :: Edit'           => 'Мой профиль :: редактирование',
    
    // summary form (admin template)
    'Role'                         => 'Роль',
    'Date Registered'              => 'Зарегистрирован',
    'Last Login'                   => 'Последний вход',
    'first login in progress'      => 'зашел в первый раз (сейчас на сайте)',
    'Current IP Address'           => 'Текущий IP адрес',
    'change password'              => 'Сменить пароль',
    // + user template
    'My Profile'                   => 'Мой профиль',
    'Preferences'                  => 'Настройки',
    'Password'                     => 'Пароль',
    'edit preferences'             => 'Редактировать',
    'view profile'                 => 'Просмотр', // po smyslu
    'No results found for that ID' => 'Нет результата для данного ID',

    // profile form (admin template)
    'Personal Details'        => 'Личная информация',
    'Username'                => 'Имя пользователя',
    'First Name'              => 'Имя',
    'Last Name'               => 'Фамилия',
    'Contact'                 => 'Контакты',
    'Location'                => 'Местонахождение',
    'Address 1'               => 'Адрес 1',
    'Address 2'               => 'Адрес 2',
    'Address 3'               => 'Адрес 3',
    'City'                    => 'Город',
    'Country'                 => 'Страна',
    'Telephone'               => 'Телефон',
    'Mobile'                  => 'Мобильный телефон',
    'County/State/Province'   => 'Округ / штат / провинция',
    'ZIP/Postal Code'         => 'ZIP / почтовый индекс',
    // + user template
    'Company'                 => 'Компания',
    'edit these values'       => 'Редактировать',
    
    // edit form
    'edit user'          => 'редактирование пользователя',
    'Confirm Password'   => 'Подтвердить пароль',
    'Organisation name'  => 'Организация',
    'Is Active?'         => 'Активен',
    'Security'           => 'Безопасность',
    'Security question'  => 'Секретный вопрос',
    'Answer'             => 'Ответ',

    'aSecurityQuestions' => array(
        0 => '',
        1 => 'Кличка любимого домашнего животного',
        2 => 'Памятная дата (дд/мм/гггг)',
        3 => 'Памятное место',
        4 => 'Девичья фамилия матери',
        5 => 'Название любимого фильма',
        6 => 'Название любимой песни',
        7 => 'Название любимого коктейля'
    ),
    
    // messages
    'profile successfully updated'          => 'Профиль успешно отредактирован',
    
   
    // validate
    'You must enter a username'             => 'Пожалуйста, укажите имя пользователя',
    'username min length'                   => 'Имя пользователя должно быть не меньше пяти символов',
    'You must enter at least address 1'     => 'Пожалуйста, укажите адрес 1',
    'You must enter your city'              => 'Пожалуйста, укажите город',
    'You must enter your state/province'    => 'Пожалуйста, укажите округ / штат / провинцию',
    'Please enter a company name'           => 'Пожалуйста, укажите компанию',
    'You must enter your ZIP/Postal Code'   => 'Пожалуйста, укажите ZIP / почтовый индекс',
    'You must enter your country'           => 'Пожалуйста, укажите страну',
    'Your email is not correctly formatted' => 'Адрес эл. почты не корректен',
    'You must enter your email'             => 'Пожалуйста, укажите адрес эл. почты',
    'You must choose a security question'   => 'Пожалуйста, укажите секретный вопрос',
    'Please enter a telephone number'       => 'Пожалуйста, укажите телефонный номер',
    'You must provide a security answer'    => 'Пожалуйста, укажите секретный ответ',
    

/* Register MRG */

    // title
    'Register' => 'Регистрация',
    
    // validate
    'You must enter a password'                                    => 'Пожалуйста, укажите пароль',
    'Please confirm password'                                      => 'Пожалуйста, подтвердите пароль',
    'Password must be between 5 to 10 characters'                  => 'Пароль должен быть не меньше пяти символов и не больше десяти',
    'Passwords are not the same'                                   => 'Пароли не совпадают',
    'This email already exist in the DB, please choose another'    => 'Этот адрес эл. почты уже существует, пожалуйста, укажите другой',
    'This username already exist in the DB, please choose another' => 'Это имя пользователя уже существует, пожалуйста, укажите другое',
    
    // messages
    'user successfully registered' => 'Вы успешно зарегистрированы! На указанный адрес эл. почты было выслано письмо для подтверждения регистрации.',
    
    
/* Password MRG */

    // titles
    'Retrieve password' => 'Восстановить пароль',
    
    // list
    'Enter the email address you registered with' => 'Введите адрес эл. почты, который был указан при регистрации',
    'retrieve' => 'Восстановить',
    

/* UserPassword */

    // titles
    'Change Password' => 'Смена пароля',

    // form
    'Original password'  => 'Текущий пароль',
    'New password'       => 'Новый пароль',
    'Confirm'            => 'Подтвердить',
    'Notify me by email' => 'Уведомление на эл. адрес',
    
    // messages
    'Password updated successfully' => 'Пароль успешно отредактирован',
    
    // validate
    'You must enter your original password'                 => 'Пожалуйста, укажите текущий пароль',
    'You must enter a new password'                         => 'Пожалуйста, укажите новый пароль',
    'You have entered your original password incorrectly'   => 'Вы ввели неправильный текущий пароль',
    
    
/* Login MRG */

    // title
    'Login' => 'Вход',
    
    // form
    'Authorisation Required' => 'Требуется авторизация',
    
    // validate
    'username/password not recognized' => 'Неверное имя пользователя и/или пароль',
    

/* UserPreference MGR */

    // title
    'User Preferences' => 'Настройки пользователя',
    
    // form (admin template)
    'Theme'                                        => 'Тема',
    'Change what this site looks like'             => 'Изменяет внешний вид сайта',
    'Date format'                                  => 'Формат даты',
    'UK format is dd/mm/yyyy and US is mm/dd/yyyy' => 'Формат Великобритании - дд/мм/гггг, формат США - мм/дд/гггг',
    'Interface language'                           => 'Язык интерфейса',
    'Session timeout'                              => 'Таймаут сессии',
    'Session timeout tooltip'                      => 'Сессия прекращает свое действие после закрытия окна браузера, однако, Вы можете установить продолжительность сессии',
    'Locale'                                       => 'Локаль',
    'Timezone'                                     => 'Временная зона',
    'Results per page'                             => 'Записей на странице',
    'Show execution times?'                        => 'Показывать время выполнения',
    // + user template
    'General'                                      => 'Основные',
    
    // messages
    'details successfully updated' => 'Настройки пользователя успешно сохранены',
    
    
/* Profile MGR */

    // titile
    'User Profile' => 'Профиль пользователя',
    
    // form
    'Real Name'                => 'Имя',
    'Lives in'                 => 'Местонахождение',
    'Member Since'             => 'Зарегистрирован',
    'none given'               => 'не указано',
    'Posting Stats for User'   => 'Статистика текстов на сайте',
    'Total Articles'           => 'Всего статей',
    'Total Comments'           => 'Всего комментариев',
    'coming soon ...'          => 'в разработке...',
    // messaging
    'back to contacts'         => 'назад в контакты',
    'Message'                  => 'Сообщение',
    'send message'             => 'послать сообщение',
    'Contacts'                 => 'Контакты',
    'add to contacts'          => 'добавить в контакты',
    
    
/* OrgPreferences MGR */

    // title
    'Organisation Preferences' => 'Настройки организации',
    
    // form
    'Preferences for org' => 'Настройки организации',
    
    // messages
    'org details successfully updated' => 'Настройки организации успешно отредактированы',
    
    
/* OrgType MGR */

    // titles
    'OrgType Manager'           => 'Управление типами организаций',
    'OrgType Manager :: Browse' => 'Управление типами организаций :: Просмотр',
    'OrgType Manager :: Add'    => 'Управление типами организаций :: Добавление',
    'OrgType Manager :: Edit'   => 'Управление типами организаций :: Редактирование',

    // list
    'Organisation Type list'    => 'Список типов организаций',
    'Add organisation type'     => 'Добавить тип организации',
    'organisation type'         => 'тип организации',
    'Be Careful!'               => 'Осторожно!',
    
    // form
    'New organisation'          => 'Новая организация',
    'Edit organisation'         => 'Редактирование организации',
   
    // validate
    'You must enter an organisation type name' => 'Пожалуйста, укажите имя типа организации',
    
    // messages
    'No data was updated'                             => 'Данные не изменены',
    'Org type(s) deleted successfully'                => 'Выбранные типы организаций успешно удалены',
    'Organisation type saved successfully'            => 'Тип организации успешно сохранен',
    'Organisation type has been updated successfully' => 'Тип организации успешно отредактирован',

    
/* Org MGR */

    // titile
    'Organisation Manager'           => 'Управление организациями',
    'Organisation Manager :: Browse' => 'Управление организациями :: просмотр',
    'Organisation Manager :: Add'    => 'Управление организациями :: добавление',
    'Organisation Manager :: Edit'   => 'Управление организациями :: редактирование',
    
    // list
    'Organisation list'    => 'Список организаций',
    'Website'              => 'Веб-сайт',
    'change'               => 'сменить',
    'organisation'         => 'организация',
        
    // form
    'New organisation'     => 'Новая организация',
    'Edit organisation'    => 'Редактирование организации',
    'Details'              => 'Основная информация', // details
    'Description'          => 'Описание',
    'Parent Org'           => 'Родительская организация',
    'Default Role'         => 'Роль по умолчанию',
    
    // validate
    'You must enter an organisation name' => 'Пожалуйста, укажите имя организации',
    
    // messages
    'organisation successfully added'                                                     => 'Организация успешно добавлена',
    'The selected organisation(s) have successfully been deleted'                         => 'Выбранные организации успешно удалены',
    'The selected organisation cannot be deleted because there are users relating to it'  => 'Выбранная организация "%s" не может быть удалена, т.к. существуют пользователи, относящиеся к ней',
    'The organisation has successfully been updated'                                      => 'Организация успешно отредактирована',
    'The organisation has successfully been updated, no data changed'                     => 'Организация успешно отредактирована, данные не изменены',
    

/* Preference MGR */

    // titles
    'Preference Manager'           => 'Управление настройками',
    'Preference Manager :: Browse' => 'Управление настройками :: просмотр',
    'Preference Manager :: Add'    => 'Управление настройками :: добавление',
    'Preference Manager :: Edit'   => 'Управление настройками :: редактирование',
    
    // list
    'Default value' => 'Значение по умолчанию',
    'preference'    => 'опция',
    
    // form
    'New preference'     => 'Новая опция',
    'Editing preference' => 'Редактирование опции',
    
    // validate
    'You must enter a default value'   => 'Пожалуйста, укажите значение по умолчанию',
    'You must enter a preference name' => 'Пожалуйста, укажите имя опции',
    
    // messages
    'pref successfully deleted' => 'Выбранные настройки успешно удалены',
    'pref successfully updated' => 'Опция успешно отредактирована',
    'pref successfully added'   => 'Опция успешно добавлена',
    
    
/* Permission MGR */

    // titles
    'Permission Manager'                    => 'Управление уровнями доступа',
    'Permission Manager :: Browse'          => 'Управление уровнями доступа :: просмотр',
    'Permission Manager :: Add'             => 'Управление уровнями доступа :: добавление',
    'Permission Manager :: Edit'            => 'Управление уровнями доступа :: редактирование',
    'Permission Manager :: Detect & Add'    => 'Управление уровнями доступа :: обнаружение и добавление',
    'Permission Manager :: Detect Orphaned' => 'Управление уровнями доступа :: удаление устаревших',
    
    // list
    'New permission'           => 'Новый уровень доступа',
    'Editing permission'       => 'Редактирование уровня доступа',
    'detect & add permissions' => 'Обнаружение и добавление',
    'remove orphaned'          => 'Удаление устаревших',
    'filter by module'         => 'Фильтровать по модулю',
    'all'                      => 'Все',
    'permission'               => 'уровень доступа',
    
    // add / edit form
    'Module'                   => 'Модуль',
    
    // detect perms / remove orphaned
    'Detected Perms'             => 'Обнаруженные уровни доступа',
    'Detected Orphaned Perms'    => 'Обнаруженные устаревшие уровни доступа',
    'orphaned perms description' => 'Данные уровни доступа были обнаружены в базе данных, но не существуют в соответствующих модулях.',
    'detected perms description' => 'Данные уровни доступа были обнаружены в модулях, но соотвествующие записи отсутствуют в базе данных.',

    // validate
    'You must enter a permission name' => 'Пожалуйста, укажите название уровня доступа',
    
    // messages
    'perm already defined'                      => 'Уровень доступа с таким названием уже определен',
    'perm successfully added'                   => 'Уровень доступа успешно добавлен',
    'perm(s) successfully added'                => 'Выбранные уровни доступа успешно добавлены',
    'perm successfully deleted'                 => 'Выбранные уровни доступа успешно удалены',
    'perm successfully updated'                 => 'Уровень доступа успешно отредактирован',
    'perm successfully deleted'                 => 'Выбранные уровни доступа успешно удалены',
    
    
/* Role MGR */

    // titles
    'Role Manager'                => 'Управление ролями',
    'Role Manager :: Browse'      => 'Управление ролями :: просмотр',
    'Role Manager :: Add'         => 'Управление ролями :: добавление',
    'Role Manager :: Edit'        => 'Управление ролями :: редактирование',
    'Role Manager :: Permissions' => 'Управление ролями :: уровни доступа',
    
    // list
    'New Role'   => 'Новая роль',
    'duplicate'  => 'дублировать',
    'role'       => 'роль',
    'remove'     => 'удалить',
    
    // add / edit / perms assignment
    'Editing role'                        => 'Редактирование роли',
    'Changing permission assignments for' => 'Смена уровней доступа для роли',
    'Remaining permissions'               => 'Свободные уровни доступа',
    'Selected permissions for'            => 'Выбранные уровни доступа для',
    
    // validation
    'You must enter a role name' => 'Пожалуйста, укажите название роли',
    
    // messages
    'role successfully added'                                                             => 'Роль успешно добавлена',
    'role successfully updated'                                                           => 'Роль успешно отредактирована',
    'role successfully deleted'                                                           => 'Выбранные роли успешно удалены',
    'role successfully deleted but please note, admin/unassigned roles cannot be deleted' => 'Выбранные роли успешно удалены, кроме ролей admin / unassigned, которые являются системными и не могут быть удалены',
    'role successfully duplicated'                                                        => 'Роль успешно дублирована',
    'role assignments successfully updated'                                               => 'Уровни доступа роли успешно отредактированы',
    

/* UserSearch MGR */

    // titles
    'User Manager :: Search' => 'Управление пользователями :: поиск',
    
    // list
    'Search Criteria' => 'Критерии поиска',
    'Role name'       => 'Роль',
    'User ID'         => 'ID пользователя',
    'Organisation'    => 'Организация',
    'Register Date'   => 'Дата регистрации',
    
    // validation
    'You must select a valid date' => 'Пожалуйста, укажите корректную дату',
    'You must select a date'       => 'Пожалуйста, укажите дату',
    
    
/* UserImport MGR */

    // titles
    'User Import Manager' => 'Управление импортом пользователей',
    
    // list
    'userfile'                                                         => 'Исходный файл',
    'Use the document manager to upload CSV files.'                    => 'Используйте модуль управления документами для загрузки CSV-файла',
    'Import users into selected organisation.'                         => 'Импортирование пользователей к выбранной организации',
    'User will be assigned to selected role.'                          => 'Импортирование пользователей к выбранной роли',
    'You should first upload at least one csv file in your upload dir' => 'Для импорта пользователей Вам следует загрузить хотя бы один CSV-файл',
    
    // validation
    'Please select a file.'              => 'Пожалуйста, укажите исходный файл',
    'Please select the organisation'     => 'Пожалуйста, укажите организацию',
    'Please select the role'             => 'Пожалуйста, укажите роль',
    
    
/* User MGR */

    // titles
    'User Manager'                     => 'Управление пользователями',
    'User Manager :: Browse'           => 'Управление пользователями :: просмотр',
    'User Manager :: Login Data'       => 'Управление пользователями :: входы* в систему',
    'User Manager :: Edit'             => 'Управление пользователями :: редактирование',
    'User Manager :: Edit permissions' => 'Управление пользователями :: редактирование уровней доступа',
    'User Manager :: Add'              => 'Управление пользователями :: добавление',
    'User Manager :: Reset password'   => 'Управление пользователями :: сброс пороля',
    'User Manager :: Change status'    => 'Управление пользователями :: смена статуса',
    
    // list
    'New User'             => 'Новый пользователь',
    'Search'               => 'Поиск',
    'Import users'         => 'Импорт пользователей',
    'User list'            => 'Список пользователей',
    'user(s) found'        => 'пользователей найдено',
    'clear search'         => 'убрать поиск',
    'Logins'               => 'Входы*',
    'list'                 => 'список',
    'Change status'        => 'Сменить статус',
    'No users found'       => 'Пользователей не найдено',
    'Synchronise'          => 'Синхронизация',
    '(each users current)' => '(текущая роль пользователей)',
    'sync perms with role' => 'синхронизация ур. доступа с ролью',
    'add missing perms'    => 'добавление недостающих ур. доступа',
    'remove extra perms'   => 'удаление устаревших ур. доступа',
    'complete sync'        => 'полная синхронизация',
    
    // add / edit
    'add user'             => 'Добавление пользователя',
    'check if active'      => 'отметьте для активации',
    
    // login data
    'Connection list'      => 'Cписок входов в систему',
    'Login Time'           => 'Время входа',
    'Remote IP'            => 'Удаленный IP адрес',
    'Are you sure?'        => 'Вы уверены?',
    
    // status change
    'Disable Now'          => 'Отключить',
    'Enable Now'           => 'Включить',
    'active'               => 'активен',
    'disabled'             => 'выключен',
    'Changing status for'  => 'Смена статуса для',
    'Current status is'    => 'Текущий статус:',
    'Enable user'          => 'Активация пользователя',
    'Disable user'         => 'Отключение пользователя',
    'Notify user'          => 'Уведомить пользователя',
    
    // reset
    'Reset password now'     => 'Сменить пароль',
    'Resetting password for' => 'Сменя пароля для',
    'Reset password info'    => 'Внимание! Если Вы сбрасываете пароль администратора, убедитесь, что указан соответствующий адрес эл. почты (воспользуйтесь функцией редактирования). Кроме того, убедитесь, что Ваша система настроена правильно для получения и отсылки эл. почты!',
    
    // change permissions
    'for user id'            => 'для пользователя с ID',
    'Select a module'        => 'Выберите модуль',
    
    // messages
    'Deleted successfully'                 => 'Выбранные записи успешно удалены',
    'Status changed successfully'          => 'Статус пользователя успешно сменен',
    'user successfully added'              => 'Пользователь успешно добавлен',
    'user successfully deleted'            => 'Выбранные пользователи успешно удалены',
    'The user has been successfully added' => 'Пользователь успешно добавлен', // povtor
    

/**
 * Mail templates
 * (mail-shablony ne perevedeny, t.k. v etom net smysla, skoro budut sovsem drugie pis'ma - 
 * s ispol'zovaniem novyh vozmohnostej metoda translate()).
 */
    'Thanks for registering with'            => 'Thanks for registering with',
    'Dear'                                   => 'Dear',
    'Click'                                  => 'Click',
    'here'                                   => 'here',
    'to logon to the'                        => 'to logon to the',
    'site now'                               => 'site now.',
    'Your username is'                       => 'Your username is',
    'Your password is'                       => 'Your password is',
    'Your registration is being reviewed'    => 'Your registration is being reviewed, you will be notified shortly',
    'Password Reminder'                      => 'Password Reminder',
    'You are being sent this email because'  => 'You are being sent this email because you requested a password reminder.',
    'Your new password is'                   => 'Your new password is',
    'New Registration at'                    => 'New Registration at',
    'The following user has just registered' => 'The following user has just registered',
    'to enable the new users account'        => 'to enable the new user\'s account',
    'You are being sent this email because your new account status is now' => 'You are being sent this email because your new account status is now',    
    'You are being sent this email because you just registered, your logon details are as follows' => 'You are being sent this email because you just registered, your logon details are as follows',
    
    // not found anywhere, but I suppose, that somewhere it is used
    'There was a problem with your session, please login again'      => 'There was a problem with your session which may have timed out, please login again',
    'Only logged on users have access to this area, please register' => 'Only logged on users have access to this area, please register',
    'Registration has been disabled'                                 => 'Registration has been disabled',
);
?>