INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'gallery2', 'Gallery', 'Use the ''Gallery'' to manage image albums and galleries.', 'gallery2', 'publisher.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'gallery2mgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'gallery2mgr_cmd_list', '', @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'gallerymgr_cmd_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);

