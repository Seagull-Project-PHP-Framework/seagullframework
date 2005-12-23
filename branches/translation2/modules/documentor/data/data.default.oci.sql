--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'documentor', 'Documentor', '''Documentor'' is a module that lets you quickly and easily create documentation in html format based on articles you submit in the ''Publisher'' module.', NULL, 'documentor.png');

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentormgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentormgr_list', '', (SELECT MAX(module_id) FROM module));