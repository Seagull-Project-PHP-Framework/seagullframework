-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Sample data for /block

-- 
--  Dumping data for table block
-- 

INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Default_Block_SampleRight1', 'Sample Right Block', '', '', 5, 'Right', 0, 0, 'N;');
INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Default_Block_Calendar', 'Calendar', '', '', 5, 'Left', 0, 1, 'N;');
INSERT INTO block VALUES ({SGL_NEXT_ID}, 'User_Block_OnlineUsers', 'Online', '', '', 6, 'Left', 0, 0, 'N;')

--
-- Dumping data for table block_assignment
--

INSERT INTO block_assignment VALUES ((SELECT block_id FROM block WHERE name = 'Default_Block_SampleRight1'), 0);
INSERT INTO block_assignment VALUES ((SELECT block_id FROM block WHERE name = 'Default_Block_Calendar'), 0);
INSERT INTO block_assignment VALUES ((SELECT block_id FROM block WHERE name = 'User_Block_OnlineUsers'), 0);

--
-- Dumping data for table block_role
--

INSERT INTO block_role VALUES ((SELECT block_id FROM block WHERE name = 'Default_Block_SampleRight1'), -2);
INSERT INTO block_role VALUES ((SELECT block_id FROM block WHERE name = 'Default_Block_Calendar'), -2);
INSERT INTO block_role VALUES ((SELECT block_id FROM block WHERE name = 'User_Block_OnlineUsers'), -2);




