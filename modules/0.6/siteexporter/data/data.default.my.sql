INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'siteexporter', 'Site Exporter', NULL, NULL, NULL, 'Dmitri Lakachauskis', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;