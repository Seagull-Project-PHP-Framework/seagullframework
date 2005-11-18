INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'messaging', 'Messaging', 'The ''Messaging'' module contains classes for sending internal Instant Messages, managing external email sending, and managing your contacts.', NULL, 'messaging.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_read', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_compose', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_reply', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_outbox', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_inbox', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_sendAlert', '', @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_delete';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'imessagemgr_compose';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'imessagemgr_delete';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'imessagemgr_inbox';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'imessagemgr_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'imessagemgr_outbox';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'imessagemgr_read';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'imessagemgr_reply';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);


#
# Dumping data for table `instant_message`
#
INSERT INTO instant_message VALUES (1,1,2,'2003-08-14 00:14:41','Welcome to Seagull','This is an example message.  Once you add users to the system, they can contact eachother via this instant message interface.',3,3);