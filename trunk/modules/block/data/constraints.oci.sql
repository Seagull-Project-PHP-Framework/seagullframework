-- ==============================================================
--  create foreign keys
-- ==============================================================
alter table block_assignment add constraint FK_block_assignment_block foreign key (block_id) references block (block_id);
alter table block_assignment add constraint FK_block_assignment_section foreign key (section_id) references section (section_id);
