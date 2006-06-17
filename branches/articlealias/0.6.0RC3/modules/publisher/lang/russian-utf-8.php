<?php
$words = array(

/* Article MGR */
    
    // titles
    'Article Manager'         => 'Управление статьями',
    'Article Manager :: Add'  => 'Управление статьями :: добавление',
    'Article Manager :: Edit' => 'Управление статьями :: редактирование',
    'Article Browser'         => 'Просмотр статей',
    
    // list
    'New Article'             => 'Добавить статью',
    'Article filter'          => 'Фильтр',
    'Article type'            => 'Тип статьи',
    'Article list'            => 'Список статей',
    'Article Name'            => 'Название',
    'Date created'            => 'Дата создания',
    'Start Date'              => 'Дата начала',
    'Expiry Date'             => 'Дата окончания',
    'Modif. By'               => 'Редактировано',
    'approve'                 => 'утвердить',
    'publish'                 => 'опубликовать',
    'archive'                 => 'архивировать',
    'For Approval'            => 'для утверждения',
    'Being Edited'            => 'редактируется',
    'Approved'                => 'утверждено',
    'Published'               => 'опубликовано',
    'Archived'                => 'архивировано',
    'Deleted'                 => 'удалено',
    'article'                 => 'статья',
    
    // add / edit
    'Content'                 => 'Содержание',
    'Editing options'         => 'Настройки',
    'Author'                  => 'Автор',    
    'No other text entered'   => 'Текст не введен',
    'Flesch score'            => 'Flesch Score', // ???
    'No expire'               => 'Не устаревает',
        
    // messages
    'Article successfully added'                             => 'Статья успешно добавлена',
    'Article successfully updated'                           => 'Статья успешно отредактирована',
    'Article status has been successfully changed'           => 'Статус статьи успешно сменен',
    'The selected article(s) have successfully been deleted' => 'Выбранные статьи успешно удалены',

    
/* ArticleView MGR */
    
    // view / summary
    'Current Category'        => 'Текущая категория',
    'No lead article found'   => 'Основная статья не найдена',
    'No articles found'       => 'Статей не найдено',
    'No documents found'      => 'Документов не найдено',
    'Related Articles'        => 'См. также (статьи)',
    'Related Documents'       => 'См. также (документы)',
    'contributed by'          => 'Автор', // perevod ne po soderzhaniju, a po smyslu
    'full story'              => 'Полный текст', // perevod ne po soderzhaniju, a po smyslu
    
    
/* Document MGR */

    // titles
    'Document Manager'        => 'Управление файлами',
    
    // list
    'New Asset'               => 'Добавить файл',
    'Document filter'         => 'Фильтр',
    'select a category'       => 'Выберите категорию',
    'Document list'           => 'Список файлов',
    'showing results for'     => 'Показывать', // perevod ne po soderzhaniju, a po smyslu
    'Asset Name'              => 'Название',
    'Size'                    => 'Размер',
    'Type'                    => 'Тип',
    'Date Added'              => 'Добавлено',
    'Owner'                   => 'Добавил',
    'Download'                => 'Скачать',
    'Document type'           => 'Тип документа',
    'asset'                   => 'файл',
    'choose'                  => 'выбрать',
    'whole DB'                => 'все категории',
    'this category'           => 'текущая категория',
    'preview'                 => 'просмотр',
    
    // add / edit
    'Editing Asset'           => 'Редактирование файла',
    'Locate'                  => 'Файл', // perevod ne po soderzhaniju, a po smyslu
    'Mime Type'               => 'Mime тип',
    'Upload'                  => 'Загрузить',
    'Category'                => 'Категория',
    'Change Category'         => 'Изменить категорию',
    'Description'             => 'Описание',
    
    // messages
    'The asset has successfully been added'       => 'Файл успешно добавлен',
    'The asset has successfully been deleted'     => 'Выбранные файлы успешно удалены',
    'The asset has successfully been updated'     => 'Файл успешно отредактирован',
    
    // validate
    'You must select a file to upload'                   => 'Пожалуйста, укажите файл для загрузки',
    'Error: Not a recognised file type'                  => 'Ошибка: неизвестный тип файла',
    'Error: A file with this name already exists'        => 'Ошибка: файл с таким именем существует',
    'There was an error attempting to download the file' => 'Ошибка при загрезке файла',
    'The specified file does not appear to exist'        => 'Указанный файл не существует',
    
    // form alerts
    'Please wait until upload is finished' => 'Пожалуйста, подождите пока файл не загрузится',
    'Please select a file to upload'       => 'Пожалуйста, укажите файл для загрузки',
    'Please wait while document uploads'   => 'Пожалуйста, подождите... файл загружается',
    

/* WikiScrape MGR */
    
    // titles
    'WikiScrape Manager' => 'Управление WikiScrape',
    

/* ContentType MGR */

    //
    // TODO: filter following messages (delete obsolete), 
    //       cheeck ContentType templates, when they will be ready
    //

    // titles
    'Content Type Manager' => 'Content Type Manager',

    // types
    'Title'    => 'Заголовок',
    'BodyHtml' => 'HTML текст',
    'NewsHtml' => 'Текст',
    
    'select a type to create a new article' => 'укажите тип новой статьи',
    'Current View Wysiwyg' => 'Current View Htmlers', // ?
    'View Wysiwyg' => 'View Wysiwyg', // ?
    'Current View Html' => 'Current View Html', // ?
    'View Html' => 'View Html', // ?
    'Select Font' => 'Выбрать Шрифт',
    
    'Content Type Manager' => 'Content Type Manager',
    'Number of fields' => 'Number of fields',
    'field' => 'field',
    'New content type' => 'New content type',
    'Give a name to this field and select its type' => 'Give a name to this field and select its type',
    'Add Type' => 'Add Type',
    'With selected content type(s)' => 'With selected content type(s)',
    'content type has successfully been added' => 'content type has successfully been added',
    'content type has successfully been deleted' => 'content type has successfully been deleted',        
    'content type has successfully been updated' => 'content type has successfully been updated',
    'Through the Publisher module Seagull allows you to create three types of content.This is easily customisable however only 3 types will be discussed here:'=>'Through the Publisher module Seagull allows you to create three types of content.This is easily customisable however only 3 types will be discussed here:',
    'What you see when you click the Articles tab in the front end is a document collection. Creating articles of type Html Article allows you to place your content in a hierarchy that you build using the Categories button above.  This can be useful for intranet applications, or if you have a large body of work that needs to be categorised. Document collection articles will be displayed with all articles from the same category appearing in the Related Articles box  on the right. Similarly, all files uploaded to the same category with the Document Manager will appear in the Related Documents box.'=>'What you see when you click the \'Articles\' tab in the front end is a document collection. Creating articles of type \'Html Article\' allows you to place your content in a hierarchy that you build using the \'Categories\' button above. This can be useful for intranet applications, or if you have a large body of work that needs to be categorised. Document collection articles will be displayed with all articles from the same category appearing in the \'Related Articles\' box on the right. Similarly, all files uploaded to the same category with the Document Manager will appear in the \'Related Documents\' box.',
    'However, if you want to make standalone pages that will be linked to by their own tab, please use the Static Html Article type.  In order to create the navigation that will link to these static pages, please use the'=>'However, if you want to make standalone pages that will be linked to by their own tab, please use the \'Static Html Article\' type.  In order to create the navigation that will link to these static pages, please use the',
    'Finally, you can create news items by choosing the News Item type, these will appear in the left hand column in the Site News box.  These articles (and all others) can be retired automatically according to the date constraints you set on the item.'=>'Finally, you can create news items by choosing the News Item type, these will appear in the left hand column in the Site News box.  These articles (and all others) can be retired automatically according to the date constraints you set on the item.',
    'You can also set permissions on who can view the content using the Permissions button above.'=>'You can also set permissions on who can view the content using the \'Permissions\' button above.',
    'more info'=>'more info',
    'Contributed by' =>'Contributed by',
    'Please select an article type' => 'Please select an article type',
);
?>