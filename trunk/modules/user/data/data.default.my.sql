INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'user', 'Users and Security', 'The ''Users and Security'' module allows you to manage all your users, administer the roles they belong to, change their passwords, setup permissions and alter the global default preferences.', 'user/user', 'users.png');

SELECT @moduleId := MAX(module_id) FROM module;

#
# Dumping data for table `permission`
#

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_duplicate', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_viewProfile', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr_summary', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_login', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_retrieve', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_forgot', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'profilemgr_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_editPerms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr_updatePerms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_requestPasswordReset', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_resetPassword', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_editPerms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_updatePerms', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr_editAll', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr_updateAll', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'accountmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'loginmgr_logout', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr_editAll', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgpreferencemgr_updateAll', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_scanNew', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_insertNew', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_scanOrphaned', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'permissionmgr_deleteOrphaned', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'preferencemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'profilemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'registermgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rolemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_insertImportedUsers', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_syncToRole', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userpreferencemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'passwordmgr_redirectToEdit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_add', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_insert', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_edit', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_update', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_delete', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'orgtypemgr_list', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'userimportmgr_redirectToUserMgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_requestChangeUserStatus', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_changeUserStatus', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_viewLogin', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usermgr_truncateLoginTbl', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr_add', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'usersearchmgr_search', NULL, @moduleId);



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

# role perms for 'guest' roles
SELECT @permissionId := permission_id FROM permission WHERE name = 'loginmgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'loginmgr_login';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'passwordmgr_forgot';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'passwordmgr_retrieve';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'profilemgr_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'registermgr_add';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'registermgr_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'usermgr_requestPasswordReset';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);




INSERT INTO role_permission VALUES (14, 2, 11);
INSERT INTO role_permission VALUES (15, 2, 10);
INSERT INTO role_permission VALUES (16, 2, 7);
INSERT INTO role_permission VALUES (17, 2, 6);
INSERT INTO role_permission VALUES (18, 2, 59);
INSERT INTO role_permission VALUES (19, 2, 58);
INSERT INTO role_permission VALUES (20, 2, 23);
INSERT INTO role_permission VALUES (21, 2, 122);
INSERT INTO role_permission VALUES (22, 2, 123);
INSERT INTO role_permission VALUES (23, 2, 98);
INSERT INTO role_permission VALUES (24, 2, 97);
INSERT INTO role_permission VALUES (25, 2, 93);
INSERT INTO role_permission VALUES (26, 2, 96);
INSERT INTO role_permission VALUES (27, 2, 94);
INSERT INTO role_permission VALUES (28, 2, 95);
INSERT INTO role_permission VALUES (29, 2, 37);
INSERT INTO role_permission VALUES (30, 2, 36);
INSERT INTO role_permission VALUES (31, 2, 38);
INSERT INTO role_permission VALUES (32, 2, 84);
INSERT INTO role_permission VALUES (33, 2, 85);
INSERT INTO role_permission VALUES (34, 2, 86);
INSERT INTO role_permission VALUES (35, 2, 239);
INSERT INTO role_permission VALUES (36, 2, 41);
INSERT INTO role_permission VALUES (37, 2, 40);
INSERT INTO role_permission VALUES (38, 2, 46);
INSERT INTO role_permission VALUES (39, 2, 43);
INSERT INTO role_permission VALUES (40, 2, 39);
INSERT INTO role_permission VALUES (41, 2, 42);
INSERT INTO role_permission VALUES (42, 2, 45);
INSERT INTO role_permission VALUES (43, 2, 117);
INSERT INTO role_permission VALUES (44, 2, 118);
INSERT INTO role_permission VALUES (45, 2, 121);
INSERT INTO role_permission VALUES (46, 2, 105);
INSERT INTO role_permission VALUES (47, 2, 106);
INSERT INTO role_permission VALUES (48, 2, 142);
INSERT INTO role_permission VALUES (49, 2, 143);
INSERT INTO role_permission VALUES (50, 2, 67);
INSERT INTO role_permission VALUES (51, 2, 60);
INSERT INTO role_permission VALUES (52, 2, 62);
INSERT INTO role_permission VALUES (53, 2, 61);
INSERT INTO role_permission VALUES (54, 2, 63);
INSERT INTO role_permission VALUES (55, 2, 66);
INSERT INTO role_permission VALUES (59, 2, 147);
INSERT INTO role_permission VALUES (63, 2, 149);
INSERT INTO role_permission VALUES (73, 2, 224);
INSERT INTO role_permission VALUES (74, 2, 150);
INSERT INTO role_permission VALUES (75, 2, 225);
INSERT INTO role_permission VALUES (76, 2, 263);

#
# Dumping data for table `user_preference`
#


# sets default prefs
INSERT INTO user_preference VALUES (1,0,1,'1800');
INSERT INTO user_preference VALUES (2,0,2,'UTC');
INSERT INTO user_preference VALUES (3,0,3,'default');
INSERT INTO user_preference VALUES (4,0,4,'UK');
INSERT INTO user_preference VALUES (5,0,5,'en-iso-8859-15');
INSERT INTO user_preference VALUES (6,0,6,'10');
INSERT INTO user_preference VALUES (7,0,7,'1');
INSERT INTO user_preference VALUES (8,0,8,'en_GB');

#
# Dumping data for table `organisation`
#

INSERT INTO organisation VALUES (1,0,1,1,2,1,1,2,0,'default org','test','aasdfasdf','','','asdfadf','AL','BJ','55555','325 652 5645','http://example.com','test@test.com','2004-01-12 16:13:21',NULL,'2004-06-23 10:44:52',1);
INSERT INTO organisation VALUES (2,0,2,1,2,2,1,2,0,'sainsburys','test','aasdfasdf','','','asdfadf','AL','BJ','asdfasdf','325 652 5645','http://sainsburys.com','info@sainsburys.com','2004-01-12 16:13:21',NULL,'2004-06-23 10:44:56',1);