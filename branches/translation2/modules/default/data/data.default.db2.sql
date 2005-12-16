--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'default', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', 'default/maintenance', 'default.png');

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_overview', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_update', '', (SELECT MAX(module_id) FROM module));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr_edit', 'Permission to view and edit config settings', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr_update', 'Permission to update config values', (SELECT MAX(module_id) FROM module));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'defaultmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'defaultmgr_list', '', (SELECT MAX(module_id) FROM module));
--INSERT INTO permission VALUES (11, 'defaultmgr_showNews', '', (SELECT MAX(module_id) FROM module));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr_list', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr_send', NULL, (SELECT MAX(module_id) FROM module));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_append', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_dbgen', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_clearCache', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_verify', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_checkAllModules', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_rebuildSequences', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_createModule', '', (SELECT MAX(module_id) FROM module));

-- guest role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'bugmgr'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'defaultmgr_list'));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'bugmgr'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'defaultmgr_list'));
