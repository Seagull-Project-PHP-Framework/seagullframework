INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'messaging', 'Messaging', 'The ''Messaging'' module contains classes for sending internal Instant Messages, managing external email sending, and managing your contacts.', NULL, '48/module_messaging.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'luusersmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'luusersmgr_cmd_editgroups', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'luusersmgr_cmd_updategroups', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_editrights', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_updaterights', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_editmemebers', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lugroupsmgr_cmd_updatemembers', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_editperms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'lurightsmgr_cmd_updateperms', '', @moduleId);

-- Application
INSERT INTO `liveuser_applications` VALUES ({SGL_NEXT_ID}, 'SEAGULL');

-- Areas
INSERT INTO `liveuser_areas` VALUES ({SGL_NEXT_ID}, 1, 'BACKEND');



