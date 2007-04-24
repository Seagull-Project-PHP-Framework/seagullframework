-- Last edited: Antonio J. Garcia 2007-04-24
-- Constraints for /user
-- leave "references" on a single line in order that table prefixes works

BEGIN;

alter table user_permission add foreign key (permission_id)
references permission (permission_id) on delete cascade;

alter table user_permission add foreign key (usr_id)
references usr (usr_id) on delete cascade;

alter table user_preference add foreign key (usr_id)
references usr (usr_id) on delete restrict on update restrict;

alter table user_preference add foreign key (preference_id)
references preference (preference_id) on delete restrict on update restrict;

alter table role_permission add foreign key (permission_id)
references permission (permission_id) on delete cascade;

alter table role_permission add foreign key (role_id)
references role (role_id) on delete cascade;

alter table org_preference add foreign key (organisation_id)
references organisation (organisation_id) on delete cascade;

alter table org_preference add foreign key (preference_id)
references preference (preference_id) on delete cascade;

-- why these constraints were removed? (sbaturzio 3/2005)
-- alter table user_preference add foreign key (usr_id) references usr (usr_id) on delete cascade;
-- alter table user_preference add foreign key (preference_id) references preference (preference_id) on delete cascade;

COMMIT;







