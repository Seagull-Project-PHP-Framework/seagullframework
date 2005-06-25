-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Data dump for /modules/user


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

-- 
-- Organisation table
--

INSERT INTO organisation VALUES (1,0,1,1,2,1,1,2,0,'default org','test','aasdfasdf','','','asdfadf','AL','BJ','55555','325 652 5645','http://example.com','test@test.com','2004-01-12 16:13:21',NULL,'2004-06-23 10:44:52',1);
INSERT INTO organisation VALUES (2,0,2,1,2,2,1,2,0,'sainsburys','test','aasdfasdf','','','asdfadf','AL','BJ','asdfasdf','325 652 5645','http://sainsburys.com','info@sainsburys.com','2004-01-12 16:13:21',NULL,'2004-06-23 10:44:56',1);

--
-- Permission table
--

INSERT INTO permission (permission_id, name, description, module_id) VALUES (1, 'blockmgr_add', '', 1);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (2, 'blockmgr_edit', '', 1);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (3, 'blockmgr_delete', '', 1);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (4, 'blockmgr_reorder', '', 1);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (5, 'blockmgr_list', '', 1);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (6, 'contactusmgr_send', '', 2);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (7, 'contactusmgr_list', '', 2);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (8, 'configmgr_edit', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (9, 'configmgr_insert', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (10, 'defaultmgr_list', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (11, 'defaultmgr_showNews', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (12, 'modulemgr_add', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (13, 'modulemgr_insert', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (14, 'modulemgr_delete', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (15, 'modulemgr_list', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (16, 'modulemgr_overview', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (17, 'documentormgr_list', '', 4);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (18, 'faqmgr_add', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (19, 'faqmgr_insert', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (20, 'faqmgr_edit', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (21, 'faqmgr_update', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (22, 'faqmgr_delete', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (23, 'faqmgr_list', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (24, 'faqmgr_reorder', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (25, 'faqmgr_reorderUpdate', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (26, 'guestbookmgr_list', '', 6);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (27, 'guestbookmgr_add', '', 6);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (28, 'guestbookmgr_insert', '', 6);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (30, 'maintenancemgr_edit', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (31, 'maintenancemgr_update', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (32, 'maintenancemgr_append', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (33, 'maintenancemgr_dbgen', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (34, 'maintenancemgr_clearCache', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (35, 'maintenancemgr_list', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (36, 'contactmgr_insert', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (37, 'contactmgr_delete', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (38, 'contactmgr_list', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (39, 'imessagemgr_read', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (40, 'imessagemgr_delete', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (41, 'imessagemgr_compose', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (42, 'imessagemgr_reply', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (43, 'imessagemgr_insert', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (45, 'imessagemgr_outbox', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (46, 'imessagemgr_inbox', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (47, 'navstylemgr_changeStyle', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (48, 'navstylemgr_list', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (49, 'pagemgr_add', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (50, 'pagemgr_insert', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (51, 'pagemgr_edit', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (52, 'pagemgr_update', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (53, 'pagemgr_delete', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (54, 'pagemgr_reorder', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (55, 'pagemgr_list', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (56, 'newslettermgr_send', '', 10);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (57, 'newslettermgr_addressBook', '', 10);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (58, 'articleviewmgr_view', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (59, 'articleviewmgr_summary', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (60, 'articlemgr_add', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (61, 'articlemgr_insert', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (62, 'articlemgr_edit', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (63, 'articlemgr_update', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (64, 'articlemgr_changeStatus', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (65, 'articlemgr_delete', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (66, 'articlemgr_view', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (67, 'articlemgr_list', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (68, 'categorymgr_add', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (69, 'categorymgr_insert', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (70, 'categorymgr_edit', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (71, 'categorymgr_update', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (73, 'rolemgr_duplicate', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (75, 'categorymgr_list', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (76, 'documentmgr_add', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (77, 'documentmgr_insert', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (78, 'documentmgr_edit', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (79, 'documentmgr_update', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (80, 'documentmgr_setDownload', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (81, 'documentmgr_view', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (82, 'documentmgr_delete', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (83, 'documentmgr_list', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (84, 'filemgr_download', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (85, 'filemgr_downloadZipped', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (86, 'filemgr_view', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (89, 'rndmsgmgr_add', '', 13);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (90, 'rndmsgmgr_insert', '', 13);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (91, 'rndmsgmgr_delete', '', 13);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (92, 'rndmsgmgr_list', '', 13);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (93, 'accountmgr_edit', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (94, 'accountmgr_update', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (95, 'accountmgr_viewProfile', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (96, 'accountmgr_summary', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (97, 'loginmgr_login', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (98, 'loginmgr_list', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (99, 'orgmgr_add', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (100, 'orgmgr_insert', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (101, 'orgmgr_edit', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (102, 'orgmgr_update', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (103, 'orgmgr_delete', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (104, 'orgmgr_list', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (105, 'passwordmgr_edit', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (106, 'passwordmgr_update', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (107, 'passwordmgr_retrieve', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (108, 'passwordmgr_forgot', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (109, 'permissionmgr_add', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (110, 'permissionmgr_insert', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (111, 'permissionmgr_edit', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (112, 'permissionmgr_update', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (113, 'permissionmgr_delete', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (114, 'permissionmgr_list', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (115, 'preferencemgr_add', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (116, 'preferencemgr_insert', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (117, 'preferencemgr_edit', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (118, 'preferencemgr_update', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (119, 'preferencemgr_delete', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (120, 'preferencemgr_list', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (121, 'profilemgr_view', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (122, 'registermgr_add', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (123, 'registermgr_insert', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (124, 'rolemgr_add', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (125, 'rolemgr_insert', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (126, 'rolemgr_edit', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (127, 'rolemgr_update', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (128, 'rolemgr_delete', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (129, 'rolemgr_list', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (130, 'rolemgr_editPerms', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (131, 'rolemgr_updatePerms', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (132, 'usermgr_add', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (133, 'usermgr_insert', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (134, 'usermgr_edit', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (135, 'usermgr_update', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (136, 'usermgr_delete', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (137, 'usermgr_list', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (138, 'usermgr_requestPasswordReset', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (139, 'usermgr_resetPassword', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (140, 'usermgr_editPerms', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (141, 'usermgr_updatePerms', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (142, 'userpreferencemgr_editAll', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (143, 'userpreferencemgr_updateAll', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (145, 'newslettermgr_list', '', 10);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (146, 'orgpreferencemgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (147, 'accountmgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (148, 'accountmgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (149, 'loginmgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (150, 'loginmgr_logout', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (151, 'orgmgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (152, 'orgmgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (153, 'orgpreferencemgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (154, 'orgpreferencemgr_editAll', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (155, 'orgpreferencemgr_updateAll', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (156, 'passwordmgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (157, 'passwordmgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (158, 'permissionmgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (159, 'permissionmgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (160, 'permissionmgr_scanNew', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (161, 'permissionmgr_insertNew', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (162, 'permissionmgr_scanOrphaned', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (163, 'permissionmgr_deleteOrphaned', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (164, 'preferencemgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (165, 'preferencemgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (166, 'profilemgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (167, 'registermgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (168, 'registermgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (169, 'rolemgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (170, 'rolemgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (171, 'userimportmgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (172, 'userimportmgr_list', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (173, 'userimportmgr_insertImportedUsers', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (174, 'userimportmgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (175, 'usermgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (176, 'usermgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (177, 'usermgr_search', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (178, 'usermgr_syncToRole', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (179, 'userpreferencemgr', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (180, 'userpreferencemgr_redirectToDefault', '', 12);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (181, 'rndmsgmgr', '', 13);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (182, 'rndmsgmgr_redirectToDefault', '', 13);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (183, 'articlemgr', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (184, 'articlemgr_redirectToDefault', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (185, 'articleviewmgr', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (186, 'documentmgr', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (187, 'documentmgr_redirectToDefault', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (188, 'filemgr', '', 11);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (189, 'newslettermgr', '', 10);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (190, 'newslettermgr_redirectToDefault', '', 10);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (191, 'categorymgr', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (192, 'categorymgr_redirectToDefault', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (193, 'categorymgr_delete', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (194, 'navstylemgr', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (195, 'navstylemgr_redirectToDefault', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (196, 'pagemgr', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (197, 'pagemgr_redirectToDefault', '', 9);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (198, 'contactmgr', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (199, 'contactmgr_redirectToDefault', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (200, 'imessagemgr', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (201, 'imessagemgr_redirectToDefault', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (202, 'imessagemgr_sendAlert', '', 8);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (203, 'maintenancemgr', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (204, 'maintenancemgr_verify', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (205, 'maintenancemgr_redirectToDefault', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (206, 'maintenancemgr_checkAllModules', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (207, 'maintenancemgr_rebuildSequences', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (208, 'maintenancemgr_createModule', '', 7);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (209, 'guestbookmgr', '', 6);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (210, 'guestbookmgr_redirectToDefault', '', 6);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (211, 'faqmgr', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (212, 'faqmgr_redirectToDefault', '', 5);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (213, 'documentormgr', '', 4);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (214, 'configmgr', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (215, 'configmgr_redirectToDefault', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (216, 'defaultmgr', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (217, 'modulemgr', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (218, 'modulemgr_redirectToDefault', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (219, 'modulemgr_edit', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (220, 'modulemgr_update', '', 3);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (221, 'contactusmgr', '', 2);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (222, 'contactusmgr_redirectToDefault', '', 2);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (223, 'blockmgr', '', 1);
INSERT INTO permission (permission_id, name, description, module_id) VALUES (224, 'blockmgr_redirectToDefault', '', 1);

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
-- Dumping data for table role
--

INSERT INTO role VALUES (-1,'unassigned','not assigned a role',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (0,'guest','public user',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (1,'root','super user',NULL,NULL,NULL,NULL);
INSERT INTO role VALUES (2,'member','has a limited set of privileges',NULL,NULL,NULL,NULL);

--
-- Dumping data for table role_permission
--

INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (1, 0, 11);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (2, 0, 10);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (3, 0, 7);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (4, 0, 6);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (5, 0, 59);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (6, 0, 58);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (7, 0, 23);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (8, 0, 122);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (9, 0, 123);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (10, 0, 98);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (11, 0, 97);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (12, 0, 108);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (13, 0, 107);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (14, 2, 11);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (15, 2, 10);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (16, 2, 7);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (17, 2, 6);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (18, 2, 59);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (19, 2, 58);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (20, 2, 23);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (21, 2, 122);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (22, 2, 123);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (23, 2, 98);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (24, 2, 97);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (25, 2, 93);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (26, 2, 96);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (27, 2, 94);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (28, 2, 95);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (29, 2, 37);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (30, 2, 36);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (31, 2, 38);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (32, 2, 84);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (33, 2, 85);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (34, 2, 86);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (36, 2, 41);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (37, 2, 40);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (38, 2, 46);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (39, 2, 43);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (40, 2, 39);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (41, 2, 42);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (42, 2, 45);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (43, 2, 117);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (44, 2, 118);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (45, 2, 121);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (46, 2, 105);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (47, 2, 106);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (48, 2, 142);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (49, 2, 143);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (50, 2, 67);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (51, 2, 60);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (52, 2, 62);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (53, 2, 61);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (54, 2, 63);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (55, 2, 66);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (56, 0, 84);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (57, 0, 85);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (58, 0, 86);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (59, 2, 147);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (60, 2, 199);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (61, 2, 222);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (62, 2, 201);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (63, 2, 149);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (64, 2, 157);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (65, 2, 165);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (66, 2, 180);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (67, 0, 222);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (68, 0, 138);
INSERT INTO role_permission (role_permission_id, role_id, permission_id) VALUES (69, 0, 168);

--
-- Dumping data for table user_permission
--

INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (1, 2, 11);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (2, 2, 10);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (3, 2, 7);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (4, 2, 6);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (5, 2, 59);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (6, 2, 58);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (7, 2, 23);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (8, 2, 122);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (9, 2, 123);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (10, 2, 98);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (11, 2, 97);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (12, 2, 93);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (13, 2, 96);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (14, 2, 94);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (15, 2, 95);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (16, 2, 37);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (17, 2, 36);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (18, 2, 38);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (19, 2, 84);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (20, 2, 85);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (21, 2, 86);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (22, 2, 41);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (23, 2, 40);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (24, 2, 46);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (25, 2, 43);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (26, 2, 39);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (27, 2, 42);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (28, 2, 45);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (29, 2, 117);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (30, 2, 118);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (31, 2, 121);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (32, 2, 105);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (33, 2, 106);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (34, 2, 142);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (35, 2, 143);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (36, 2, 67);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (37, 2, 60);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (38, 2, 62);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (39, 2, 61);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (40, 2, 63);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (41, 2, 66);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (42, 2, 147);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (43, 2, 199);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (44, 2, 222);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (45, 2, 201);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (46, 2, 149);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (47, 2, 157);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (48, 2, 165);
INSERT INTO user_permission (user_permission_id, usr_id, permission_id) VALUES (49, 2, 180);

--
-- Dumping data for table user_preference
--

INSERT INTO user_preference VALUES (1, 0, 1, '1800');
INSERT INTO user_preference VALUES (2, 0, 2, 'Europe/London');
INSERT INTO user_preference VALUES (3, 0, 3, 'default');
INSERT INTO user_preference VALUES (4, 0, 4, 'UK');
INSERT INTO user_preference VALUES (5, 0, 5, 'en-iso-8859-15');
INSERT INTO user_preference VALUES (6, 0, 6, '10');
INSERT INTO user_preference VALUES (7, 0, 7, '1');
INSERT INTO user_preference VALUES (8, 0, 8, 'en_GB');

--
-- Dumping data for table usr
--

-- INSERT INTO usr (usr_id, organisation_id, role_id, username, passwd, first_name, last_name, telephone, mobile, email, addr_1, addr_2, addr_3, city, region, country, post_code, is_email_public, is_acct_active, security_question, security_answer, date_created, created_by, last_updated, updated_by) VALUES (0, 1, 1, 'nobody', '21232f297a57a5a743894a0e4a801fc3', 'Nobody', 'Nobody', '', '', 'none@none.com', 'none', '', '', 'None', '', 'NN', '55555', 0, 0, 1, 'rover', '2003-12-09 18:02:44', 1, '2004-06-10 11:07:27', 1);
-- INSERT INTO usr (usr_id, organisation_id, role_id, username, passwd, first_name, last_name, telephone, mobile, email, addr_1, addr_2, addr_3, city, region, country, post_code, is_email_public, is_acct_active, security_question, security_answer, date_created, created_by, last_updated, updated_by) VALUES (1, 1, 1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Admin', 'User', '', '', 'webmaster@phpkitchen.com', '1 Seagull Drive', '', '', 'London', '', 'GB', '55555', 0, 1, 1, 'rover', '2003-12-09 18:02:44', 1, '2004-06-10 11:07:27', 1);
-- INSERT INTO usr (usr_id, organisation_id, role_id, username, passwd, first_name, last_name, telephone, mobile, email, addr_1, addr_2, addr_3, city, region, country, post_code, is_email_public, is_acct_active, security_question, security_answer, date_created, created_by, last_updated, updated_by) VALUES (2, 1, 2, 'seagull', '21232f297a57a5a743894a0e4a801fc3', 'Test', 'User', '', '', 'demian@phpkitchen.com', '17 Daver Court', 'Mount Avenue', '', 'Ealing', '', 'GB', '55555', 0, 1, 1, 'rover', '2004-06-10 18:04:06', 1, '2004-06-10 18:04:06', 1);

INSERT INTO usr VALUES (0, 1, 1, 'nobody', '21232f297a57a5a743894a0e4a801fc3', 'Nobody', 'Nobody', '', '', 'none@none.com', 'none', '', '', 'None', '', 'NN', '55555', 0, 0, 1, 'rover', '2003-12-09 18:02:44', 1, '2004-06-10 11:07:27', 1);
INSERT INTO usr VALUES (1,1,1,'admin','21232f297a57a5a743894a0e4a801fc3','Admin','User','','','webmaster@phpkitchen.com','1 Seagull Drive','','','London','','GB','55555',0,1,1,'rover','2003-12-09 18:02:44',1,'2004-06-10 11:07:27',1);
INSERT INTO usr VALUES (2,1,2,'seagull','21232f297a57a5a743894a0e4a801fc3','Test','User','','','demian@phpkitchen.com','17 Daver Court','Mount Avenue','','Ealing','','GB','55555',0,1,1,'rover','2004-06-10 18:04:06',1,'2004-06-10 18:04:06',1);


COMMIT;
