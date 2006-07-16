BEGIN;

INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'liveuser', 'LiveUser', 'The ''LiveUser'' module allows users to belong to multiple groups and the creations of rights.', NULL, '48/module_messaging.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'luusersmgr', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'luusersmgr_cmd_editgroups', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'luusersmgr_cmd_updategroups', '', (SELECT max(module_id) FROM module));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_add', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_insert', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_edit', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_update', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_delete', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_list', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_editrights', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_updaterights', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_editmemebers', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_updatemembers', '', (SELECT max(module_id) FROM module));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_add', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_insert', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_edit', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_update', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_delete', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_list', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_editperms', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_updateperms', '', (SELECT max(module_id) FROM module));

-- Application
INSERT INTO liveuser_applications VALUES ({SGL_NEXT_ID}, 'SEAGULL');

-- Areas
INSERT INTO liveuser_areas VALUES ({SGL_NEXT_ID}, 1, 'BACKEND');

COMMIT;