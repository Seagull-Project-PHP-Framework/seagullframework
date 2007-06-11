-- ==============================================================
--  create foreign keys
-- ==============================================================
alter table role_permission add foreign key (role_id) references Role (role_id) on delete cascade;
alter table role_permission add foreign key (permission_id) references Permission (permission_id) on delete cascade;
alter table user_permission add foreign key (permission_id) references permission (permission_id) on delete cascade;
alter table user_permission add foreign key (usr_id) references usr (usr_id) on delete cascade;
alter table user_preference add foreign key (usr_id) references usr (usr_id) on delete cascade;
alter table user_preference add foreign key (preference_id) references preference (preference_id) on delete cascade;
alter table org_preference add foreign key (organisation_id) references organisation (organisation_id) on delete cascade;
alter table org_preference add foreign key (preference_id) references preference (preference_id) on delete cascade;
alter table login add constraint FK_usr_login foreign key (usr_id) references usr (usr_id) on delete cascade;
alter table usr add foreign key (role_id) references Role (role_id);
alter table organisation add foreign key (organisation_type_id) references organisation_type (organisation_type_id);
