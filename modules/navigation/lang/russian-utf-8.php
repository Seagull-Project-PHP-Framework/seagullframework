<?php

$words = array(

/* SectionMgr */

    // page titles
    'Section Manager'        => 'Управление разделами',
    'Section Manager :: Add' => 'Управление разделами :: Добавление',

    // actions
    'New section'      => 'Новый раздел',
    'Reorder sections' => 'Порядок разделов',
    'Add section'      => 'Добавление раздела',
    'Edit section'     => 'Редактирование раздела',
    'Browse'           => 'Просмотр',

    // list
    'Resource URI' => 'URI ресурса',
    'Order'        => 'Порядок',
    'Parent ID'    => 'ID родителя',
    'Alias'        => 'Alias', // есть идеи?

    // add / edit - section tab
    'Section info'                                         => 'Описание',
    'Section Title'                                        => 'Заголовок',
    'Parent Section'                                       => 'Родительский раздел',
    'Top level (no parent)'                                => 'Корневой уровень (нет родителя)',
    'specify the section under which yours will be placed' => 'укажите раздел, под которым будет расположен Ваш раздел',
    'Target'                                               => 'Назначение',
    'select where your content is coming from'             => 'укажите тип содержания',
    'pre-existing static content'                          => 'существующий статичный контент',
    'output from specified module'                         => 'вывод указанного модуля',
    'external URI'                                         => 'внешний URI',
    'link to section'                                      => 'ссылка на раздел',
    'addon'                                                => 'добавление (add-on)',
    'empty link'                                           => 'пустая ссылка',
    'static article title'                                 => 'заглавие статичной статьи',
    'Module'                                               => 'Модуль',
    'Manager'                                              => 'Мanager-класс',
    'none'                                                 => 'не указано',
    'Additional params'                                    => 'Дополнительные параметры',
    'separate with slashes (/)'                            => 'разделяйте параметры знаком /',
    'Anchor'                                               => 'Якорь (anchor)',
    'just the anchor name'                                 => 'только имя якоря',
    'Add an alias'                                         => 'Добавить alias',
    'Automatic alias'                                      => 'Автоматический alias',
    'alias URI'                                            => 'alias URI',
    'an alias is just a search-engine-friendly URL'        => 'alias - это дружелюбный для поисковой системы URL, например my-article-name вместо /article/view/frmArticleId/23/',
    'Choose section'                                       => 'Укажите раздел',
    'Addon class name'                                     => 'Имя класса добавления',
    'Choose class name'                                    => 'Укажите имя класса',
    'Addon description'                                    => 'Описание добавления',
    'External section URI'                                 => 'URI внешнего раздела',

    'Select a content'                                     => 'Выберите статью',
    'You must select a valid article'                      => 'Выберите существующую статью. Если таковой нет в списке, пожалуйста, сначала создайте статичную статью',

    // editing tab
    'Editing options' => 'Доступ',
    'Publish'         => 'Показывать',
    'Can view'        => 'Доступ',
    'All roles'       => 'Все роли',
    'Select roles to which you want to grant access' => 'Укажите роли, которые будут иметь доступ к данному раздел',

    // accessibility tab
    'Accessibility' => 'Доступность',
    'Access Key'    => 'Access key',
    'Rel Marker'    => 'Rel marker',
    'Any number, which can be pressed with the ALT-key to load the page.'                            => 'Любой символ, который может быть нажат комбинацией клавиш ALT+символ для загрузки страницы',
    'Additional navigation aids for better accessibility. Use values like "home", "prev" or "next".' => 'Вспомогательное средство для лучшего доступа. Используйте такие значения, как "home", "prev" или "next"',

    // validate
    'Please fill in a title'                                                 => 'Пожалуйста, укажите заголовок',
    'You cannot activate unless you first activate.'                         => 'Вы не можете активировать раздел "%1" пока не активирован раздел "%2"',
    'To access this section, a user must have access to the parent section.' => 'Для доступа к этому разделу пользователь должен иметь доступ к родительскому разделу. Или одна или несколько из выбранных Вами ролей не имеет доступа к разделу "%s"',

    // messages
    'Sections reordered successfully'                                     => 'Разделы успешно упорядочены',
    'Section successfully added'                                          => 'Раздел успешно добавлен',
    'Section details successfully updated'                                => 'Раздел успешно отредактирован',
    'Section details updated, no data changed'                            => 'Раздел успешно отредактирован, данные не изменены',
    'The selected section(s) have successfully been deleted'              => 'Выбранные разделы успешно удалены',
    'Section successfully added but alias creation failed as there can '.
    'be no duplicates'                                                    => 'Раздел успешно добавлен, но URL alias не был создан, т.к. alias с указанным названием уже существует',
    'Section details successfully updated but alias creation failed as '.
    'there can be no duplicates'                                          => 'Раздел успешно отредактирован, но URL alias не был создан, т.к. alias с указанным названием уже существует',


/* NavStyleMgr */

    // title
    'Navigation Style Manager'  => 'Управление стилем навигации',

    // list
    'current style, previewed above'                             => 'текущий стиль, показанный выше, в рамке предварительный просмотр',
    'preview'                                                    => 'предварительный просмотр',
    'Navigation menu preview as displayed to the following role' => 'Предварительный просмотр меню навигации показан для следующей роли',
    'Style Name'                                                 => 'Название стиля',
    'Last modified'                                              => 'Последние изменения',
    'return to navigation manager'                               => 'вернуться к управлению навигацией',

    // messages
    'Current style successfully changed' => 'Текущий стиль успешно изменен',


/* CategoryMgr */

    // titles
    'Category Manager' => 'Управление категориями',

    // list
    'Add Category'                                           => 'Добавить категорию',
    'Add Root Category'                                      => 'Добавить корневую категорию',
    'category'                                               => 'категория',
    'Please choose a category from the left to edit'         => 'Пожалуйста, выберите категорию слева для редактирования',
    'Target Parent Category'                                 => 'Родительская категория',
    'Current Category Name'                                  => 'Имя текущей категории',
    'Has Permissions'                                        => 'Имеет доступ',
    'Yes'                                                    => 'Да',
    'No'                                                     => 'Нет',
    'Permissions are set by default to allow all users into '.
    'all catgories. If you would like to deny a certain '.
    'group access to a category, choose "no" in response '.
    'to "has permissions" for the given group.'              => 'По умолчанию все пользователи имеют доступ ко всем категориям. В случае, если Вы хотите ограничить доступ группе пользователей к выбранной категории, то выбирите опцию "нет" у соответствующей группы',

    // reorder
    'Reorder Categories' => 'Упорядочить категории',
    'Label'              => 'Название',

    // Messages
    'Category details successfully updated'      => 'Категория успешно отредактирована',
    'The category has successfully been deleted' => 'Категория успешно удалена',
    'Categories successfully reordered'          => 'Категории успешно упорядочены',
    'Categories reordered successfully'          => 'Категории успешно упорядочены',
    'do not delete root category'                => 'На данный момент невозможно удалить корневую категорию, попробуйте вместо этого ее переименовать',
);

?>