ALTER TABLE `role_permission` ADD FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE;
ALTER TABLE `role_permission` ADD FOREIGN KEY (`permission_id`) REFERENCES `permission` (`permission_id`) ON DELETE CASCADE;

ALTER TABLE `user_permission` ADD FOREIGN KEY (`permission_id`) REFERENCES `permission` (`permission_id`) ON DELETE CASCADE;
ALTER TABLE `user_permission` ADD FOREIGN KEY (`usr_id`) REFERENCES `usr` (`usr_id`) ON DELETE CASCADE;

ALTER TABLE `user_preference` ADD FOREIGN KEY (`usr_id`) REFERENCES `usr` (`usr_id`) ON DELETE CASCADE;
ALTER TABLE `user_preference` ADD FOREIGN KEY (`preference_id`) REFERENCES `preference` (`preference_id`) ON DELETE CASCADE;

ALTER TABLE `org_preference` ADD FOREIGN KEY (`organisation_id`) REFERENCES `organisation` (`organisation_id`) ON DELETE CASCADE;
ALTER TABLE `org_preference` ADD FOREIGN KEY (`preference_id`) REFERENCES `preference` (`preference_id`) ON DELETE CASCADE;

-- alter table login add constraint FK_usr_login foreign key (usr_id)
--       references usr (usr_id) on delete restrict on update restrict;
-- 
-- alter table user_preference add constraint FK_preference_user_preference foreign key (preference_id)
--       references preference (preference_id) on delete restrict on update restrict;
-- 
-- alter table user_preference add constraint FK_usr_user_preferences foreign key (usr_id)
--       references usr (usr_id) on delete restrict on update restrict;