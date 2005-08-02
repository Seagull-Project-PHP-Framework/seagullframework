# import groups
INSERT INTO `liveuser_groups` (`group_id`,`owner_perm_user_id`,`owner_group_id`,`is_active`,`group_define_name`,`description`)
select id, 1, '', 'Y', name, description from user_group where id <> -1;

# import users
insert into liveuser_perm_users (perm_user_id, auth_user_id, type)
select id, id, 1 from usr;

# import users to their groups
insert into liveuser_groupusers (perm_user_id, group_id)
select id, gid from usr where gid <> -1;

insert into sequences(name, id) select 'liveuser_groups', max(group_id) from liveuser_groups;