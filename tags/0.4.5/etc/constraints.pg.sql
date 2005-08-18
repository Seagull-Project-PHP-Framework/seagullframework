-- ==============================================================
--  DBMS name:      PostgreSQL 7.3                               
--  Created on:     2004-04-13 23:45:57                          
-- ==============================================================

-- Constraints for /etc


-- Begin a transaction-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

alter table item add constraint FK_item_item_type foreign key (item_type_id)
      references item_type (item_type_id) on delete restrict on update restrict;

alter table item_addition add constraint FK_item_item_addition foreign key (item_id)
      references item (item_id) on delete restrict on update restrict;

alter table item_addition add constraint FK_item_type_mapping_item_addition foreign key (item_type_mapping_id)
      references item_type_mapping (item_type_mapping_id) on delete restrict on update restrict;

alter table item_type_mapping add constraint FK_item_type_item_type_mapping foreign key (item_type_id)
      references item_type (item_type_id) on delete restrict on update restrict;

COMMIT;

