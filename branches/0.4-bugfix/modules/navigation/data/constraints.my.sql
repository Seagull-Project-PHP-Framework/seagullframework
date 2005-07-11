alter table category add constraint FK_parent foreign key (parent_id)
      references category (category_id) on delete restrict on update restrict;