/*==============================================================*/
/* Table: usr                                                   */
/*==============================================================*/
ALTER TABLE `usr`
    ADD `about` TEXT NULL AFTER `email`;
ALTER TABLE `usr`
    ADD `gender` CHAR(1) NULL AFTER `email`;

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'user2', 'User2', NULL, NULL, NULL, 'Dmitri Lakachauskis', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;
SELECT @rootId   := 1;
SELECT @memberId := 2;

--
-- Add permissions
--
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'account2mgr', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'account2mgr_cmd_list', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'login2mgr', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'login2mgr_cmd_logout', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'login2mgr_cmd_register', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'login2mgr_cmd_login', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'passwordrecoverymgr', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'passwordrecoverymgr_cmd_list', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'passwordrecoverymgr_cmd_reset', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'profile2mgr', '', @moduleId);
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'profile2mgr_cmd_list', '', @moduleId);

#member role perms
SELECT @permissionId := `permission_id` FROM `permission` WHERE `name` = 'account2mgr';
INSERT INTO `role_permission` VALUES ({SGL_NEXT_ID}, @memberId, @permissionId);

--
-- Default mime types
--
-- SELECT @mediaMimeId_gif := `media_mime_id` FROM `media_mime` WHERE `name` = 'gif image';
-- SELECT @mediaMimeId_jpg := `media_mime_id` FROM `media_mime` WHERE `name` = 'jpeg image';
-- SELECT @mediaMimeId_png := `media_mime_id` FROM `media_mime` WHERE `name` = 'png image';

--
-- Media types
--
-- INSERT INTO `media_type` VALUES ({SGL_NEXT_ID}, 'profile', 'profile image');
-- SELECT @mediaTypeId_profile := `media_type_id` FROM `media_type` WHERE `name` = 'profile';

-- INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_gif);
-- INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_jpg);
-- INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_png);