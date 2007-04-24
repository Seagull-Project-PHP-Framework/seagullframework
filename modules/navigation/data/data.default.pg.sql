-- Last edited: Antonio J. Garcia 2007-04-24
-- Data dump for /navigation
-- leave subqueries on a single line in order that table prefixes works

BEGIN;


INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'navigation', 'Navigation', 'The ''Navigation'' module is what you use to build your site navigation, it creates menus that you can customise in terms of look and feel, and allows you to link to any site resource.', 'navigation/page', 'navigation.png', 'Andrey Podshivalov', NULL, 'BSD', 'beta');

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr_cmd_changeStyle', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_add', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_insert', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_edit', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_update', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_delete', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_reorder', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));

--
-- Dumping data for table section
--

INSERT INTO section VALUES (1, 'root', 'uriEmpty:', '1', 1, 0, 0, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO section VALUES (2, 'User menu', 'uriEmpty:', '-2', 2, 0, 2, 1, 2, 1, 1, 1, 0, '', '');
INSERT INTO section VALUES (4, 'Admin menu', 'uriEmpty:', '1', 4, 0, 4, 1, 2, 2, 1, 1, 0, '', '');

COMMIT;

