-- Last edited: Antonio J. Garcia 2007-04-24
-- Data for /etc
-- leave subqueries on a single line in order that table prefixes works

BEGIN;

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'default', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', 'default/maintenance', '48/module_default.png', 'Demian Turner', NULL, 'BSD', 'stable');

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_add', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_insert', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_delete', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_overview', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_edit', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_update', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_detect', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_install', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_uninstall', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_reinstall', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulemgr_cmd_deregister', '', (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr_cmd_edit', 'Permission to view and edit config settings', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'configmgr_cmd_update', 'Permission to update config values', (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'defaultmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'defaultmgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'bugmgr_cmd_send', '', (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_edit', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_update', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_append', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_dbgen', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_clearCache', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_verify', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_checkAllModules', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_rebuildSequences', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_createModule', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_checkLatestVersion', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_rebuildSeagull', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'maintenancemgr_cmd_deleteConfigs', '', (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pearmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pearmgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pearmgr_cmd_doRequest', '', (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'moduleconfigmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'moduleconfigmgr_cmd_edit', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'moduleconfigmgr_cmd_update', '', (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulegenerationmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulegenerationmgr_cmd_createModule', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'modulegenerationmgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'bugmgr'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'defaultmgr_cmd_list'
    ));

-- another interesting hack
-- INSERT INTO role_permission (role_permission_id, role_id, permission_id)
-- SELECT MAX(role_permission_id) +1 AS role_permission,
-- 	0 AS role_id,
-- 	p.permission_id AS permission_id
-- FROM permission p, role_permission rp
-- WHERE name = 'bugmgr'
-- GROUP BY role_id;

-- SELECT rp.role_permission_id, p.name
-- from permission p, role_permission rp
-- where p.permission_id = rp.permission_id
-- and rp.role_id = 0;

COMMIT;
