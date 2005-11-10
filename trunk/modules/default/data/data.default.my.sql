INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'default', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', 'faq/maintenance', 'default.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_overview', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_update', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr_edit', 'Permission to view and edit config settings', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr_update', 'Permission to update config values', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'defaultmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'defaultmgr_list', '', @moduleId);
#INSERT INTO permission VALUES (11, 'defaultmgr_showNews', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr_list', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr_send', NULL, @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_append', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_dbgen', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_clearCache', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_verify', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_checkAllModules', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_rebuildSequences', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_createModule', '', @moduleId);

#guest role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'bugmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'defaultmgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'bugmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'defaultmgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);