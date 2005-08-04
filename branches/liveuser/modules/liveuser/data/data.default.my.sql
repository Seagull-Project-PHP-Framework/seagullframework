
## insert sample rights
#INSERT INTO `liveuser_rights` ( `right_id` , `area_id` , `right_define_name` , `has_implied` , `has_level` , `name` , `description` ) 
#VALUES ('1','1','SGL_RIGHT_ADMIN','Y','N','admin','blah');

#INSERT INTO `liveuser_rights` ( `right_id` , `area_id` , `right_define_name` , `has_implied` , `has_level` , `name` , `description` ) 
#VALUES ('2','1','SGL_RIGHT_GRANT_RIGHTS','N','N','grant rights','User can grant rights to groups');

#INSERT INTO `liveuser_rights` ( `right_id` , `area_id` , `right_define_name` , `has_implied` , `has_level` , `name` , `description` ) 
#VALUES ('3','1','SGL_RIGHT_MANAGE_USER_GROUPS','N','N','Managing groups','Managing groups');

#INSERT INTO `liveuser_rights` ( `right_id` , `area_id` , `right_define_name` , `has_implied` , `has_level` , `name` , `description` ) 
#VALUES ('4','1','SGL_RIGHT_MANAGE_USERS','Y','N','Manage users accounts','Manage users accounts');

## if user has right with id = 1 he implied also 2, 3 and 4
#INSERT INTO `liveuser_right_implied` (`right_id`,`implied_right_id`) VALUES ('1','2');
#INSERT INTO `liveuser_right_implied` (`right_id`,`implied_right_id`) VALUES ('1','3');
#INSERT INTO `liveuser_right_implied` (`right_id`,`implied_right_id`) VALUES ('1','4');

## insert right for admin so he will always has admin right even if he will be deleted form admin
#INSERT INTO `liveuser_userrights` (`perm_user_id`,`right_id`,`right_level`) VALUES ('1','1','3');


-- 
-- Dumping data for table `liveuser_applications`
-- 

INSERT INTO `liveuser_applications` (`application_id`, `application_define_name`) VALUES (1, 'OPC');

-- 
-- Dumping data for table `liveuser_areas`
-- 

INSERT INTO `liveuser_areas` (`area_id`, `application_id`, `area_define_name`) VALUES (1, 1, 'BACKEND');

-- 
-- Dumping data for table `liveuser_grouprights`
-- 

INSERT INTO `liveuser_grouprights` (`group_id`, `right_id`, `right_level`) VALUES (6, 4, 1);
INSERT INTO `liveuser_grouprights` (`group_id`, `right_id`, `right_level`) VALUES (6, 6, 1);
INSERT INTO `liveuser_grouprights` (`group_id`, `right_id`, `right_level`) VALUES (6, 7, 1);
INSERT INTO `liveuser_grouprights` (`group_id`, `right_id`, `right_level`) VALUES (7, 6, 1);
INSERT INTO `liveuser_grouprights` (`group_id`, `right_id`, `right_level`) VALUES (7, 8, 1);
INSERT INTO `liveuser_grouprights` (`group_id`, `right_id`, `right_level`) VALUES (7, 9, 1);

-- 
-- Dumping data for table `liveuser_groups`
-- 

