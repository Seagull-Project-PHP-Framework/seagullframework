<?php
/*
Data setup in navigation files such as this one will allow you to add sections
to the 'Admin menu' that only the admin user can see.  There are 2 types of
nodes you can add:

 (1)    a SGL_NODE_ADMIN node
        These will appear at the top level in the admin menu
 (2)    a SGL_NODE_GROUP node
        These will appear as a subsection of the node inserted previously

Using this logic you can create node groups that correspond to modules, with a
SGL_NODE_ADMIN as the designated parent, and all children designated as
SGL_NODE_GROUP nodes.

*/
$aSections = array(
    array (
      'title'           => 'Publishing',
      'parent_id'       => SGL_NODE_ADMIN,
      'uriType'         => 'dynamic',
      'module'          => 'publisher',
      'manager'         => 'ArticleMgr.php',
      'actionMapping'   => '',              // eg: "edit"
      'add_params'      => '',              // eg: "frmArticleID/23"
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,       // role id or constant, eg: 1 for admin
                                            //   multiple possible, as comma-separated string
                                            //   eg: "SGL_GUEST, SGL_MEMBER, SGL_ADMIN"
        ),
    array (
      'title'           => 'Articles',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'publisher',
      'manager'         => 'ArticleMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Categories',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'navigation',
      'manager'         => 'CategoryMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Files',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'publisher',
      'manager'         => 'DocumentMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),

// example of user navigation
//    array (
//      'title'           => 'My Navigation Tab Name',
//      'parent_id'       => SGL_NODE_USER,
//      'uriType'         => 'dynamic',
//      'module'          => 'foomodule',
//      'manager'         => 'FooMgr.php',
//      'actionMapping'   => '',
//      'add_params'      => '',
//      'is_enabled'      => 1,
//      'perms'           => SGL_GUEST,
//        ),
    );
?>