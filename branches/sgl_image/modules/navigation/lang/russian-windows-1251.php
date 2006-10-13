<?php
$words = array(

/* SECTION MGR */

    // titles
    'Section Manager'           => 'Управление разделами',
    'Section Manager :: Add'    => 'Управление разделами', // wrong value in souce keyword
    'New section'               => 'Новый раздел',
    'Reorder sections'          => 'Порядок разделов',
    'Add section'               => 'добавление раздела',
    'Edit section'              => 'редактирование раздела',
    'Browse'                    => 'просмотр',
    
    // list
    'Resource URI'              => 'URI ресурса',
    'Order'                     => 'Порядок',
    'Parent ID'                 => 'ID родителя',
    'Alias'                     => 'Alias', // est' kakoj-to korrektnyj perevod?
    
    // add / edit
    
    // Section tab
    'Section info'              => 'Описание', // curreсt key would be "section details" as in block module
    'Section Title'             => 'Название',
    'Parent Section'            => 'Родительский раздел',
    'Top level (no parent)'     => 'Корневой уровень (нет родителя)',
    'Target'                    => 'Тип (target)',
    // link types
    'static articles'           => 'статичное содержание',
    'dynamic sections'          => 'динамичное содержание',
    'external URI'              => 'внешний URI',
    'link to section'           => 'ссылка на раздел',
    'addon'                     => 'добавление (add-on)',
    'empty link'                => 'пустая ссылка',
    'static article title'      => 'Название статичной статьи',
    'Module'                    => 'Модуль',
    'Manager'                   => 'Мanager-класс',
    'none'                      => 'не указано',
    'Additional params'         => 'Дополнительные параметры',
    'separate with slashes (/)' => 'Разделяйте параметры знаком /',
    'Anchor'                    => 'Якорь (anchor)',
    'just the anchor name'      => 'Только имя якоря',
    'Add an alias'              => 'Добавить alias',
    'Automatic alias'           => 'Автоматический alias',
    'alias URI'                 => 'Alias URI', // est' kakoj-to korrektnyj perevod?
    'Choose section'            => 'Укажите раздел',
    'Addon class name'          => 'Имя класса добавления',
    'Choose class name'         => 'Укажите имя класса',
    'Addon description'         => 'Описание добавления',
    'External section URI'      => 'URI внешнего раздела',
    // Editing tab
    'Editing options'           => 'Видимость',
    'Publish'                   => 'Показывать',
    'Can view'                  => 'Видимость',
    'All roles'                 => 'Все роли',
    'Select roles to which you want to grant access' => 'Укажите роли, которые будут видеть данный раздел',
    // Optimisation tab
    'Optimisation'              => 'Оптимизация',
    'Access Key'                => 'Access key',
    'Rel Marker'                => 'Rel marker',
    'Any number, which can be pressed with the ALT-key to load the page.'                            => 'Любой символ, который может быть нажат комбинацией клавиш ALT+символ для загрузки страницы',
    'Additional navigation aids for better accessibility. Use values like "home", "prev" or "next".' => 'Вспомогательное средство для лучшего доступа. Используйте такие значения, как "home", "prev" или "next"',
    
    // validate
    'Please fill in a title'                                                 => 'Пожалуйста, укажите название раздела',
    'You cannot activate unless you first activate.'                         => 'Вы не можете активировать раздел "%1", т.к. не активирован раздел "%2"',
    'To access this section, a user must have access to the parent section.' => 'Невозможно указать выбранные права для текущей страницы, т.к. страница "%s" не имеет выбранных прав',
    
    // messages
    'Sections reordered successfully'                                        => 'Разделы успешно упорядочены',
    'Section successfully added'                                             => 'Раздел успешно добавлен',
    'Section details successfully updated'                                   => 'Раздел успешно отредактирован',
    'Section details updated, no data changed'                               => 'Раздел успешно отредактирован, данные не изменены',
    'The selected section(s) have successfully been deleted'                 => 'Выбранные разделы успешно удалены.',
    'Section successfully added but alias creation failed as there can '.
    'be no duplicates'                                                       => 'Раздел успешно добавлен, но URL alias не был создан, т.к. alias с указанным названием уже существует',
    'Section details successfully updated but alias creation failed as '.
    'there can be no duplicates'                                             => 'Раздел успешно отредактирован, но URL alias не был создан, т.к. alias с указанным названием уже существует',
    

/* NavStyle MGR */
    
    // title
    'Navigation Style Manager'  => 'Управление стилем навигации',

    // list
    'current style, previewed above'                             => 'текущий стиль, показанный выше, в рамке предварительный просмотр',
    'preview'                                                    => 'предварительный просмотр',
    'Navigation menu preview as displayed to the following role' => 'Предварительный просмотр меню навигации показан для следующей роли',
    'Style Name'                                                 => 'Название стиля',
    'Last modified'                                              => 'Последние изменения',
    'return to navigation manager'                               => 'Вернуться к управлению разделами',
    
    // messages
    'Current style successfully changed'                         => 'Текущий стиль успешно изменен',


/* Category MGR */

    // titles
    'Category Manager' => 'Управление категориями',
    
    // Messages
    'Category details successfully updated'                  => 'Категория успешно отредактирована',
    'The category has successfully been deleted'             => 'Выбранная категория успешно удалена',
    'Categories successfully reordered'                      => 'Категории успешно упорядочены',
    'Categories reordered successfully'                      => 'Категории успешно упорядочены',
    'do not delete root category'                            => 'Невозможно удалить корневую директорию',
    
    // list
    'Add Category'                                           => 'Добавить категорию',
    'Add Root Category'                                      => 'Добавить корневую категорию',
    'category'                                               => 'категория',
    'Please choose a category from the left to edit'         => 'Пожалуйста, выберите категорию справа для редактирования',
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
);
?>