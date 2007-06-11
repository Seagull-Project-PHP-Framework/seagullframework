-- Last edited: Antonio J. Garcia 2007-04-24
-- Constraints for /block
-- leave "references" on a single line in order that table prefixes works

BEGIN;

alter table block_assignment add constraint FK_block_assignment_block foreign key (block_id)
      references block (block_id) on delete restrict on update restrict;

-- alter table block_assignment add constraint FK_block_assignment_section foreign key (section_id)
--       references section (section_id) on delete restrict on update restrict;

COMMIT;







