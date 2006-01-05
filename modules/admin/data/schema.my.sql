/*==============================================================*/
/* Table: admin_menu                                                */
/*==============================================================*/
CREATE TABLE IF NOT EXISTS admin_menu (
    section_id int(11) NOT NULL default '0',
    title varchar(45) default NULL,
    resource_uri varchar(255) default NULL,
    perms varchar(45) default NULL,
    parent_id int(11) default NULL,
    root_id int(11) default NULL,
    left_id int(11) default NULL,
    right_id int(11) default NULL,
    level_id int(11) default NULL,
    order_id int(11) default NULL,
    is_enabled smallint(1) unsigned NOT NULL default '1',
    has_link smallint(1) unsigned NOT NULL default '1',
    PRIMARY KEY  (section_id),
    key AK_key_root_id (root_id),
    key AK_key_order_id (order_id),
    key AK_key_left_id (left_id),
    key AK_key_right_id (right_id),
    key AK_id_root_l_r (section_id, root_id, left_id, right_id),
    key AK_key_level_id (level_id)
);

