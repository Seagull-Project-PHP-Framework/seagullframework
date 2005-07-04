alter table category add constraint FK_parent foreign key (parent_id)
      references category (category_id) on delete restrict on update restrict;
      
alter table block_assignment add constraint FK_block_assignment foreign key (section_id)
      references section (section_id) on delete restrict on update restrict;