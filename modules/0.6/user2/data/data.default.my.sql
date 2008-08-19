INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'user2', 'User2', NULL, NULL, NULL, 'Dmitri Lakachauskis', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;
SELECT @memberId := 2;

--
-- Add permissions
--
INSERT INTO `permission` VALUES ({SGL_NEXT_ID}, 'account2mgr', '', @moduleId);
SELECT @permissionId := `permission_id` FROM `permission` WHERE `name` = 'account2mgr';
INSERT INTO `role_permission` VALUES ({SGL_NEXT_ID}, @memberId, @permissionId);