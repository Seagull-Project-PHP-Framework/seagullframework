-- 
--  `liveuser_applications`
-- 

INSERT INTO `liveuser_applications` VALUES (1, 'OPC');

-- 
--  `liveuser_area_admin_areas`
-- 


-- 
--  `liveuser_areas`
-- 

INSERT INTO `liveuser_areas` VALUES (1, 1, 'BACKEND');

-- 
--  `liveuser_group_subgroups`
-- 


-- 
--  `liveuser_grouprights`
-- 

INSERT INTO `liveuser_grouprights` VALUES (38, 9, 1);
INSERT INTO `liveuser_grouprights` VALUES (38, 6, 1);
INSERT INTO `liveuser_grouprights` VALUES (38, 8, 1);
INSERT INTO `liveuser_grouprights` VALUES (36, 9, 1);
INSERT INTO `liveuser_grouprights` VALUES (36, 4, 1);
INSERT INTO `liveuser_grouprights` VALUES (36, 6, 1);
INSERT INTO `liveuser_grouprights` VALUES (36, 8, 1);

-- 
--  `liveuser_groups`
-- 

INSERT INTO `liveuser_groups` VALUES (38, 1, 'MODERATOR');
INSERT INTO `liveuser_groups` VALUES (36, 1, 'GUEST');

-- 
--  `liveuser_groups_seq`
-- 

INSERT INTO `liveuser_groups_seq` VALUES (38);

-- 
--  `liveuser_groupusers`
-- 

INSERT INTO `liveuser_groupusers` VALUES (0, 38);

-- 
--  `liveuser_perm_users`
-- 

INSERT INTO `liveuser_perm_users` VALUES (1, '1', 1, '0');
INSERT INTO `liveuser_perm_users` VALUES (2, '2', 1, '0');

-- 
--  `liveuser_right_implied`
-- 


-- 
--  `liveuser_rights`
-- 

INSERT INTO `liveuser_rights` VALUES (30, 1, 'HUMAN_RIGHT', 'N', 'N', 'human right', '');

-- 
--  `liveuser_rights_seq`
-- 

INSERT INTO `liveuser_rights_seq` VALUES (30);

-- 
--  `liveuser_translations`
-- 

INSERT INTO `liveuser_translations` VALUES (5, 25, 4, 'en_iso_8859_15', 'dsf', 'sdf');
INSERT INTO `liveuser_translations` VALUES (6, 26, 4, 'en_iso_8859_15', 'dsf', 'sdf');
INSERT INTO `liveuser_translations` VALUES (12, 38, 3, 'en_iso_8859_15', 'moderator', 'User with permission to edit content');
INSERT INTO `liveuser_translations` VALUES (9, 30, 4, 'en_iso_8859_15', 'Human right', 'All people have their rights and lefts');
INSERT INTO `liveuser_translations` VALUES (10, 36, 3, 'en_iso_8859_15', 'guest', 'unauthorized users');

-- 
--  `liveuser_translations_seq`
-- 

INSERT INTO `liveuser_translations_seq` VALUES (12);

-- 
--  `liveuser_userrights`
-- 


-- 
--  `liveuser_users`
-- 


-- 
--  `right_permission`
-- 

INSERT INTO `right_permission` VALUES (7, 4, 52);
INSERT INTO `right_permission` VALUES (8, 4, 55);
INSERT INTO `right_permission` VALUES (15, 4, 3);
INSERT INTO `right_permission` VALUES (14, 4, 4);
INSERT INTO `right_permission` VALUES (13, 4, 1);
INSERT INTO `right_permission` VALUES (12, 4, 2);
INSERT INTO `right_permission` VALUES (16, 4, 7);
INSERT INTO `right_permission` VALUES (17, 4, 6);
INSERT INTO `right_permission` VALUES (26, 30, 55);
INSERT INTO `right_permission` VALUES (25, 30, 52);
INSERT INTO `right_permission` VALUES (24, 30, 104);
INSERT INTO `right_permission` VALUES (31, 30, 5);
INSERT INTO `right_permission` VALUES (32, 30, 51);
INSERT INTO `right_permission` VALUES (33, 30, 86);
INSERT INTO `right_permission` VALUES (34, 30, 90);