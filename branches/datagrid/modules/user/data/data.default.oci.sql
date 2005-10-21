-- 
-- Dumping data for table organisation
--

INSERT INTO organisation  VALUES (1, 0, 1, 1, 2, 1, 1, 2, 0, 'default org', 'test', 'aasdfasdf', '', '', 'asdfadf', 'AL', 'BJ', '55555', '325 652 5645', 'http:--example.com', 'test@test.com', '2004-01-12 16:13:21', NULL, '2004-06-23 10:44:52', 1);
INSERT INTO organisation  VALUES (2, 0, 2, 1, 2, 2, 1, 2, 0, 'sainsburys', 'test', 'aasdfasdf', '', '', 'asdfadf', 'AL', 'BJ', 'asdfasdf', '325 652 5645', 'http:--sainsburys.com', 'info@sainsburys.com', '2004-01-12 16:13:21', NULL, '2004-06-23 10:44:56', 1);

-- 
-- Dumping data for table organisation_type
--

INSERT INTO organisation_type VALUES (0,'Default');

--
-- Permission table
--

INSERT INTO permission VALUES (1, 'blockmgr_add', 'Permission to add new block', 1);
INSERT INTO permission VALUES (2, 'blockmgr_edit', 'Permission to edit existing block', 1);
INSERT INTO permission VALUES (3, 'blockmgr_delete', 'Permission to remove block', 1);
INSERT INTO permission VALUES (4, 'blockmgr_reorder', 'Permission to reorder blocks', 1);
INSERT INTO permission VALUES (5, 'blockmgr_list', 'Permission to view block listing', 1);
INSERT INTO permission VALUES (6, 'contactusmgr_send', 'Permission to submit contact info', 2);
INSERT INTO permission VALUES (7, 'contactusmgr_list', 'Permission to view Contact Us screen', 2);
INSERT INTO permission VALUES (8, 'configmgr_edit', 'Permission to view and edit config settings', 3);
INSERT INTO permission VALUES (9, 'configmgr_update', 'Permission to update config values', 3);
INSERT INTO permission VALUES (10, 'defaultmgr_list', NULL, 3);
INSERT INTO permission VALUES (11, 'defaultmgr_showNews', NULL, 3);
INSERT INTO permission VALUES (12, 'modulemgr_add', NULL, 3);
INSERT INTO permission VALUES (13, 'modulemgr_insert', NULL, 3);
INSERT INTO permission VALUES (14, 'modulemgr_delete', NULL, 3);
INSERT INTO permission VALUES (15, 'modulemgr_list', NULL, 3);
INSERT INTO permission VALUES (16, 'modulemgr_overview', NULL, 3);
INSERT INTO permission VALUES (17, 'documentormgr_list', NULL, 4);
INSERT INTO permission VALUES (18, 'faqmgr_add', NULL, 5);
INSERT INTO permission VALUES (19, 'faqmgr_insert', NULL, 5);
INSERT INTO permission VALUES (20, 'faqmgr_edit', NULL, 5);
INSERT INTO permission VALUES (21, 'faqmgr_update', NULL, 5);
INSERT INTO permission VALUES (22, 'faqmgr_delete', NULL, 5);
INSERT INTO permission VALUES (23, 'faqmgr_list', NULL, 5);
INSERT INTO permission VALUES (24, 'faqmgr_reorder', NULL, 5);
INSERT INTO permission VALUES (25, 'faqmgr_reorderUpdate', NULL, 5);
INSERT INTO permission VALUES (26, 'guestbookmgr_list', NULL, 6);
INSERT INTO permission VALUES (27, 'guestbookmgr_add', NULL, 6);
INSERT INTO permission VALUES (28, 'guestbookmgr_insert', NULL, 6);
INSERT INTO permission VALUES (30, 'maintenancemgr_edit', NULL, 7);
INSERT INTO permission VALUES (31, 'maintenancemgr_update', NULL, 7);
INSERT INTO permission VALUES (32, 'maintenancemgr_append', NULL, 7);
INSERT INTO permission VALUES (33, 'maintenancemgr_dbgen', NULL, 7);
INSERT INTO permission VALUES (34, 'maintenancemgr_clearCache', NULL, 7);
INSERT INTO permission VALUES (35, 'maintenancemgr_list', NULL, 7);
INSERT INTO permission VALUES (36, 'contactmgr_insert', NULL, 8);
INSERT INTO permission VALUES (37, 'contactmgr_delete', NULL, 8);
INSERT INTO permission VALUES (38, 'contactmgr_list', NULL, 8);
INSERT INTO permission VALUES (39, 'imessagemgr_read', NULL, 8);
INSERT INTO permission VALUES (40, 'imessagemgr_delete', NULL, 8);
INSERT INTO permission VALUES (41, 'imessagemgr_compose', NULL, 8);
INSERT INTO permission VALUES (42, 'imessagemgr_reply', NULL, 8);
INSERT INTO permission VALUES (43, 'imessagemgr_insert', NULL, 8);
INSERT INTO permission VALUES (45, 'imessagemgr_outbox', NULL, 8);
INSERT INTO permission VALUES (46, 'imessagemgr_inbox', NULL, 8);
INSERT INTO permission VALUES (47, 'navstylemgr_changeStyle', NULL, 9);
INSERT INTO permission VALUES (48, 'navstylemgr_list', NULL, 9);
INSERT INTO permission VALUES (49, 'pagemgr_add', NULL, 9);
INSERT INTO permission VALUES (50, 'pagemgr_insert', NULL, 9);
INSERT INTO permission VALUES (51, 'pagemgr_edit', NULL, 9);
INSERT INTO permission VALUES (52, 'pagemgr_update', NULL, 9);
INSERT INTO permission VALUES (53, 'pagemgr_delete', NULL, 9);
INSERT INTO permission VALUES (54, 'pagemgr_reorder', NULL, 9);
INSERT INTO permission VALUES (55, 'pagemgr_list', NULL, 9);
INSERT INTO permission VALUES (58, 'articleviewmgr_view', NULL, 11);
INSERT INTO permission VALUES (59, 'articleviewmgr_summary', NULL, 11);
INSERT INTO permission VALUES (60, 'articlemgr_add', NULL, 11);
INSERT INTO permission VALUES (61, 'articlemgr_insert', NULL, 11);
INSERT INTO permission VALUES (62, 'articlemgr_edit', NULL, 11);
INSERT INTO permission VALUES (63, 'articlemgr_update', NULL, 11);
INSERT INTO permission VALUES (64, 'articlemgr_changeStatus', NULL, 11);
INSERT INTO permission VALUES (65, 'articlemgr_delete', NULL, 11);
INSERT INTO permission VALUES (66, 'articlemgr_view', NULL, 11);
INSERT INTO permission VALUES (67, 'articlemgr_list', NULL, 11);
INSERT INTO permission VALUES (69, 'categorymgr_insert', NULL, 9);
INSERT INTO permission VALUES (71, 'categorymgr_update', NULL, 9);
INSERT INTO permission VALUES (73, 'rolemgr_duplicate', NULL, 12);
INSERT INTO permission VALUES (75, 'categorymgr_list', NULL, 9);
INSERT INTO permission VALUES (76, 'documentmgr_add', NULL, 11);
INSERT INTO permission VALUES (77, 'documentmgr_insert', NULL, 11);
INSERT INTO permission VALUES (78, 'documentmgr_edit', NULL, 11);
INSERT INTO permission VALUES (79, 'documentmgr_update', NULL, 11);
INSERT INTO permission VALUES (80, 'documentmgr_setDownload', NULL, 11);
INSERT INTO permission VALUES (81, 'documentmgr_view', NULL, 11);
INSERT INTO permission VALUES (82, 'documentmgr_delete', NULL, 11);
INSERT INTO permission VALUES (83, 'documentmgr_list', NULL, 11);
INSERT INTO permission VALUES (84, 'filemgr_download', NULL, 11);
INSERT INTO permission VALUES (85, 'filemgr_downloadZipped', NULL, 11);
INSERT INTO permission VALUES (86, 'filemgr_view', NULL, 11);
INSERT INTO permission VALUES (89, 'rndmsgmgr_add', NULL, 13);
INSERT INTO permission VALUES (90, 'rndmsgmgr_insert', NULL, 13);
INSERT INTO permission VALUES (91, 'rndmsgmgr_delete', NULL, 13);
INSERT INTO permission VALUES (92, 'rndmsgmgr_list', NULL, 13);
INSERT INTO permission VALUES (93, 'accountmgr_edit', NULL, 12);
INSERT INTO permission VALUES (94, 'accountmgr_update', NULL, 12);
INSERT INTO permission VALUES (95, 'accountmgr_viewProfile', NULL, 12);
INSERT INTO permission VALUES (96, 'accountmgr_summary', NULL, 12);
INSERT INTO permission VALUES (97, 'loginmgr_login', NULL, 12);
INSERT INTO permission VALUES (98, 'loginmgr_list', NULL, 12);
INSERT INTO permission VALUES (99, 'orgmgr_add', NULL, 12);
INSERT INTO permission VALUES (100, 'orgmgr_insert', NULL, 12);
INSERT INTO permission VALUES (101, 'orgmgr_edit', NULL, 12);
INSERT INTO permission VALUES (102, 'orgmgr_update', NULL, 12);
INSERT INTO permission VALUES (103, 'orgmgr_delete', NULL, 12);
INSERT INTO permission VALUES (104, 'orgmgr_list', NULL, 12);
INSERT INTO permission VALUES (105, 'passwordmgr_edit', NULL, 12);
INSERT INTO permission VALUES (106, 'passwordmgr_update', NULL, 12);
INSERT INTO permission VALUES (107, 'passwordmgr_retrieve', NULL, 12);
INSERT INTO permission VALUES (108, 'passwordmgr_forgot', NULL, 12);
INSERT INTO permission VALUES (109, 'permissionmgr_add', NULL, 12);
INSERT INTO permission VALUES (110, 'permissionmgr_insert', NULL, 12);
INSERT INTO permission VALUES (111, 'permissionmgr_edit', NULL, 12);
INSERT INTO permission VALUES (112, 'permissionmgr_update', NULL, 12);
INSERT INTO permission VALUES (113, 'permissionmgr_delete', NULL, 12);
INSERT INTO permission VALUES (114, 'permissionmgr_list', NULL, 12);
INSERT INTO permission VALUES (115, 'preferencemgr_add', NULL, 12);
INSERT INTO permission VALUES (116, 'preferencemgr_insert', NULL, 12);
INSERT INTO permission VALUES (117, 'preferencemgr_edit', NULL, 12);
INSERT INTO permission VALUES (118, 'preferencemgr_update', NULL, 12);
INSERT INTO permission VALUES (119, 'preferencemgr_delete', NULL, 12);
INSERT INTO permission VALUES (120, 'preferencemgr_list', NULL, 12);
INSERT INTO permission VALUES (121, 'profilemgr_view', NULL, 12);
INSERT INTO permission VALUES (122, 'registermgr_add', NULL, 12);
INSERT INTO permission VALUES (123, 'registermgr_insert', NULL, 12);
INSERT INTO permission VALUES (124, 'rolemgr_add', NULL, 12);
INSERT INTO permission VALUES (125, 'rolemgr_insert', NULL, 12);
INSERT INTO permission VALUES (126, 'rolemgr_edit', NULL, 12);
INSERT INTO permission VALUES (127, 'rolemgr_update', NULL, 12);
INSERT INTO permission VALUES (128, 'rolemgr_delete', NULL, 12);
INSERT INTO permission VALUES (129, 'rolemgr_list', NULL, 12);
INSERT INTO permission VALUES (130, 'rolemgr_editPerms', NULL, 12);
INSERT INTO permission VALUES (131, 'rolemgr_updatePerms', NULL, 12);
INSERT INTO permission VALUES (132, 'usermgr_add', NULL, 12);
INSERT INTO permission VALUES (133, 'usermgr_insert', NULL, 12);
INSERT INTO permission VALUES (134, 'usermgr_edit', NULL, 12);
INSERT INTO permission VALUES (135, 'usermgr_update', NULL, 12);
INSERT INTO permission VALUES (136, 'usermgr_delete', NULL, 12);
INSERT INTO permission VALUES (137, 'usermgr_list', NULL, 12);
INSERT INTO permission VALUES (138, 'usermgr_requestPasswordReset', NULL, 12);
INSERT INTO permission VALUES (139, 'usermgr_resetPassword', NULL, 12);
INSERT INTO permission VALUES (140, 'usermgr_editPerms', NULL, 12);
INSERT INTO permission VALUES (141, 'usermgr_updatePerms', NULL, 12);
INSERT INTO permission VALUES (142, 'userpreferencemgr_editAll', NULL, 12);
INSERT INTO permission VALUES (143, 'userpreferencemgr_updateAll', NULL, 12);
INSERT INTO permission VALUES (145, 'newslettermgr_list', NULL, 10);
INSERT INTO permission VALUES (147, 'accountmgr', NULL, 12);
INSERT INTO permission VALUES (149, 'loginmgr', NULL, 12);
INSERT INTO permission VALUES (150, 'loginmgr_logout', NULL, 12);
INSERT INTO permission VALUES (151, 'orgmgr', NULL, 12);
INSERT INTO permission VALUES (153, 'orgpreferencemgr', NULL, 12);
INSERT INTO permission VALUES (154, 'orgpreferencemgr_editAll', NULL, 12);
INSERT INTO permission VALUES (155, 'orgpreferencemgr_updateAll', NULL, 12);
INSERT INTO permission VALUES (156, 'passwordmgr', NULL, 12);
INSERT INTO permission VALUES (158, 'permissionmgr', NULL, 12);
INSERT INTO permission VALUES (160, 'permissionmgr_scanNew', NULL, 12);
INSERT INTO permission VALUES (161, 'permissionmgr_insertNew', NULL, 12);
INSERT INTO permission VALUES (162, 'permissionmgr_scanOrphaned', NULL, 12);
INSERT INTO permission VALUES (163, 'permissionmgr_deleteOrphaned', NULL, 12);
INSERT INTO permission VALUES (164, 'preferencemgr', NULL, 12);
INSERT INTO permission VALUES (166, 'profilemgr', NULL, 12);
INSERT INTO permission VALUES (167, 'registermgr', NULL, 12);
INSERT INTO permission VALUES (169, 'rolemgr', NULL, 12);
INSERT INTO permission VALUES (171, 'userimportmgr', NULL, 12);
INSERT INTO permission VALUES (172, 'userimportmgr_list', NULL, 12);
INSERT INTO permission VALUES (173, 'userimportmgr_insertImportedUsers', NULL, 12);
INSERT INTO permission VALUES (175, 'usermgr', NULL, 12);
INSERT INTO permission VALUES (178, 'usermgr_syncToRole', NULL, 12);
INSERT INTO permission VALUES (179, 'userpreferencemgr', NULL, 12);
INSERT INTO permission VALUES (181, 'rndmsgmgr', NULL, 13);
INSERT INTO permission VALUES (183, 'articlemgr', NULL, 11);
INSERT INTO permission VALUES (185, 'articleviewmgr', NULL, 11);
INSERT INTO permission VALUES (186, 'documentmgr', NULL, 11);
INSERT INTO permission VALUES (188, 'filemgr', NULL, 11);
INSERT INTO permission VALUES (189, 'newslettermgr', NULL, 10);
INSERT INTO permission VALUES (191, 'categorymgr', NULL, 9);
INSERT INTO permission VALUES (193, 'categorymgr_delete', NULL, 9);
INSERT INTO permission VALUES (194, 'navstylemgr', NULL, 9);
INSERT INTO permission VALUES (196, 'pagemgr', NULL, 9);
INSERT INTO permission VALUES (198, 'contactmgr', NULL, 8);
INSERT INTO permission VALUES (200, 'imessagemgr', NULL, 8);
INSERT INTO permission VALUES (202, 'imessagemgr_sendAlert', NULL, 8);
INSERT INTO permission VALUES (203, 'maintenancemgr', NULL, 7);
INSERT INTO permission VALUES (204, 'maintenancemgr_verify', NULL, 7);
INSERT INTO permission VALUES (206, 'maintenancemgr_checkAllModules', NULL, 7);
INSERT INTO permission VALUES (207, 'maintenancemgr_rebuildSequences', NULL, 7);
INSERT INTO permission VALUES (208, 'maintenancemgr_createModule', NULL, 7);
INSERT INTO permission VALUES (209, 'guestbookmgr', NULL, 6);
INSERT INTO permission VALUES (211, 'faqmgr', NULL, 5);
INSERT INTO permission VALUES (213, 'documentormgr', NULL, 4);
INSERT INTO permission VALUES (214, 'configmgr', NULL, 3);
INSERT INTO permission VALUES (216, 'defaultmgr', NULL, 3);
INSERT INTO permission VALUES (217, 'modulemgr', NULL, 3);
INSERT INTO permission VALUES (219, 'modulemgr_edit', NULL, 3);
INSERT INTO permission VALUES (220, 'modulemgr_update', NULL, 3);
INSERT INTO permission VALUES (221, 'contactusmgr', NULL, 2);
INSERT INTO permission VALUES (223, 'blockmgr', 'Permission to use block manager', 1);
INSERT INTO permission VALUES (224, 'bugmgr', NULL, 3);
INSERT INTO permission VALUES (225, 'rssmgr_news', NULL, 14);
INSERT INTO permission VALUES (239, 'passwordmgr_redirectToEdit', NULL, 12);
INSERT INTO permission VALUES (240, 'orgtypemgr', NULL, 12);
INSERT INTO permission VALUES (241, 'orgtypemgr_add', NULL, 12);
INSERT INTO permission VALUES (242, 'orgtypemgr_insert', NULL, 12);
INSERT INTO permission VALUES (243, 'orgtypemgr_edit', NULL, 12);
INSERT INTO permission VALUES (244, 'orgtypemgr_update', NULL, 12);
INSERT INTO permission VALUES (245, 'orgtypemgr_delete', NULL, 12);
INSERT INTO permission VALUES (246, 'orgtypemgr_list', NULL, 12);
INSERT INTO permission VALUES (247, 'userimportmgr_redirectToUserMgr', NULL, 12);
INSERT INTO permission VALUES (248, 'usermgr_requestChangeUserStatus', NULL, 12);
INSERT INTO permission VALUES (249, 'usermgr_changeUserStatus', NULL, 12);
INSERT INTO permission VALUES (250, 'usermgr_viewLogin', NULL, 12);
INSERT INTO permission VALUES (251, 'usermgr_truncateLoginTbl', NULL, 12);
INSERT INTO permission VALUES (252, 'usersearchmgr', NULL, 12);
INSERT INTO permission VALUES (253, 'usersearchmgr_add', NULL, 12);
INSERT INTO permission VALUES (254, 'usersearchmgr_search', NULL, 12);
INSERT INTO permission VALUES (255, 'contenttypemgr', NULL, 11);
INSERT INTO permission VALUES (256, 'contenttypemgr_add', NULL, 11);
INSERT INTO permission VALUES (257, 'contenttypemgr_insert', NULL, 11);
INSERT INTO permission VALUES (258, 'contenttypemgr_edit', NULL, 11);
INSERT INTO permission VALUES (259, 'contenttypemgr_update', NULL, 11);
INSERT INTO permission VALUES (260, 'contenttypemgr_delete', NULL, 11);
INSERT INTO permission VALUES (261, 'contenttypemgr_list', NULL, 11);
INSERT INTO permission VALUES (262, 'wikiscrapemgr', NULL, 11);
INSERT INTO permission VALUES (263, 'wikiscrapemgr_list', NULL, 11);
INSERT INTO permission VALUES (264, 'listmgr', NULL, 10);
INSERT INTO permission VALUES (265, 'listmgr_list', NULL, 10);
INSERT INTO permission VALUES (266, 'listmgr_send', NULL, 10);
INSERT INTO permission VALUES (267, 'listmgr_addressBook', NULL, 10);
INSERT INTO permission VALUES (268, 'listmgr_listSubscribers', NULL, 10);
INSERT INTO permission VALUES (269, 'listmgr_editSubscriber', NULL, 10);
INSERT INTO permission VALUES (270, 'listmgr_updateSubscriber', NULL, 10);
INSERT INTO permission VALUES (271, 'listmgr_deleteSubscriber', NULL, 10);
INSERT INTO permission VALUES (272, 'listmgr_listLists', NULL, 10);
INSERT INTO permission VALUES (273, 'listmgr_addList', NULL, 10);
INSERT INTO permission VALUES (274, 'listmgr_editList', NULL, 10);
INSERT INTO permission VALUES (275, 'listmgr_updateList', NULL, 10);
INSERT INTO permission VALUES (276, 'listmgr_deleteLists', NULL, 10);
INSERT INTO permission VALUES (277, 'newslettermgr_subscribe', NULL, 10);
INSERT INTO permission VALUES (278, 'newslettermgr_unsubscribe', NULL, 10);
INSERT INTO permission VALUES (279, 'newslettermgr_authorize', NULL, 10);
INSERT INTO permission VALUES (280, 'categorymgr_reorder', NULL, 9);
INSERT INTO permission VALUES (281, 'categorymgr_reorderUpdate', NULL, 9);
INSERT INTO permission VALUES (282, 'rssmgr', NULL, 14);
INSERT INTO permission VALUES (283, 'bugmgr_list', NULL, 3);
INSERT INTO permission VALUES (284, 'bugmgr_send', NULL, 3);

