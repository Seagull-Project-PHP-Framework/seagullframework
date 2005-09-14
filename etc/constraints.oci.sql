-- ==============================================================
--  create foreign keys
-- ==============================================================
alter table item add constraint FK_item_item_type foreign key (item_type_id) references item_type (item_type_id);
alter table item_addition add constraint FK_item_item_addition foreign key (item_id) references item (item_id) on delete cascade;
alter table item_addition add constraint FK_item_type_mapping_item_add foreign key (item_type_mapping_id) references item_type_mapping (item_type_mapping_id);
alter table item_type_mapping add constraint FK_item_type_item_type_mapping foreign key (item_type_id) references item_type (item_type_id) on delete cascade;
alter table item add constraint FK_category_item foreign key (category_id) references category (category_id);
alter table item add foreign key (created_by_id) references usr (usr_id) on delete set null;
alter table item add foreign key (updated_by_id) references usr (usr_id) on delete set null;
