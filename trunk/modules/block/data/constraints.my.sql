alter table block_assignment add constraint FK_block_assignment foreign key (block_id)
      references block (block_id) on delete restrict on update restrict;
