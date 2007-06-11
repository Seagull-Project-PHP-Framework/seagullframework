<?php

$words = array(

/* BlockMgr */

    // page title
    'Blocks Manager' => 'Управление блоками',

    // actions
    'Browse'         => 'Просмотр',
    'Edit block'     => 'Редактирование блока',
    'New block'      => 'Новый блок',
    'Reorder blocks' => 'Порядок блоков',

    // list
    'Block list' => 'Список блоков',
    'No section' => 'Нет раздела',
    'Unassigned' => 'Не присвоен',
    'Order'      => 'Порядок',

    // add / edit - details tab
    'Block Details'                => 'Описание',
    'Display Title'                => 'Заголовок',
    'Block title tooltip'          => 'Название блока, показывается в заголовке',
    'Block Class Name'             => 'Имя класса',
    'Block class name tooltip'     => 'Один из заранее созданных классов - физическая составляющая блока',
    'Choose class name'            => 'Выберите класс',
    'Block name'                   => 'Название',
    'Block description'            => 'Описание',
    'Title class'                  => 'Класс "title"',
    'Body class'                   => 'Класс "body"',
    'Cache status'                 => 'Кэширование',
    'check to cache block content' => 'выбрать для активации кэширования содержания блока',

    // publishing tab
    //'Publishing'   => 'Расположение',
    'Position'     => 'Позиция',
    'Sections'     => 'Разделы',
    'All sections' => 'Все разделы',
    'Can view'     => 'Видимость',
    'All roles'    => 'Все роли',

    // params tab
    'Block Parameters' => 'Параметры',

    // reorder
    'Left column'  => 'Левая колонка',
    'Right column' => 'Правая колонка',

    // messages
    'Block successfully added'                             => 'Блок успешно добавлен',
    'Block details successfully updated'                   => 'Блок успешно отредактирован',
    'The selected block(s) have successfully been deleted' => 'Выбранные блоки успешно удалены',
    'There is no block to delete'                          => 'Нет блока для удаления',

    // validate
    'Please select a class name' => 'Пожалуйста, укажите имя класса',
    'Please fill in a title'     => 'Пожалуйста, укажите заголовок',
    'Please select a section(s)' => 'Пожалуйста, укажите разделы',
    'Please select a role(s)'    => 'Пожалуйста, укажите видимость',

    // unsorted (may be used in future)
    'Block' => 'Блок',


/* BLOCKS */

    // breadcrumbs block
    'Start parent node'                                       => 'Раздел родителя',
    'Parent node will not be shown'                           => 'ID родителя (раздел родителя не показывается)',
    'Breadcrumbs will be rendered with this template'         => 'Шаблон для рендеринга Breadcrumbs',

    // article block
    'Static Html article'                                     => 'Статичная HTML статья',
    'Set id of static Html article'                           => 'Укажите ID статичной HTML статьи',
    'Article will be rendered with this template'             => 'Шаблон для рендеринга статьи',
    'Template name'                                           => 'Название шаблона',
    'Show unpublished article'                                => 'Показывать неопубликованную статью',
    'Unpublished article will be not shown unless allowed'    => 'Неопубликованная статья не показывается, пока не разрешено',

    // navigation block
    'Start root node'                                         => 'Корневой раздел',
    'Start rendering the tree from this node ID'              => 'Укажите ID корнегого раздела, с которого начинается рендеринг дерева',
    'Start rendered level'                                    => 'Начальный уровень рендеринга',
    'Nodes will be rendered starting from level 0 by default' => 'По умолчанию рендеринг разделов происходит, начиная с нулевого уровня',
    'How many levels to render'                               => 'Количество уровней для рендеринга',
    'To render all levels set to 0'                           => 'Чтобы рендеринг задействовал все уровни, укажите 0',
    'Collapsible mode'                                        => 'Свертывание разделов',
    'When collapsible mode is enabled, children will only '.
    'be displayed when parent is current'                     => 'При включенном "свертывании разделов" подразделы показываются только тогда, когда родительский раздел является текущим',
    'Show always'                                             => 'Показывать всегда',
    'If yes, navigation menu will always be shown, even if '.
    'a different branch of navigation tree is current'        => 'Если данная опция включена, то меню навигации показывается всегда, даже если другая ветвь навигации является текущей', // nemnogo zaputanno... nado skazat' kak-to po prosche
    'Use cacheable navigation menu'                           => 'Кэширование',
    'If no, navigation menu will be recalculated every time'  => 'При отключении кэширования меню вычисляется при каждом запросе',
    'Generate breadcrumbs objects'                            => 'Генерация объектов breadcrumb',
    'Tells navigation driver to generate bredcrumbs objects'  => 'Указывает драйверу навигации генерировать объекты bredcrumb',
    'Navigation Html renderer class'                          => 'Класс HTML-рендеринга',
    'Indicate renderer class from modules/navigation/'.
    'classes/*Renderer.php'                                   => 'Укажите класс рендерига из modules/navigation/classes/*Renderer.php',
    'Template name (optional)'                                => 'Название шаблона (не обязательно)',
    'For navigation renderer which requires template'         => 'Шаблон использует класс HTML-рендеринга',

    // login2 block
    'Login template name'  => 'Шаблон для рендеринга формы входа', // кто-то может придумать лучше?
    'Logout template name' => 'Шаблон для рендеринга формы выхода',

    // show rss block
    'RSS resource'            => 'RSS ресурс',
    'URI of RSS feed'         => 'URI ресурса RSS',
    'Items to show'           => 'Показывать единиц',
    'Number of Items to show' => 'Количество записей для показа',
    'News list will be rendered with this template' => 'Генерировать список новостей, используя данный шаблон',

    // html block
    'Html text' => 'HTML текст',

    // LangSwitcher2 block
    'Image extension for flags' => 'Расширение картинок для флагов',
);

?>