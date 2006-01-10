--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'randommsg', 'Random Messages', 'Allows you to create a list of messages and display them randomly (fortune).', 'randommsg/rndmsg', 'rndmsg.png');

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rndmsgmgr_list', '', (SELECT MAX(module_id) FROM module));