--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'messaging', 'Messaging', 'The ''Messaging'' module contains classes for sending internal Instant Messages, managing external email sending, and managing your contacts.', NULL, 'messaging.png');

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_read', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_compose', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_reply', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_outbox', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_inbox', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'imessagemgr_sendAlert', '', (SELECT MAX(module_id) FROM module));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'imessagemgr_compose'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'imessagemgr_delete'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'imessagemgr_inbox'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'imessagemgr_insert'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'imessagemgr_outbox'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'imessagemgr_read'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'imessagemgr_reply'));

--
-- Dumping data for table instant_message
--

INSERT INTO instant_message VALUES (1, 1, 2, '2003-08-14 00:14:41', 'Welcome to Seagull', 'This is an example message.  Once you add users to the system, they can contact eachother via this instant message interface.', 3, 3);

--
-- Creating sequences
-- sequence must start on the first free record id
--

CREATE SEQUENCE instant_message_seq START WITH 2;
