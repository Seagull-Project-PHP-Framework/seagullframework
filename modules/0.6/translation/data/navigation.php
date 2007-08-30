<?php
$aSections = array(
    array (
        'title'         => 'Manage Translations',
        'parent_id'     => SGL_NODE_TRANSLATOR,
        'uriType'       => 'dynamic',
        'module'        => 'translation',
        'manager'       => 'TranslationMgr.php',
        'actionMapping' => '',
        'add_params'    => '',
        'is_enabled'    => 1,
        'perms'         => 'SGL_ADMIN,SGL_TRANSLATOR',
    ),
    array(
        'title'         => 'Summary',
        'parent_id'     => SGL_NODE_TRANSLATOR,
        'uriType'       => 'dynamic',
        'module'        => 'translation',
        'manager'       => 'TranslationMgr.php',
        'actionMapping' => 'summary',
        'add_params'    => '',
        'is_enabled'    => 1,
        'perms'         => 'SGL_ADMIN,SGL_TRANSLATOR',
    ),
    array (
      'title'           => 'Edit Preferences',
      'parent_id'       => SGL_NODE_TRANSLATOR,
      'uriType'         => 'dynamic',
      'module'          => 'user',
      'manager'         => 'UserPreferenceMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => 'SGL_TRANSLATOR',
        ),
);
?>