INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'sitemap', 'Sitemap', NULL, NULL, NULL, 'Dmitri Lakachauskis', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sitemapmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sitemapmgr_cmd_list', '', @moduleId);