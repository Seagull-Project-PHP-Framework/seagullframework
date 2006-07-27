#
# Dumping data for table `block`
#

INSERT INTO `block` VALUES ({SGL_NEXT_ID}, 'Default_Block_SampleRight1', 'Sample Right Block', '', '', 5, 'Right', 0, 0, 'N;');
INSERT INTO `block` VALUES ({SGL_NEXT_ID}, 'Default_Block_Calendar', 'Calendar', '', '', 5, 'Left', 0, 1, 'N;');
INSERT INTO `block` VALUES ({SGL_NEXT_ID}, 'User_Block_OnlineUsers', 'Online', '', '', 6, 'Left', 0, 0, 'N;');


SELECT @blockIdSampleRight1 := block_id FROM block WHERE name = 'Default_Block_SampleRight1';
SELECT @blockIdCalendar := block_id FROM block WHERE name = 'Default_Block_Calendar';
SELECT @blockIdOnlineUsers := block_id FROM block WHERE name = 'User_Block_OnlineUsers';

--
-- Dumping data for table `block_assignment`
--

INSERT INTO `block_assignment` VALUES (@blockIdSampleRight1, 0);
INSERT INTO `block_assignment` VALUES (@blockIdCalendar, 0);
INSERT INTO `block_assignment` VALUES (@blockIdOnlineUsers, 0);

--
-- Dumping data for table `block_role`
--

INSERT INTO `block_role` VALUES (@blockIdSampleRight1, -2);
INSERT INTO `block_role` VALUES (@blockIdCalendar, -2);
INSERT INTO `block_role` VALUES (@blockIdOnlineUsers, -2);