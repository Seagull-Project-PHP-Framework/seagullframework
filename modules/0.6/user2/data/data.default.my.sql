INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'user2', 'User2', NULL, NULL, NULL, 'Dmitri Lakachauskis', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;