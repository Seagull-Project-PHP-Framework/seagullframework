<?php
if (!defined('SGL_ROLE_TRANSLATOR')) {
    /**
     * Translator role.
     *
     * @param integer
     */
    define('SGL_ROLE_TRANSLATOR', 3);

    /**
     * Root node for translator's navigation branch.
     *
     * @param integer
     */
    define('SGL_NODE_TRANSLATOR', 5);
}
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
    ),

    /**
     * Nodes for translator navigation branch.
     */
    array(
        'title'         => 'Manage Translations',
        'parent_id'     => SGL_NODE_TRANSLATOR,
        'uriType'       => 'dynamic',
        'module'        => 'translation',
        'manager'       => 'TranslationMgr.php',
        'actionMapping' => '',
        'add_params'    => '',
        'is_enabled'    => 1,
        'perms'         => SGL_ROLE_TRANSLATOR,
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
        'perms'         => SGL_ROLE_TRANSLATOR,
    )
);
?>