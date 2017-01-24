INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'emailqueue', 'EmailQueue', 'The ''EmailQueue'' module manages a common email queue.', NULL, NULL, 'Peter Termaten', NULL, 'BSD', 'beta');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'emailqueuemgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'emailqueuemgr_cmd_process', NULL, @moduleId);