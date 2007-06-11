INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'messaging', 'Messaging', 'The ''Messaging'' module contains classes for sending internal Instant Messages, managing external email sending, and managing your contacts.', NULL, '48/module_messaging.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_read', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_compose', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_reply', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_outbox', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_inbox', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_sendAlert', '', @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_cmd_delete';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_cmd_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_cmd_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_compose';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_delete';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_inbox';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_outbox';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_read';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_reply';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
