-- Last edited: Antonio J. Garcia 2007-04-24
-- block data for block
-- leave subqueries on a single line in order that table prefixes works

BEGIN;

INSERT INTO block VALUES ({SGL_NEXT_ID}, 'User_Block_Login2', 'Login', '', '', 5, 'Right', 1, 0, 'a:2:{s:13:"loginTemplate";s:15:"blockLogin.html";s:14:"logoutTemplate";s:16:"blockLogout.html";}');
INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Default_Block_Sample1', 'Community', '', '', 7, 'Left', 1, 1, 'N;');
INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Default_Block_Sample2', 'Syndication', '', '', 3, 'Left', 1, 1, 'N;');
-- INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Export_Block_ShowRss', 'Latest Seagull News', '', '', 2, 'Left', 1, 1, 'a:2:{s:9:"rssSource";s:37:"http://seagullproject.org/export/rss/";s:11:"itemsToShow";s:1:"5";}');

-- SELECT @blockIdLogin := block_id FROM block WHERE name = 'User_Block_Login2';
-- SELECT @blockIdSample1 := block_id FROM block WHERE name = 'Default_Block_Sample1';
-- SELECT @blockIdSample2 := block_id FROM block WHERE name = 'Default_Block_Sample2';
-- SELECT @blockIdSampleRss := block_id FROM block WHERE name = 'Export_Block_ShowRss';

INSERT INTO block_assignment VALUES ((
    SELECT block_id FROM block WHERE name = 'User_Block_Login2'
    ), 0);
INSERT INTO block_assignment VALUES ((
    SELECT block_id FROM block WHERE name = 'Default_Block_Sample1'
    ), 0);
INSERT INTO block_assignment VALUES ((
    SELECT block_id FROM block WHERE name = 'Default_Block_Sample2'
    ), 0);
-- INSERT INTO block_assignment VALUES (@blockIdSampleRss, 0);

INSERT INTO block_role VALUES ((
    SELECT block_id FROM block WHERE name = 'User_Block_Login2'
    ), -2);
INSERT INTO block_role VALUES ((
    SELECT block_id FROM block WHERE name = 'Default_Block_Sample1'
    ), -2);
INSERT INTO block_role VALUES ((
    SELECT block_id FROM block WHERE name = 'Default_Block_Sample2'
    ), -2);
-- INSERT INTO block_role VALUES (@blockIdSampleRss, -2);

COMMIT;

