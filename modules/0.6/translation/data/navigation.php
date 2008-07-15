<?php
$aSections = array(

    /**
     * Nodes for admin navigation branch.
     */
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
    )
);
?>