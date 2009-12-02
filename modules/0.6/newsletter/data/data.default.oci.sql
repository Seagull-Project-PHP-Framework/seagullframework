--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'newsletter', 'Newsletter', 'The ''Newsletter'' module is a simple mass mailer module that allows you to create an HTML formatted message or newsletter, and send it to all your registered users, or on a group by group basis, in a single click.', 'newsletter/list', 'newsletter.png', '', NULL, NULL, NULL);

--
-- Dumping data for table module
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_list', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_send', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_addressBook', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_listSubscribers', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_editSubscriber', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_updateSubscriber', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_deleteSubscriber', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_listLists', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_addList', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_editList', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_updateList', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_deleteLists', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_exportSubscribers', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_addSubscriber', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_insertSubscriber', NULL, (SELECT MAX(module_id) FROM module));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr_cmd_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr_cmd_subscribe', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr_cmd_unsubscribe', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr_cmd_authorize', NULL, (SELECT MAX(module_id) FROM module));

--
-- Dumping data for table newsletter
--

INSERT INTO newsletter VALUES (1,'general','To stay informed you may join our general discussion list.','',9,'','','2005-02-28 19:44:32','2005-02-28 19:44:32');

--
-- Creating sequences
-- sequence must start on the first free record id
--

-- CREATE SEQUENCE newsletter_seq START WITH 2;