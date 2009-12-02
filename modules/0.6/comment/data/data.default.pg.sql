-- Last edited: Antonio J. Garcia 2007-04-24
-- leave subqueries on a single line in order that table prefixes works
BEGIN;
INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'comment', 'Comments', 'Allows users to associate comments with any module in the system.', '', '48/module_block.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr_cmd_edit, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr_cmd_update, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr_cmd_changeStatus, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr_cmd_delete, NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr_cmd_list, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr_cmd_reportHam, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr_cmd_reportSpam, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr_cmd_testAkismetAPIKey, NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentmgr, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentmgr_cmd_insert, NULL, (
    SELECT max(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentsearchmgr, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentsearchmgr_cmd_delete, NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentsearchmgr_cmd_search, NULL, (
    SELECT max(module_id) FROM module
    ));
COMMIT;
