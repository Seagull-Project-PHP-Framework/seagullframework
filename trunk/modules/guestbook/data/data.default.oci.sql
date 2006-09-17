--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'guestbook', 'Guestbook', 'Use the ''Guestbook'' to allow users to leave comments about your site.', 'guestbook/guestbook', 'core.png', '', NULL, NULL, NULL);

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'guestbookmgr_cmd_insert', '', (SELECT MAX(module_id) FROM module));

-- guest role perms
-- SELECT @permissionId := permission_id FROM permission WHERE name = 'guestbookmgr';
-- INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'guestbookmgr'));

-- member role perms
--SELECT @permissionId := permission_id FROM permission WHERE name = 'guestbookmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'guestbookmgr'));
