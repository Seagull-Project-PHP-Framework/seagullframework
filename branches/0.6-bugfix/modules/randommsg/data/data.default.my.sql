INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'randommsg', 'Random Messages', 'Allows you to create a list of messages and display them randomly (fortune).', 'randommsg/rndmsg', 'rndmsg.png', 'Michael Willemot', NULL, 'BSD', 'beta');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_cmd_list', '', @moduleId);