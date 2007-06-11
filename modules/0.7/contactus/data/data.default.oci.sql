--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'contactus', 'Contact Us', 'The ''Contact Us'' module can be used to present a form to your users allowing them to contact the site administrators.', NULL, 'contactus.png', '', NULL, NULL, NULL);

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr_cmd_send', 'Permission to submit contact info', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contactusmgr_cmd_list', 'Permission to view Contact Us screen', (SELECT MAX(module_id) FROM module));

--
-- Dumping data for table permission_id
--
-- guest roles perms
-- INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'contactusmgr_list'));
-- INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'contactusmgr_send'));

-- member roles perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'contactusmgr_cmd_list'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'contactusmgr_cmd_send'));