--
-- Dumping data for table preference
--

INSERT INTO preference VALUES (1, 'sessionTimeout', '1800');
INSERT INTO preference VALUES (2, 'timezone', 'Europe/London');
INSERT INTO preference VALUES (3, 'theme', 'default');
INSERT INTO preference VALUES (4, 'dateFormat', 'UK');
INSERT INTO preference VALUES (5, 'language', 'en-iso-8859-15');
INSERT INTO preference VALUES (6, 'resPerPage', '10');
INSERT INTO preference VALUES (7, 'showExecutionTimes', '1');
INSERT INTO preference VALUES (8, 'locale', 'en_GB');

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

INSERT INTO role_permission VALUES (1, 0, 11);
INSERT INTO role_permission VALUES (2, 0, 10);
INSERT INTO role_permission VALUES (3, 0, 7);
INSERT INTO role_permission VALUES (4, 0, 6);
INSERT INTO role_permission VALUES (5, 0, 59);
INSERT INTO role_permission VALUES (6, 0, 58);
INSERT INTO role_permission VALUES (7, 0, 23);
INSERT INTO role_permission VALUES (8, 0, 122);
INSERT INTO role_permission VALUES (9, 0, 123);
INSERT INTO role_permission VALUES (10, 0, 98);
INSERT INTO role_permission VALUES (11, 0, 97);
INSERT INTO role_permission VALUES (12, 0, 108);
INSERT INTO role_permission VALUES (13, 0, 107);
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
INSERT INTO role_permission VALUES (56, 0, 84);
INSERT INTO role_permission VALUES (57, 0, 85);
INSERT INTO role_permission VALUES (58, 0, 86);
INSERT INTO role_permission VALUES (59, 2, 147);
INSERT INTO role_permission VALUES (63, 2, 149);
INSERT INTO role_permission VALUES (68, 0, 138);
INSERT INTO role_permission VALUES (69, 0, 224);
INSERT INTO role_permission VALUES (70, 0, 121);
INSERT INTO role_permission VALUES (71, 0, 225);
INSERT INTO role_permission VALUES (72, 0, 263);
INSERT INTO role_permission VALUES (73, 2, 224);
INSERT INTO role_permission VALUES (74, 2, 150);
INSERT INTO role_permission VALUES (75, 2, 225);
INSERT INTO role_permission VALUES (76, 2, 263);

