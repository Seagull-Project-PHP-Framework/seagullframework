-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Constraints for /user

BEGIN;

alter table role_permission add foreign key (role_id) references role (role_id) on delete cascade;
alter table role_permission add foreign key (permission_id) references permission (permission_id) on delete cascade;

alter table user_permission add foreign key (permission_id) references permission (permission_id) on delete cascade;
alter table user_permission add foreign key (usr_id) references usr (usr_id) on delete cascade;

alter table user_preference add foreign key (usr_id) references usr (usr_id) on delete cascade;
alter table user_preference add foreign key (preference_id) references preference (preference_id) on delete cascade;

alter table org_preference add foreign key (organisation_id) references organisation (organisation_id) on delete cascade;
alter table org_preference add foreign key (preference_id) references preference (preference_id) on delete cascade;

alter table login add constraint FK_usr_login foreign key (usr_id)
      references usr (usr_id) on delete restrict on update restrict;

alter table user_preference add constraint FK_preferene_user_preference foreign key (preference_id)
      references preference (preference_id) on delete restrict on update restrict;

alter table user_preference add constraint FK_usr_user_preferences foreign key (usr_id)
      references usr (usr_id) on delete restrict on update restrict;

COMMIT;







