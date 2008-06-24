-- this was incorrectly called configmgr_insert before
UPDATE permission SET name = 'configmgr_update' WHERE permission_id = 9;

-- adding some new permission descriptions
UPDATE permission SET description = 'Permission to use block manager' WHERE name = 'blockmgr';
  
UPDATE permission SET description = 'Permission to view block listing' WHERE name = 'blockmgr_list';
  
UPDATE permission SET description = 'Permission to reorder blocks' WHERE name = 'blockmgr_reorder';
  
UPDATE permission SET description = 'Permission to remove block' WHERE name = 'blockmgr_delete';

UPDATE permission SET description = 'Permission to edit existing block' WHERE name = 'blockmgr_edit';

UPDATE permission SET description = 'Permission to add new block' WHERE name = 'blockmgr_add';

UPDATE permission SET description = 'Permission to submit contact info' WHERE name = 'contactusmgr_send';
  
UPDATE permission SET description = 'Permission to view Contact Us screen' WHERE name = 'contactusmgr_list';

UPDATE permission SET description = 'Permission to view and edit config settings' WHERE name = 'configmgr_edit';
  
UPDATE permission SET description = 'Permission to update config values' WHERE name = 'configmgr_update';  