--
-- Dumping data for table user_permission
--

INSERT INTO user_permission VALUES (1, 2, 11);
INSERT INTO user_permission VALUES (2, 2, 10);
INSERT INTO user_permission VALUES (3, 2, 7);
INSERT INTO user_permission VALUES (4, 2, 6);
INSERT INTO user_permission VALUES (5, 2, 59);
INSERT INTO user_permission VALUES (6, 2, 58);
INSERT INTO user_permission VALUES (7, 2, 23);
INSERT INTO user_permission VALUES (8, 2, 122);
INSERT INTO user_permission VALUES (9, 2, 123);
INSERT INTO user_permission VALUES (10, 2, 98);
INSERT INTO user_permission VALUES (11, 2, 97);
INSERT INTO user_permission VALUES (12, 2, 93);
INSERT INTO user_permission VALUES (13, 2, 96);
INSERT INTO user_permission VALUES (14, 2, 94);
INSERT INTO user_permission VALUES (15, 2, 95);
INSERT INTO user_permission VALUES (16, 2, 37);
INSERT INTO user_permission VALUES (17, 2, 36);
INSERT INTO user_permission VALUES (18, 2, 38);
INSERT INTO user_permission VALUES (19, 2, 84);
INSERT INTO user_permission VALUES (20, 2, 85);
INSERT INTO user_permission VALUES (21, 2, 86);
INSERT INTO user_permission VALUES (22, 2, 41);
INSERT INTO user_permission VALUES (23, 2, 40);
INSERT INTO user_permission VALUES (24, 2, 46);
INSERT INTO user_permission VALUES (25, 2, 43);
INSERT INTO user_permission VALUES (26, 2, 39);
INSERT INTO user_permission VALUES (27, 2, 42);
INSERT INTO user_permission VALUES (28, 2, 45);
INSERT INTO user_permission VALUES (29, 2, 117);
INSERT INTO user_permission VALUES (30, 2, 118);
INSERT INTO user_permission VALUES (31, 2, 121);
INSERT INTO user_permission VALUES (32, 2, 105);
INSERT INTO user_permission VALUES (33, 2, 106);
INSERT INTO user_permission VALUES (34, 2, 142);
INSERT INTO user_permission VALUES (35, 2, 143);
INSERT INTO user_permission VALUES (36, 2, 67);
INSERT INTO user_permission VALUES (37, 2, 60);
INSERT INTO user_permission VALUES (38, 2, 62);
INSERT INTO user_permission VALUES (39, 2, 61);
INSERT INTO user_permission VALUES (40, 2, 63);
INSERT INTO user_permission VALUES (41, 2, 66);
INSERT INTO user_permission VALUES (42, 2, 147);
INSERT INTO user_permission VALUES (43, 2, 239);
INSERT INTO user_permission VALUES (46, 2, 149);
INSERT INTO user_permission VALUES (47, 2, 224);
INSERT INTO user_permission VALUES (48, 2, 150);
INSERT INTO user_permission VALUES (49, 2, 225);
INSERT INTO user_permission VALUES (50, 2, 263);

