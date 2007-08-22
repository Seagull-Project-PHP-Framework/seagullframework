-- Last edited: Antonio J. Garcia 2007-04-24
-- Data dump for /randommsg
-- leave subqueries on a single line in order that table prefixes works

BEGIN;


INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'randommsg', 'Random Messages', 'Allows you to create a list of messages and display them randomly (fortune).', 'randommsg/rndmsg', 'rndmsg.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_cmd_add', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_cmd_insert', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_cmd_delete', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));

COMMIT;