INSERT INTO `liveuser_groups` (`group_id`, `group_type`, `group_define_name`, `owner_user_id`, `owner_group_id`, `is_active`, `name`, `description`) VALUES (0, 1, 'guest', 1, 0, 'Y', NULL, 'public user');
INSERT INTO `liveuser_groups` (`group_id`, `group_type`, `group_define_name`, `owner_user_id`, `owner_group_id`, `is_active`, `name`, `description`) VALUES (1, 1, 'developer', 1, 0, 'Y', NULL, 'super user');
INSERT INTO `liveuser_groups` (`group_id`, `group_type`, `group_define_name`, `owner_user_id`, `owner_group_id`, `is_active`, `name`, `description`) VALUES (2, 1, 'member', 1, 0, 'Y', NULL, 'has a limited set of privileges');
INSERT INTO `liveuser_groups` (`group_id`, `group_type`, `group_define_name`, `owner_user_id`, `owner_group_id`, `is_active`, `name`, `description`) VALUES (6, 1, 'GROUP2', NULL, NULL, 'Y', 'group2', 'This is samplee group');
INSERT INTO `liveuser_groups` (`group_id`, `group_type`, `group_define_name`, `owner_user_id`, `owner_group_id`, `is_active`, `name`, `description`) VALUES (7, 1, 'SUPERUSERS', NULL, NULL, 'Y', 'superusers', 'some description...');
INSERT INTO `liveuser_groups` (`group_id`, `group_type`, `group_define_name`, `owner_user_id`, `owner_group_id`, `is_active`, `name`, `description`) VALUES (8, 1, 'MODERATORS', NULL, NULL, 'Y', 'moderators', 'Moderators are allowed to edit stuff');

-- 
-- Dumping data for table `liveuser_groupusers`
-- 

INSERT INTO `liveuser_groupusers` (`perm_user_id`, `group_id`) VALUES (1, 6);
INSERT INTO `liveuser_groupusers` (`perm_user_id`, `group_id`) VALUES (1, 7);
INSERT INTO `liveuser_groupusers` (`perm_user_id`, `group_id`) VALUES (1, 8);
INSERT INTO `liveuser_groupusers` (`perm_user_id`, `group_id`) VALUES (2, 6);
INSERT INTO `liveuser_groupusers` (`perm_user_id`, `group_id`) VALUES (2, 7);
INSERT INTO `liveuser_groupusers` (`perm_user_id`, `group_id`) VALUES (2, 8);

-- 
-- Dumping data for table `liveuser_perm_users`
-- 

INSERT INTO `liveuser_perm_users` (`perm_user_id`, `auth_user_id`, `perm_type`, `auth_container_name`) VALUES (1, '1', 1, '0');
INSERT INTO `liveuser_perm_users` (`perm_user_id`, `auth_user_id`, `perm_type`, `auth_container_name`) VALUES (2, '2', 1, '0');

-- 
-- Dumping data for table `liveuser_rights`
-- 

INSERT INTO `liveuser_rights` (`right_id`, `area_id`, `right_define_name`, `has_implied`, `has_level`, `name`, `description`) VALUES (4, 1, 'OPC_RIGHT_ADD_NEW_FORM', 'N', 'N', 'Adding new forms', 'With this right users could add new forms to some pgaes');
INSERT INTO `liveuser_rights` (`right_id`, `area_id`, `right_define_name`, `has_implied`, `has_level`, `name`, `description`) VALUES (6, 1, 'RIGHT_TO_LIVE', 'N', 'N', 'Right to live', 'Who want''s to live forever?');
INSERT INTO `liveuser_rights` (`right_id`, `area_id`, `right_define_name`, `has_implied`, `has_level`, `name`, `description`) VALUES (7, 1, 'RIGHT_TEST', 'N', 'N', 'Test right', 'Some test right...');
INSERT INTO `liveuser_rights` (`right_id`, `area_id`, `right_define_name`, `has_implied`, `has_level`, `name`, `description`) VALUES (8, 1, 'RIGHT_TO_EDIT', 'N', 'N', 'Edit right', 'Gives the right to edit entries');
INSERT INTO `liveuser_rights` (`right_id`, `area_id`, `right_define_name`, `has_implied`, `has_level`, `name`, `description`) VALUES (9, 1, 'SGL_LIVEUSER_SKASUJ_TO', 'N', 'N', 'SKASUJ_TO', 'Trial rights entry');
INSERT INTO `liveuser_rights` (`right_id`, `area_id`, `right_define_name`, `has_implied`, `has_level`, `name`, `description`) VALUES (66, 2, 'SDFG', 'N', 'N', 'foo', 'bar');