-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Constraints for /block

BEGIN;

alter table block_assignment add constraint FK_block_assignment_block foreign key (block_id)
      references block (block_id) on delete restrict on update restrict;

-- alter table block_assignment add constraint FK_block_assignment_section foreign key (section_id)
--       references section (section_id) on delete restrict on update restrict;

COMMIT;







