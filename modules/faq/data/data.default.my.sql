INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'faq', 'FAQs', 'Use the ''FAQ'' module to easily create a list of Frequently Asked Questions with corresponding answers for your site.', 'faq/faq', 'faqs.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_reorder', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'faqmgr_reorderUpdate', '', @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'faqmgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);