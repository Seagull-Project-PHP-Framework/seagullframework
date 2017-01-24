INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'user', 'Users and Security', 'The ''Users and Security'' module allows you to manage all your users, administer the roles they belong to, change their passwords, setup permissions and alter the global default preferences.', 'user/user', '48/module_user.png', 'Demian Turner', NULL, 'BSD', 'stable');

SELECT @moduleId := MAX(module_id) FROM module;

#
# Dumping data for table `permission`
#

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_cmd_viewProfile', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_cmd_summary', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_cmd_login', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_cmd_removeCookies', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_cmd_list', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_cmd_retrieve', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_cmd_forgot', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpasswordmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpasswordmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpasswordmgr_cmd_update', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_cmd_updatePreferencesForAllMembers', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_scanNew', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_insertNew', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_scanOrphaned', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_cmd_deleteOrphaned', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'profilemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'profilemgr_cmd_view', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr_cmd_insert', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_editPerms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_updatePerms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_cmd_duplicate', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_syncToRole', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_requestPasswordReset', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_resetPassword', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_editPerms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_updatePerms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_removeExpiredCookies', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_requestChangeUserStatus', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_changeUserStatus', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_viewLogin', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_cmd_truncateLoginTbl', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr_cmd_editAll', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr_cmd_updateAll', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_cmd_logout', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr_cmd_editAll', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr_cmd_updateAll', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_cmd_insertImportedUsers', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_cmd_list', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr_cmd_search', '', @moduleId);

#
# Dumping data for table `preference`
#

INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'sessionTimeout', '1800');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'timezone', 'UTC');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'theme', 'default');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'dateFormat', 'UK');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'language', 'en-iso-8859-15');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'resPerPage', '10');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'showExecutionTimes', '1');
INSERT INTO preference VALUES ({SGL_NEXT_ID}, 'locale', 'en_GB');

#
# Dumping data for table `role`
#


INSERT INTO role VALUES (-1,'unassigned','not assigned a role',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (0,'guest','public user',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (1,'root','super user',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (2,'member','has a limited set of privileges',NULL,NULL,NULL,NULL);

#
# Dumping data for table `role_permission`
#

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'accountmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'accountmgr_cmd_edit';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'accountmgr_cmd_summary';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'accountmgr_cmd_update';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'accountmgr_cmd_viewProfile';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'loginmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'loginmgr_cmd_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'loginmgr_cmd_login';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'loginmgr_cmd_logout';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'userpasswordmgr_cmd_edit';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'userpasswordmgr_cmd_update';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'preferencemgr_cmd_edit';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'preferencemgr_cmd_update';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'profilemgr_cmd_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'registermgr_cmd_add';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'registermgr_cmd_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'userpreferencemgr_cmd_editAll';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'userpreferencemgr_cmd_updateAll';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);

# add 'nobody' user
INSERT INTO usr VALUES (0, 1, 0, 'nobody', '21232f297a57a5a743894a0e4a801fc3', 'Nobody', 'Nobody', '', '', 'none@none.com', 'none', '', '', 'None', '', 'GB', '55555', 0, 0, 1, 'rover', '2003-12-09 18:02:44', 1, '2004-06-10 11:07:27', 1);


#
# Dumping data for table `organisation`
#

#INSERT INTO organisation VALUES (1,0,1,1,2,1,1,2,0,'default org','test','aasdfasdf','','','asdfadf','AL','BJ','55555','325 652 5645','http://example.com','test@test.com','2004-01-12 16:13:21',NULL,'2004-06-23 10:44:52',1);
#INSERT INTO organisation VALUES (2,0,2,1,2,2,1,2,0,'sainsburys','test','aasdfasdf','','','asdfadf','AL','BJ','asdfasdf','325 652 5645','http://sainsburys.com','info@sainsburys.com','2004-01-12 16:13:21',NULL,'2004-06-23 10:44:56',1);
