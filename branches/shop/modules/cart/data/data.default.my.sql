INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'cart', 'Cart', 'Universal cart module with basket and order management.', 'cart/cartAdmin', 'default.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID},'cartmgr',NULL,@moduleId);

#guest role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'cartmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID},0,@permissionId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'cartmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID},2,@permissionId);