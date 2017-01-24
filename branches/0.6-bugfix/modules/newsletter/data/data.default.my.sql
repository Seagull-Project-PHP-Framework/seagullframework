INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'newsletter', 'Newsletter', 'The ''Newsletter'' module is a simple mass mailer module that allows you to create an HTML formatted message or newsletter, and send it to all your registered users, or on a group by group basis, in a single click.', 'newsletter/list', 'newsletter.png', 'Rares Benea', NULL, 'BSD', 'beta');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_list', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_send', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_addressBook', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_listSubscribers', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_editSubscriber', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_updateSubscriber', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_deleteSubscriber', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_listLists', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_addList', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_editList', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_updateList', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_deleteLists', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_exportSubscribers', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_addSubscriber', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'listmgr_cmd_insertSubscriber', NULL, @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr_cmd_subscribe', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr_cmd_unsubscribe', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'newslettermgr_cmd_authorize', NULL, @moduleId);

INSERT INTO newsletter VALUES (1,'general','To stay informed you may join our general discussion list.','',9,'','','2005-02-28 19:44:32','2005-02-28 19:44:32');
