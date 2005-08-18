-- ==============================================================
--  DBMS name:      PostgreSQL 7.3                               
--  Created on:     2004-04-13 23:45:57                          
-- ==============================================================

-- Constraints for /block


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

alter table block_assignment add constraint FK_block_assignment_block foreign key (block_id)
      references block (block_id) on delete restrict on update restrict;

alter table block_assignment add constraint FK_block_assignment_section foreign key (section_id)
      references section (section_id) on delete restrict on update restrict;

COMMIT;







