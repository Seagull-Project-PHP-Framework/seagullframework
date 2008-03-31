<?php
$aSections = array(
    array(
        'title'         => 'Manage Translations',
        'parent_id'     => SGL_NODE_ADMIN,
        'uriType'       => 'dynamic',
        'module'        => 'translation',
        'manager'       => 'TranslationMgr.php',
        'actionMapping' => '',
        'add_params'    => '',
        'is_enabled'    => 1,
        'perms'         => 'SGL_ADMIN',
    ),
    array(
        'title'         => 'Summary',
        'parent_id'     => SGL_NODE_GROUP,
        'uriType'       => 'dynamic',
        'module'        => 'translation',
        'manager'       => 'TranslationMgr.php',
        'actionMapping' => 'summary',
        'add_params'    => '',
        'is_enabled'    => 1,
        'perms'         => 'SGL_ADMIN',
    ),

    /**
     * @todo add SGL_TRANSLATOR constant to init.php
     * @todo add SGL_NODE_TRANSLATOR constant to init.php
     */
    array(
        'title'         => 'Manage Translations',
        'parent_id'     => 5,
        'uriType'       => 'dynamic',
        'module'        => 'translation',
        'manager'       => 'TranslationMgr.php',
        'actionMapping' => '',
        'add_params'    => '',
        'is_enabled'    => 1,
        'perms'         => 3,
    ),
    array(
        'title'         => 'Summary',
        'parent_id'     => SGL_NODE_GROUP,
        'uriType'       => 'dynamic',
        'module'        => 'translation',
        'manager'       => 'TranslationMgr.php',
        'actionMapping' => 'summary',
        'add_params'    => '',
        'is_enabled'    => 1,
        'perms'         => 3,
    )
);
?>