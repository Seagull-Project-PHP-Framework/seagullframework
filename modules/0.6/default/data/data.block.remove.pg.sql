-- Last edited: Antonio J. Garcia 2007-04-24
-- leave subqueries on a single line in order that table prefixes works
-- delete assignments
DELETE FROM block_assignment WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Default_Block_LangSwitcher2'
    );

-- delete role assignments
DELETE FROM block_role WHERE block_id =(
    SELECT block_id FROM block WHERE name = 'Default_Block_LangSwitcher2'
    );

-- delete blocks
DELETE FROM block WHERE block_id = (
    SELECT block_id FROM block WHERE name = 'Default_Block_LangSwitcher2'
    );
