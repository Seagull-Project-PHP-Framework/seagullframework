INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'faq', 'FAQs', 'Use the ''FAQ'' module to easily create a list of Frequently Asked Questions with corresponding answers for your site.', 'faq/faq', 'faqs.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr_cmd_reorder', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr_cmd_reorderUpdate', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminfaqmgr_cmd_list', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_cmd_reorder', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_cmd_reorderUpdate', '', @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'faqmgr_cmd_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);