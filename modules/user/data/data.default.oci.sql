-- 
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'user', 'Users and Security', 'The ''Users and Security'' module allows you to manage all your users, administer the roles they belong to, change their passwords, setup permissions and alter the global default preferences.', 'user/user', 'users.png');

--
-- Permission table
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_duplicate', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_viewProfile', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_summary', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_login', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_retrieve', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_forgot', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'profilemgr_view', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_editPerms', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_updatePerms', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_requestPasswordReset', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_resetPassword', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_editPerms', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_updatePerms', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr_editAll', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr_updateAll', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_logout', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr_editAll', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr_updateAll', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_scanNew', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_insertNew', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_scanOrphaned', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_deleteOrphaned', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'profilemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_insertImportedUsers', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_syncToRole', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_redirectToEdit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_add', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_insert', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_edit', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_update', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_delete', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_list', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_redirectToUserMgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_requestChangeUserStatus', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_changeUserStatus', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_viewLogin', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_truncateLoginTbl', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr_add', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr_search', NULL, (SELECT MAX(module_id) FROM module));

--
-- Dumping data for table preference
--

INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'sessionTimeout', '1800');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'timezone', 'Europe/London');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'theme', 'default');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'dateFormat', 'UK');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'language', 'de-iso-8859-1');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'resPerPage', '10');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'showExecutionTimes', '0');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'locale', 'en_GB');

--
-- Role table
--

INSERT INTO role VALUES (-1,'unassigned','not assigned a role',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (0,'guest','public user',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (1,'root','super user',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (2,'member','has a limited set of privileges',NULL,NULL,NULL,NULL);

--
-- Dumping data for table role_permission
--

-- guest role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'loginmgr_list'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'loginmgr_login'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'passwordmgr_forgot'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'passwordmgr_retrieve'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'profilemgr_view'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'registermgr_add'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'registermgr_insert'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'usermgr_requestPasswordReset'));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'accountmgr'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'accountmgr_edit'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'accountmgr_summary'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'accountmgr_update'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'accountmgr_viewProfile'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'loginmgr'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'loginmgr_list'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'loginmgr_login'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'loginmgr_logout'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'passwordmgr_edit'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'passwordmgr_redirectToEdit'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'passwordmgr_update'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'preferencemgr_edit'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'preferencemgr_update'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'profilemgr_view'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'registermgr_add'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'registermgr_insert'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'userpreferencemgr_editAll'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'userpreferencemgr_updateAll'));

--
-- Dumping data for table user_preference
--

-- sets default prefs
INSERT INTO user_preference VALUES (1,0,1,'1800');
INSERT INTO user_preference VALUES (2,0,2,'Europe/Berlin');
INSERT INTO user_preference VALUES (3,0,3,'default');
INSERT INTO user_preference VALUES (4,0,4,'UK');
INSERT INTO user_preference VALUES (5,0,5,'de-iso-8859-1');
INSERT INTO user_preference VALUES (6,0,6,'10');
INSERT INTO user_preference VALUES (7,0,7,'0');
INSERT INTO user_preference VALUES (8,0,8,'en_GB');

-- 
-- Dumping data for table organisation
--

INSERT INTO organisation  VALUES (1, 0, 1, 1, 2, 1, 1, 2, 0, 'default org', 'test', 'aasdfasdf', '', '', 'asdfadf', 'AL', 'BJ', '55555', '325 652 5645', 'http:--example.com', 'test@test.com', '2004-01-12 16:13:21', NULL, '2004-06-23 10:44:52', 1);
INSERT INTO organisation  VALUES (2, 0, 2, 1, 2, 2, 1, 2, 0, 'sainsburys', 'test', 'aasdfasdf', '', '', 'asdfadf', 'AL', 'BJ', 'asdfasdf', '325 652 5645', 'http:--sainsburys.com', 'info@sainsburys.com', '2004-01-12 16:13:21', NULL, '2004-06-23 10:44:56', 1);

--
-- Creating sequences
-- sequence must start on the first free record id
--

CREATE SEQUENCE organisation_seq START WITH 3;
CREATE SEQUENCE role_seq START WITH 3;
CREATE SEQUENCE user_preference_seq START WITH 9;