--
-- Dumping data for table user_preference
--

INSERT INTO user_preference VALUES (1, 0, 1, '1800');
INSERT INTO user_preference VALUES (2, 0, 2, 'Europe/Berlin');
INSERT INTO user_preference VALUES (3, 0, 3, 'default');
INSERT INTO user_preference VALUES (4, 0, 4, 'UK');
INSERT INTO user_preference VALUES (5, 0, 5, 'de-iso-8859-1');
INSERT INTO user_preference VALUES (6, 0, 6, '10');
INSERT INTO user_preference VALUES (7, 0, 7, '0');
INSERT INTO user_preference VALUES (8, 0, 8, 'en_GB');

--
-- Dumping data for table usr
--

INSERT INTO usr VALUES (0, 1, 1, 'nobody', '21232f297a57a5a743894a0e4a801fc3', 'Nobody', 'Nobody', '', '', 'none@none.com', 'none', '', '', 'None', '', 'NN', '55555', 0, 0, 1, 'rover', '2003-12-09 18:02:44', 1, '2004-06-10 11:07:27', 1);
INSERT INTO usr VALUES (1, 1, 1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Admin', 'User', '', '', 'admin@example.com', '1 Seagull Drive', '', '', 'London', '', 'GB', '55555', 0, 1, 1, 'rover', '2003-12-09 18:02:44', 1, '2004-06-10 11:07:27', 1);
INSERT INTO usr VALUES (2, 1, 2, 'seagull', '21232f297a57a5a743894a0e4a801fc3', 'Test', 'User', '', '', 'seagull@example.com', '17 Daver Court', 'Mount Avenue', '', 'Ealing', '', 'GB', '55555', 0, 1, 1, 'rover', '2004-06-10 18:04:06', 1, '2004-06-10 18:04:06', 1);

--
-- Creating sequences
-- sequence must start on the first free record id
--

CREATE SEQUENCE organisation_seq START WITH 3;
CREATE SEQUENCE permission_seq START WITH 285;
CREATE SEQUENCE preference_seq START WITH 8;
CREATE SEQUENCE role_seq START WITH 3;
CREATE SEQUENCE role_permission_seq START WITH 77;
CREATE SEQUENCE user_preference_seq START WITH 8;
CREATE SEQUENCE usr_seq START WITH 3;
CREATE SEQUENCE user_permission_seq START WITH 51;