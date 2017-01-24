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
// Admin Menu
    array (
          'title'           => 'Users and security',
          'parent_id'       => SGL_NODE_ADMIN,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'UserMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    array (
          'title'           => 'Manage users',
          'parent_id'       => SGL_NODE_GROUP,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'UserMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    array (
          'title'           => 'Manage permissions',
          'parent_id'       => SGL_NODE_GROUP,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'PermissionMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    array (
          'title'           => 'Manage roles',
          'parent_id'       => SGL_NODE_GROUP,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'RoleMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    array (
          'title'           => 'Manage preferences',
          'parent_id'       => SGL_NODE_GROUP,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'PreferenceMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    //array (
    //      'title'           => 'Manage organisations',
    //      'parent_id'       => SGL_NODE_GROUP,
    //      'uriType'         => 'dynamic',
    //      'module'          => 'user',
    //      'manager'         => 'OrgMgr.php',
    //      'actionMapping'   => '',
    //      'add_params'      => '',
    //      'is_enabled'      => 1,
    //      'perms'           => SGL_ADMIN,
    //        ),
    // Admin Menu
    array (
          'title'           => 'My Account',
          'parent_id'       => SGL_NODE_ADMIN,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'AccountMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    array (
          'title'           => 'Summary',
          'parent_id'       => SGL_NODE_GROUP,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'AccountMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    array (
          'title'           => 'View Profile',
          'parent_id'       => SGL_NODE_GROUP,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'AccountMgr.php',
          'actionMapping'   => 'viewProfile',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    array (
          'title'           => 'Edit Preferences',
          'parent_id'       => SGL_NODE_GROUP,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'UserPreferenceMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_ADMIN,
            ),
    // User Menu
    array (
          'title'           => 'My Account',
          'parent_id'       => SGL_NODE_USER,
          'uriType'         => 'dynamic',
          'module'          => 'user',
          'manager'         => 'AccountMgr.php',
          'actionMapping'   => '',
          'add_params'      => '',
          'is_enabled'      => 1,
          'perms'           => SGL_MEMBER,
            ),
);
?>