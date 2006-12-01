-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Data dump for /guestbook

BEGIN;


INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'guestbook', 'Guestbook', 'Use the ''Guestbook'' to allow users to leave comments about your site.', 'guestbook/guestbook', 'core.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_list', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_add', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_insert', '', (SELECT max(module_id) FROM module));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'guestbookmgr'));


COMMIT;

