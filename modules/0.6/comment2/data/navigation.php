<?php

$aSections = array(

        //  admin
    array (
      'title'           => 'Comments',
      'parent_id'       => SGL_NODE_USER,
      'uriType'         => 'dynamic',
      'module'          => 'comment2',
      'manager'         => 'ExampleMgr.php',
      'is_enabled'      => 1,
      'perms'           => 'SGL_ADMIN,SGL_GUEST',
        ),
    );
?>
