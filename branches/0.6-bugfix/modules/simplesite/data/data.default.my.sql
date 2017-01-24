INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'simplesite', 'Simplesite', 'A single manager, routes and array based nav to create simple, template-based sites.', '', '48/module_default.png', 'Demian Turner', NULL, 'NULL', 'NULL');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'simplesitemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'simplesitemgr_cmd_list', '', @moduleId);