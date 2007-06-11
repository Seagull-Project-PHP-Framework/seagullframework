-- Last edited: Antonio J. Garcia 2007-04-24
-- leave subqueries on a single line in order that table prefixes works
BEGIN;
-- LangSwitcher2
INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Default_Block_LangSwitcher2', 'Language switcher', NULL, NULL, 1, 'BodyTop', 1, 1,'N;');

-- create block assignments
INSERT INTO block_assignment VALUES ((
    SELECT block_id FROM block WHERE name = 'Default_Block_LangSwitcher2'
    ), 0);
-- create block role assignments
INSERT INTO block_role VALUES ((
    SELECT block_id FROM block WHERE name = 'Default_Block_LangSwitcher2'
    ), -2);
COMMIT;
