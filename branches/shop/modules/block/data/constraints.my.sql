alter table block_assignment add constraint FK_block_assignment foreign key (block_id)
      references block (block_id) on delete restrict on update restrict;
      
# alter table block_assignment add constraint FK_block_assignment foreign key (section_id)
#      references section (section_id) on delete restrict on update restrict;
