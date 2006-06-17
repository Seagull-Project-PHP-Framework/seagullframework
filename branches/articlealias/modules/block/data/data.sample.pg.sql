-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Sample data for /block

BEGIN;

#
# Dumping data for table block
#


INSERT INTO block VALUES (4, 'User_Block_Login', 'Login', '', '', 5, 'Right', 1, 0, 'N;');
INSERT INTO block VALUES (5, 'Default_Block_SampleRight1', 'Sample Right Block', '', '', 5, 'Right', 0, 0, 'N;');
INSERT INTO block VALUES (6, 'Publisher_Block_SiteNews', 'Site News', '', '', 4, 'Left', 0, 1, 'N;');
INSERT INTO block VALUES (7, 'Default_Block_Sample1', 'Community', '', '', 7, 'Right', 1, 1, 'N;');
INSERT INTO block VALUES (8, 'Default_Block_Sample2', 'Syndication', '', '', 3, 'Left', 1, 1, 'N;');
INSERT INTO block VALUES (9, 'Default_Block_Calendar', 'Calendar', '', '', 5, 'Left', 0, 1, 'N;');
INSERT INTO block VALUES (10, 'Publisher_Block_RecentHtmlArticles2', 'Recent articles', '', '', 3, 'Right', 0, 1, 'N;');
INSERT INTO block VALUES (11, 'User_Block_OnlineUsers', 'Online', '', '', 6, 'Left', 0, 0, 'N;');
INSERT INTO block VALUES (12, 'Export_Block_SampleRss', 'Latest Seagull News', '', '', 2, 'Left', 1, 1, 'N;');
INSERT INTO block VALUES (13, 'Publisher_Block_Html', 'Seagull Gear', '', '', 6, 'Right', 1, 0, 'a:1:{s:4:"html";s:219:"<a href="http://www.cafepress.com/seagullsystems" title="Buy Seagull Gear"><img src="http://seagullfiles.phpkitchen.com/images/seagull_gear.png" alt="Buy Seagull gear and support the project" title="Seagull Gear" /></a>";}');
INSERT INTO block VALUES (14, 'Publisher_Block_Html', 'Donate', '', '', 1, 'Left', 1, 0, 'a:1:{s:4:"html";s:252:"<div class="alignCenter">\r\n<a href="http://sf.net/donate/index.php?group_id=92482"><img src="http://seagullfiles.phpkitchen.com/images/project-support.jpg" border="0" alt="Support The Seagull PHP Framework Project" width="88" height="32" /></a>\r\n</div>";}');

--
-- Dumping data for table block_assignment
--

INSERT INTO block_assignment VALUES (4, 0);
INSERT INTO block_assignment VALUES (5, 0);
INSERT INTO block_assignment VALUES (6, 0);
INSERT INTO block_assignment VALUES (7, 0);
INSERT INTO block_assignment VALUES (8, 0);
INSERT INTO block_assignment VALUES (9, 6);
INSERT INTO block_assignment VALUES (10, 6);
INSERT INTO block_assignment VALUES (11, 6);
INSERT INTO block_assignment VALUES (12, 0);
INSERT INTO block_assignment VALUES (13, 0);
INSERT INTO block_assignment VALUES (14, 0);


--
-- Dumping data for table block_role
--

INSERT INTO block_role VALUES (4, -2);
INSERT INTO block_role VALUES (5, -2);
INSERT INTO block_role VALUES (6, -2);
INSERT INTO block_role VALUES (7, -2);
INSERT INTO block_role VALUES (8, -2);
INSERT INTO block_role VALUES (9, -2);
INSERT INTO block_role VALUES (10, -2);
INSERT INTO block_role VALUES (11, -2);
INSERT INTO block_role VALUES (12, -2);
INSERT INTO block_role VALUES (13, -2);
INSERT INTO block_role VALUES (14, -2);

COMMIT;

