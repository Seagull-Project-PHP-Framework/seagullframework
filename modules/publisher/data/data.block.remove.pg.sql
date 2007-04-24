-- Last edited: Antonio J. Garcia 2007-04-24
-- Setup blocks for publisher
-- leave subqueries on a single line in order that table prefixes works

BEGIN;

-- get block IDs
-- SELECT @catBlockId := block_id FROM block WHERE name = 'Navigation_Block_CategoryNav';
-- SELECT @blockIdSiteNews := block_id FROM block WHERE name = 'Publisher_Block_SiteNews';
-- SELECT @blockIdRecentHtmlArticles2 := block_id FROM block WHERE name = 'Publisher_Block_RecentHtmlArticles2';
-- SELECT @blockIdSeagullGear := block_id FROM block WHERE title = 'Seagull Gear';
-- SELECT @blockIdDonate := block_id FROM block WHERE title = 'Donate';

-- delete role assignments
DELETE FROM block_role WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Navigation_Block_CategoryNav'
    );
DELETE FROM block_role WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Publisher_Block_SiteNews'
    );
DELETE FROM block_role WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Publisher_Block_RecentHtmlArticles2'
    );
DELETE FROM block_role WHERE block_id = (
    SELECT block_id FROM block WHERE title = 'Seagull Gear'
    );
DELETE FROM block_role WHERE block_id = (
    SELECT block_id FROM block WHERE title = 'Donate'
    );

-- delete assignments
DELETE FROM block_assignment WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Navigation_Block_CategoryNav'
    );
DELETE FROM block_assignment WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Publisher_Block_SiteNews'
    );
DELETE FROM block_assignment WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Publisher_Block_RecentHtmlArticles2'
    );
DELETE FROM block_assignment WHERE block_id = (
    SELECT block_id FROM block WHERE title = 'Seagull Gear'
    );
DELETE FROM block_assignment WHERE block_id = (
    SELECT block_id FROM block WHERE title = 'Donate'
    );

-- delete blocks
DELETE FROM block WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Navigation_Block_CategoryNav'
    );
DELETE FROM block WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Publisher_Block_SiteNews'
    );
DELETE FROM block WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Publisher_Block_RecentHtmlArticles2'
    );
DELETE FROM block WHERE block_id = (
    SELECT block_id FROM block WHERE title = 'Seagull Gear'
    );
DELETE FROM block WHERE block_id = (
    SELECT block_id FROM block WHERE title = 'Donate'
    );

COMMIT;

