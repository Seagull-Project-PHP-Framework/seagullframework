INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'guestbook', 'Guestbook', 'Use the ''Guestbook'' to allow users to leave comments about your site.', 'guestbook/guestbook', 'core.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_insert', '', @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'guestbookmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);