INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'gallery2', 'Gallery', 'The ''Gallery'' module is a wrapper that allows you to seamlessly integrate Gallery2 into Seagull, see http://trac.seagullproject.org/wiki/Integration/Gallery.', 'gallery2', 'publisher.png', 'Matti Tahvonen', NULL, 'BSD', 'beta');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'gallery2mgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'gallery2mgr_cmd_list', '', @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'gallery2mgr_cmd_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);

