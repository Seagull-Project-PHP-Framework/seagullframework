-- Last edited: Antonio J. Garcia 2007-04-24
-- Data dump for /contactus
-- leave subqueries on a single line in order that table prefixes works

BEGIN;


INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'contactus', 'Contact Us', 'The ''Contact Us'' module can be used to present a form to your users allowing them to contact the site administrators.', NULL, '48/module_contactus.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr_cmd_send', 'Permission to submit contact info', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr_cmd_list', 'Permission to view Contact Us screen', (
    SELECT max(module_id) FROM module
    ));

-- member roles perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'contactusmgr_cmd_list'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'contactusmgr_cmd_send'
    ));


COMMIT;

