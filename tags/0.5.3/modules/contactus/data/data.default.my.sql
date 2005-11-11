INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'contactus', 'Contact Us', 'The ''Contact Us'' module can be used to present a form to your users allowing them to contact the site administrators.', NULL, 'contactus.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr_send', 'Permission to submit contact info', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr_list', 'Permission to view Contact Us screen', @moduleId);

#guest role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactusmgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactusmgr_send';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);

#member roles perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_delete';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactmgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactusmgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'contactusmgr_send';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);