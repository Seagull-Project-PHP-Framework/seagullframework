-- 
-- Дамп даних таблиці `liveuser_applications`
-- 

INSERT INTO liveuser_applications VALUES (1, 'OPC');

-- 
-- Дамп даних таблиці `liveuser_area_admin_areas`
-- 


-- 
-- Дамп даних таблиці `liveuser_areas`
-- 

INSERT INTO liveuser_areas VALUES (1, 1, 'BACKEND');

-- 
-- Дамп даних таблиці `liveuser_group_subgroups`
-- 


-- 
-- Дамп даних таблиці `liveuser_grouprights`
-- 

INSERT INTO liveuser_grouprights VALUES (6, 4, 1);
INSERT INTO liveuser_grouprights VALUES (6, 6, 1);
INSERT INTO liveuser_grouprights VALUES (6, 7, 1);
INSERT INTO liveuser_grouprights VALUES (7, 6, 1);
INSERT INTO liveuser_grouprights VALUES (7, 8, 1);
INSERT INTO liveuser_grouprights VALUES (7, 9, 1);
INSERT INTO liveuser_grouprights VALUES (8, 8, 1);
INSERT INTO liveuser_grouprights VALUES (8, 6, 1);
INSERT INTO liveuser_grouprights VALUES (8, 7, 1);
INSERT INTO liveuser_grouprights VALUES (2, 6, 1);
INSERT INTO liveuser_grouprights VALUES (2, 9, 1);
INSERT INTO liveuser_grouprights VALUES (2, 7, 1);

-- 
-- Дамп даних таблиці `liveuser_groups`
-- 

INSERT INTO liveuser_groups VALUES (0, 1, 'guest');
INSERT INTO liveuser_groups VALUES (1, 1, 'developer');
INSERT INTO liveuser_groups VALUES (2, 1, 'member');
INSERT INTO liveuser_groups VALUES (6, 1, 'GROUP2');
INSERT INTO liveuser_groups VALUES (7, 1, 'SUPERUSERS');
INSERT INTO liveuser_groups VALUES (8, 1, 'MODERATORS');

-- 
-- Дамп даних таблиці `liveuser_groups_seq`
-- 

INSERT INTO liveuser_groups_seq VALUES (32);

-- 
-- Дамп даних таблиці `liveuser_groupusers`
-- 

INSERT INTO liveuser_groupusers VALUES (0, 0);
INSERT INTO liveuser_groupusers VALUES (1, 6);
INSERT INTO liveuser_groupusers VALUES (2, 6);
INSERT INTO liveuser_groupusers VALUES (1, 7);
INSERT INTO liveuser_groupusers VALUES (2, 7);
INSERT INTO liveuser_groupusers VALUES (1, 8);
INSERT INTO liveuser_groupusers VALUES (2, 8);

-- 
-- Дамп даних таблиці `liveuser_perm_users`
-- 

INSERT INTO liveuser_perm_users VALUES (1, '1', 1, '0');
INSERT INTO liveuser_perm_users VALUES (2, '2', 1, '0');

-- 
-- Дамп даних таблиці `liveuser_right_implied`
-- 


-- 
-- Дамп даних таблиці `liveuser_rights`
-- 

INSERT INTO liveuser_rights VALUES (4, 1, 'OPC_RIGHT_ADD_NEW_FORM', 'N', 'N', 'Adding new forms', 'With this right users could add new forms to some pgaes');
INSERT INTO liveuser_rights VALUES (6, 1, 'RIGHT_TO_LIVE', 'N', 'N', 'Right to live', 'Who want''s to live forever?');
INSERT INTO liveuser_rights VALUES (7, 1, 'RIGHT_TEST', 'N', 'N', 'Test right', 'Some test right...');
INSERT INTO liveuser_rights VALUES (8, 1, 'RIGHT_TO_EDIT', 'N', 'N', 'Edit right', 'Gives the right to edit entries');
INSERT INTO liveuser_rights VALUES (9, 1, 'SGL_LIVEUSER_SKASUJ_TO', 'N', 'N', 'SKASUJ_TO', 'Trial rights entry');
INSERT INTO liveuser_rights VALUES (30, 1, 'HUMAN_RIGHT', 'N', 'N', NULL, NULL);
INSERT INTO liveuser_rights VALUES (28, 1, 'TEST', 'N', 'N', NULL, NULL);

-- 
-- Дамп даних таблиці `liveuser_rights_seq`
-- 

INSERT INTO liveuser_rights_seq VALUES (30);

-- 
-- Дамп даних таблиці `liveuser_translations`
-- 

INSERT INTO liveuser_translations VALUES (5, 25, 4, 'en_iso_8859_15', 'dsf', 'sdf');
INSERT INTO liveuser_translations VALUES (6, 26, 4, 'en_iso_8859_15', 'dsf', 'sdf');
INSERT INTO liveuser_translations VALUES (7, 28, 4, 'en_iso_8859_15', 'test', 'testik');
INSERT INTO liveuser_translations VALUES (8, 0, 4, 'en_iso_8859_15', 'test', 'testik');
INSERT INTO liveuser_translations VALUES (9, 30, 4, 'en_iso_8859_15', 'Human right', 'All people have their right and lefts');

-- 
-- Дамп даних таблиці `liveuser_translations_seq`
-- 

INSERT INTO liveuser_translations_seq VALUES (9);

-- 
-- Дамп даних таблиці `liveuser_userrights`
-- 


-- 
-- Дамп даних таблиці `liveuser_users`
-- 


-- 
-- Дамп даних таблиці `right_permission`
-- 

INSERT INTO right_permission VALUES (7, 4, 52);
INSERT INTO right_permission VALUES (8, 4, 55);
INSERT INTO right_permission VALUES (15, 4, 3);
INSERT INTO right_permission VALUES (14, 4, 4);
INSERT INTO right_permission VALUES (13, 4, 1);
INSERT INTO right_permission VALUES (12, 4, 2);
INSERT INTO right_permission VALUES (16, 4, 7);
INSERT INTO right_permission VALUES (17, 4, 6);
INSERT INTO right_permission VALUES (18, 28, 55);
INSERT INTO right_permission VALUES (19, 28, 53);
INSERT INTO right_permission VALUES (20, 28, 54);
INSERT INTO right_permission VALUES (21, 28, 1);
INSERT INTO right_permission VALUES (22, 28, 2);
INSERT INTO right_permission VALUES (23, 28, 4);
        