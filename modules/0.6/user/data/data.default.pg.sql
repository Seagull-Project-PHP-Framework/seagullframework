-- Last edited: Antonio J. Garcia 2007-04-24
-- Constraints for /user
-- leave subqueries on a single line in order that table prefixes works

-- Data dump for /modules/user

-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'user', 'Users and Security', 'The ''Users and Security'' module allows you to manage all your users, administer the roles they belong to, change their passwords, setup permissions and alter the global default preferences.', 'user/user', '48/module_user.png', 'Demian Turner', NULL, 'BSD', 'stable');

-- this is from MySQL version, PgSQL can do without it using subquery in next statement ;-)
-- SELECT @moduleId := MAX(module_id) FROM module;

--
--  Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_cmd_edit', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_cmd_update', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_cmd_viewProfile', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_cmd_summary', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_cmd_login', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_cmd_list', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_cmd_removeCookies', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_add', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_insert', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_edit', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_update', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_delete', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_list', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_cmd_retrieve', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_cmd_forgot', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpasswordmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpasswordmgr_cmd_edit', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpasswordmgr_cmd_update', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_add', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_insert', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_edit', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_update', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_delete', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_list', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_updatePreferencesForAllMembers', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_add', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_insert', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_edit', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_update', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_delete', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_list', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_scanNew', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_insertNew', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_scanOrphaned', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_deleteOrphaned', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'profilemgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'profilemgr_cmd_view', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr_cmd_add', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr_cmd_insert', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_add', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_insert', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_edit', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_update', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_delete', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_list', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_editPerms', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_updatePerms', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_duplicate', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_syncToRole', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_add', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_insert', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_edit', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_update', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_delete', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_list', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_requestPasswordReset', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_resetPassword', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_editPerms', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_updatePerms', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_removeExpiredCookies', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_requestChangeUserStatus', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_changeUserStatus', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_viewLogin', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_truncateLoginTbl', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr_cmd_editAll', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr_cmd_updateAll', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_cmd_logout', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr_cmd_editAll', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr_cmd_updateAll', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_cmd_list', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_cmd_insertImportedUsers', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_add', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_insert', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_edit', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_update', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_delete', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_list', NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr_cmd_add', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr_cmd_search', NULL, (
    SELECT max(module_id) FROM module
    ));

--
--  Dumping data for table preference
--

INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'sessionTimeout', '1800');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'timezone', 'UTC');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'theme', 'default');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'dateFormat', 'UK');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'language', 'en-iso-8859-15');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'resPerPage', '10');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'showExecutionTimes', '1');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'locale', 'en_GB');

--
--  Dumping data for table role
--


INSERT INTO role VALUES (-1,'unassigned','not assigned a role',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (0,'guest','public user',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (1,'root','super user',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (2,'member','has a limited set of privileges',NULL,NULL,NULL,NULL);

--
--  Dumping data for table role_permission
--

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'accountmgr'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'accountmgr_cmd_edit'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'accountmgr_cmd_summary'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'accountmgr_cmd_update'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'accountmgr_cmd_viewProfile'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'loginmgr'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'loginmgr_cmd_list'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'loginmgr_cmd_login'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'loginmgr_cmd_logout'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'userpasswordmgr_cmd_edit'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'userpasswordmgr_cmd_update'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'preferencemgr_cmd_edit'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'preferencemgr_cmd_update'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'profilemgr_cmd_view'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'registermgr_cmd_add'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'registermgr_cmd_insert'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'userpreferencemgr_cmd_editAll'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'userpreferencemgr_cmd_updateAll'
    ));

--  add 'nobody' user
INSERT INTO usr VALUES (0, 1, 0, 'nobody', '21232f297a57a5a743894a0e4a801fc3', 'Nobody', 'Nobody', '', '', 'none@none.com', 'none', '', '', 'None', '', 'GB', '55555', 0, 0, 1, 'rover', '2003-12-09 18:02:44', 1, '2004-06-10 11:07:27', 1);


--
--  Dumping data for table organisation
--

-- INSERT INTO organisation VALUES (1,0,1,1,2,1,1,2,0,'default org','test','aasdfasdf','','','asdfadf','AL','BJ','55555','325 652 5645','http://example.com','test@test.com','2004-01-12 16:13:21',NULL,'2004-06-23 10:44:52',1);
-- INSERT INTO organisation VALUES (2,0,2,1,2,2,1,2,0,'sainsburys','test','aasdfasdf','','','asdfadf','AL','BJ','asdfasdf','325 652 5645','http://sainsburys.com','info@sainsburys.com','2004-01-12 16:13:21',NULL,'2004-06-23 10:44:56',1);

COMMIT;
