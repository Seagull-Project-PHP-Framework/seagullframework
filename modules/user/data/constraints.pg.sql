-- ==============================================================
--  DBMS name:      PostgreSQL 7.3                               
--  Created on:     2004-04-13 23:45:57                          
-- ==============================================================

-- Constraints for /user


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

alter table role_permission add foreign key (role_id) references role (role_id) on delete cascade;
alter table role_permission add foreign key (permission_id) references permission (permission_id) on delete cascade;

alter table user_permission add foreign key (permission_id) references permission (permission_id) on delete cascade;
alter table user_permission add foreign key (usr_id) references usr (usr_id) on delete cascade;

alter table user_preference add foreign key (usr_id) references usr (usr_id) on delete cascade;
alter table user_preference add foreign key (preference_id) references preference (preference_id) on delete cascade;

alter table org_preference add foreign key (organisation_id) references organisation (organisation_id) on delete cascade;
alter table org_preference add foreign key (preference_id) references preference (preference_id) on delete cascade;

alter table user_preference add constraint FK_preferene_user_preference foreign key (preference_id)
      references preference (preference_id) on delete restrict on update restrict;

alter table user_preference add constraint FK_usr_user_preferences foreign key (usr_id)
      references usr (usr_id) on delete restrict on update restrict;

alter table login add constraint FK_usr_login foreign key (usr_id)
      references usr (usr_id) on delete restrict on update restrict;

COMMIT;







