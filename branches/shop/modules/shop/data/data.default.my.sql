INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'shop', 'Shop', 'This is the Shop Manager. Add and edit your products, prices and discounts here.', 'shop/shopadmin', 'default.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID},'shopmgr',NULL,@moduleId);

#guest role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'shopmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID},0,@permissionId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'shopmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID},2,@permissionId);