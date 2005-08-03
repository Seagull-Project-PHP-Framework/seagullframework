
# insert sample rights
INSERT INTO `liveuser_rights` ( `right_id` , `area_id` , `right_define_name` , `has_implied` , `has_level` , `name` , `description` ) 
VALUES ('1','1','SGL_RIGHT_ADMIN','Y','N','admin','blah');

INSERT INTO `liveuser_rights` ( `right_id` , `area_id` , `right_define_name` , `has_implied` , `has_level` , `name` , `description` ) 
VALUES ('2','1','SGL_RIGHT_GRANT_RIGHTS','N','N','grant rights','User can grant rights to groups');

INSERT INTO `liveuser_rights` ( `right_id` , `area_id` , `right_define_name` , `has_implied` , `has_level` , `name` , `description` ) 
VALUES ('3','1','SGL_RIGHT_MANAGE_USER_GROUPS','N','N','Managing groups','Managing groups');

INSERT INTO `liveuser_rights` ( `right_id` , `area_id` , `right_define_name` , `has_implied` , `has_level` , `name` , `description` ) 
VALUES ('4','1','SGL_RIGHT_MANAGE_USERS','Y','N','Manage users accounts','Manage users accounts');

# if user has right with id = 1 he implied also 2, 3 and 4
INSERT INTO `liveuser_right_implied` (`right_id`,`implied_right_id`) VALUES ('1','2');
INSERT INTO `liveuser_right_implied` (`right_id`,`implied_right_id`) VALUES ('1','3');
INSERT INTO `liveuser_right_implied` (`right_id`,`implied_right_id`) VALUES ('1','4');

# insert right for admin so he will always has admin right even if he will be deleted form admin
INSERT INTO `liveuser_userrights` (`perm_user_id`,`right_id`,`right_level`) VALUES ('1','1','3');
