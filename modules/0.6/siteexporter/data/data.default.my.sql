INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'siteexporter', 'Site Exporter', NULL, NULL, NULL, 'Dmitri Lakachauskis', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'siteexportermgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'siteexportermgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'siteexportermgr_cmd_cliResult', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'siteexportermgr_cmd_run', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'siteexportermgr_cmd_runCollection', '', @moduleId);