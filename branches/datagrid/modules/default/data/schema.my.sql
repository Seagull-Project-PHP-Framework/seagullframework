/*==============================================================*/
/* Table: module                                                 */
/*==============================================================*/
create table if not exists module (
  module_id int(11) not null,
  is_configurable smallint(1),
  name varchar(255),
  title varchar(255),
  description text,
  admin_uri varchar(255),
  icon varchar(255),
  primary key  (module_id)
);
