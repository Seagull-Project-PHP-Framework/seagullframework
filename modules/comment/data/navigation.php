<?php

$aSections = array(

        //  admin
    array (
      'title'           => 'Comments',
      'parent_id'       => SGL_NODE_ADMIN,
      'uriType'         => 'dynamic',
      'module'          => 'comment',
      'manager'         => 'CommentMgr.php',
      'actionMapping'   => '',              // eg: "edit"
      'add_params'      => '',              // eg: "frmArticleID/23"
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Manage Comments',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'comment',
      'manager'         => 'CommentMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    );
?>