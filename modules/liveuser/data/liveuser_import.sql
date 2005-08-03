# import groups
INSERT INTO `liveuser_groups` (`group_id`,`owner_user_id`,`owner_group_id`,`is_active`,`group_define_name`,`description`)
select role_id, 1, '', 'Y', name, description from role where role_id <> -1;

# import users
insert into liveuser_perm_users (perm_user_id, auth_user_id, perm_type)
select usr_id, usr_id, 1 from usr;

# import users to their groups
insert into liveuser_groupusers (perm_user_id, group_id)
select usr_id, role_id from usr where role_id <> -1;

#insert into sequences(name, id) select 'liveuser_groups', max(group_id) from liveuser_groups;