INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'default', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', 'default/maintenance', '48/module_default.png', 'Demian Turner', NULL, 'BSD', 'stable');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_overview', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_detect', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_install', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_uninstall', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_reinstall', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_deregister', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr_cmd_edit', 'Permission to view and edit config settings', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr_cmd_update', 'Permission to update config values', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'defaultmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'defaultmgr_cmd_list', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr_cmd_send', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_append', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_dbgen', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_clearCache', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_verify', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_checkAllModules', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_rebuildSequences', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_createModule', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_checkLatestVersion', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_rebuildSeagull', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_deleteConfigs', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pearmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pearmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pearmgr_cmd_doRequest', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'moduleconfigmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'moduleconfigmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'moduleconfigmgr_cmd_update', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulegenerationmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulegenerationmgr_cmd_createModule', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulegenerationmgr_cmd_list', '', @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'bugmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'defaultmgr_cmd_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);

#another interesting hack
#INSERT INTO role_permission (role_permission_id, role_id, permission_id)
#SELECT MAX(role_permission_id) +1 AS role_permission,
#	0 AS role_id,
#	p.permission_id AS permission_id
#FROM permission p, role_permission rp
#WHERE name = 'bugmgr'
#GROUP BY role_id;

#SELECT rp.role_permission_id, p.name
#from permission p, role_permission rp
#where p.permission_id = rp.permission_id
#and rp.role_id = 0;
