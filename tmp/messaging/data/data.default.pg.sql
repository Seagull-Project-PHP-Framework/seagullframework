-- Last edited: Antonio J. Garcia 2007-04-21
-- Data dump for /messaging
-- leave subqueries on a single line in order that table prefixes works

BEGIN;

INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'messaging', 'Messaging', 'The ''Messaging'' module contains classes for sending internal Instant Messages, managing external email sending, and managing your contacts.', NULL, '48/module_messaging.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_cmd_insert', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_cmd_delete', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactmgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_read', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_delete', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_compose', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_reply', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_insert', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_outbox', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_inbox', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'instantmessagemgr_cmd_sendAlert', '', (
    SELECT max(module_id) FROM module
    ));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'contactmgr_cmd_delete'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'contactmgr_cmd_insert'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'contactmgr_cmd_list'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_compose'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_delete'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_inbox'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_insert'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_outbox'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_read'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'instantmessagemgr_cmd_reply'
    ));

COMMIT;
