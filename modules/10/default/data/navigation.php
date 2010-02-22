<?php
$aSections = array(
    array (
      'title'           => 'General',
      'parent_id'       => SGL_NODE_ADMIN,
      'uriType'         => 'dynamic',
      'module'          => 'default',
      'manager'         => 'ModuleMgr.php',
      'actionMapping'   => '',              // eg: "edit"
      'add_params'      => '',              // eg: "frmArticleID/23"
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,       // role id or constant, eg: 1 for admin
                                            //   multiple possible, as comma-separated string
                                            //   eg: "2,4,6,7"
        ),
    array (
      'title'           => 'Manage Modules',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'default',
      'manager'         => 'ModuleMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Configuration',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'default',
      'manager'         => 'ConfigMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Maintenance',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'default',
      'manager'         => 'MaintenanceMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Module Generator',
      'parent_id'       => SGL_NODE_GROUP,
      'uriType'         => 'dynamic',
      'module'          => 'default',
      'manager'         => 'ModuleGenerationMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    );
//  dynamically add PEAR navigation if it's not a minimal install
#disabled until fixed
//if (!SGL::isMinimalInstall()) {
//    $aSections[] =
//    array (
//      'title'           => 'PEAR Packages',
//      'parent_id'       => SGL_NODE_GROUP,
//      'uriType'         => 'dynamic',
//      'module'          => 'default',
//      'manager'         => 'PearMgr.php',
//      'actionMapping'   => '',
//      'add_params'      => '',
//      'is_enabled'      => 1,
//      'perms'           => SGL_ADMIN,
//        );
//}
//  then add rest of sections
$ok = array_push($aSections,
    array (
      'title'           => 'Home',
      'parent_id'       => SGL_NODE_USER,
      'uriType'         => 'dynamic',
      'module'          => 'default',
      'manager'         => 'DefaultMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_GUEST,
        ),
    array (
      'title'           => 'Admin Home',
      'parent_id'       => SGL_NODE_USER,
      'uriType'         => 'dynamic',
      'module'          => 'default',
      'manager'         => 'DefaultMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ),
    array (
      'title'           => 'Admin',
      'parent_id'       => SGL_NODE_USER,
      'uriType'         => 'dynamic',
      'module'          => 'default',
      'manager'         => 'ModuleMgr.php',
      'actionMapping'   => '',
      'add_params'      => '',
      'is_enabled'      => 1,
      'perms'           => SGL_ADMIN,
        ));
?>