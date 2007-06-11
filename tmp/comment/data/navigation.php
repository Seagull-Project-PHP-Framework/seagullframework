<?php

$aSections = array(

        //  admin
    array (
      'title'           => 'Comments',
      'parent_id'       => SGL_NODE_ADMIN,
      'uriType'         => 'dynamic',
      'module'          => 'comment',
      'manager'         => 'CommentSearchMgr.php',
      'actionMapping'   => '',              // eg: "edit"
      'add_params'      => '',              // eg: "frmArticleID/23"
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Search Comments',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'comment',
      'manager'         => 'CommentSearchMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Test Akismet Key',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'comment',
      'manager'         => 'AkismetMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    );
?>
