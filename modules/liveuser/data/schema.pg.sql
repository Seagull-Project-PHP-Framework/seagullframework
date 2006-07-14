BEGIN;

--
--  `liveuser_applications`
--

create table liveuser_applications (
  application_id integer NOT NULL default '0',
  application_define_name varchar(32) NOT NULL default '',
  PRIMARY KEY  (application_id),
  UNIQUE (application_define_name)
);
create sequence liveuser_applications_seq;
-- --------------------------------------------------------

--
--  `liveuser_area_admin_areas`
--

create table liveuser_area_admin_areas (
  area_id integer NOT NULL default '0',
  perm_user_id integer NOT NULL default '0',
  PRIMARY KEY (area_id,perm_user_id)
);
create index perm_user_id on liveuser_area_admin_areas (perm_user_id);
create index area_id on liveuser_area_admin_areas (area_id);
create sequence liveuser_area_admin_areas_seq;
-- --------------------------------------------------------

--
--  `liveuser_areas`
--

create table liveuser_areas (
  area_id integer NOT NULL default '0',
  application_id integer NOT NULL default '0',
  area_define_name varchar(32) NOT NULL default '',
  PRIMARY KEY (area_id),
  UNIQUE (application_id,area_define_name)
);
create index areas_application_id on liveuser_areas (application_id);
create sequence liveuser_areas_seq;
-- --------------------------------------------------------

--
--  `liveuser_group_subgroups`
--

create table liveuser_group_subgroups (
  group_id integer NOT NULL default '0',
  subgroup_id integer NOT NULL default '0',
  PRIMARY KEY  (group_id,subgroup_id)
);
create index group_id on liveuser_group_subgroups (group_id);
create index subgroup_id on liveuser_group_subgroups (subgroup_id);
create sequence liveuser_group_subgroups_seq;
-- --------------------------------------------------------

--
--  `liveuser_grouprights`
--

create table liveuser_grouprights (
  group_id integer NOT NULL default '0',
  right_id integer NOT NULL default '0',
  right_level int2 default '3',
  PRIMARY KEY  (group_id,right_id)
);
create sequence liveuser_grouprights_seq;
-- --------------------------------------------------------

--
--  `liveuser_groups`
--

create table liveuser_groups (
  group_id integer NOT NULL default '0',
  group_type integer default '1',
  group_define_name varchar(32) default NULL,
  PRIMARY KEY  (group_id),
  UNIQUE (group_define_name)
);
create sequence liveuser_groups_seq;

-- --------------------------------------------------------

--
--  `liveuser_groupusers`
--

-- perm_user_id already exists; use liveuser_groupusers_perm_user_id instead
-- group_id already exists; use liveuser_groupusers_group_id instead
create table liveuser_groupusers (
  perm_user_id integer NOT NULL default '0',
  group_id integer NOT NULL default '0',
  PRIMARY KEY  (group_id,perm_user_id)
);
create index liveuser_groupusers_group_id on liveuser_groupusers (group_id);
create index liveuser_groupusers_perm_user_id on liveuser_groupusers (perm_user_id);
create sequence liveuser_groupusers_seq;
-- --------------------------------------------------------

--
--  `liveuser_perm_users`
--

create table liveuser_perm_users (
  perm_user_id integer NOT NULL default '0',
  auth_user_id varchar(32) NOT NULL default '0',
  perm_type int2 default NULL,
  auth_container_name varchar(32) NOT NULL default '',
  PRIMARY KEY  (perm_user_id)
);
create sequence liveuser_perm_users_seq;
-- --------------------------------------------------------

--
--  `liveuser_right_implied`
--

create table liveuser_right_implied (
  right_id integer NOT NULL default '0',
  implied_right_id integer NOT NULL default '0',
  PRIMARY KEY  (right_id,implied_right_id)
);
create index implied_right_id on liveuser_right_implied (implied_right_id);
create index right_id on liveuser_right_implied (right_id);
create sequence liveuser_right_implied_seq;
-- --------------------------------------------------------

--
--  `liveuser_rights`
--

create table liveuser_rights (
  right_id integer NOT NULL default '0',
  area_id integer NOT NULL default '0',
  right_define_name varchar(32) NOT NULL default '',
  has_implied char(1) NOT NULL default 'N',
  has_level char(1) NOT NULL default 'N',
  name varchar(100) default NULL,
  description varchar(250) default NULL,
  PRIMARY KEY  (right_id),
  UNIQUE (area_id,right_define_name)
);
create index rights_area_id on liveuser_rights (area_id);
create sequence liveuser_rights_seq;
-- --------------------------------------------------------

--
--  `liveuser_translations`
--

create table liveuser_translations (
  translation_id integer NOT NULL default '0',
  section_id integer NOT NULL default '0',
  section_type int2 NOT NULL default '0',
  language_id varchar(128) NOT NULL default '',
  name varchar(50) NOT NULL default '',
  description varchar(255) default NULL,
  PRIMARY KEY  (section_id,section_type,language_id),
  UNIQUE (translation_id)
);
create index translation_id on liveuser_translations (translation_id);
create sequence liveuser_translations_seq;
-- --------------------------------------------------------

--
--  `liveuser_userrights`
--

-- perm_user_id already exists; use liveuser_userrights_perm_user_id instead
-- right_id already exists; use liveuser_userrights_right_id instead
create table liveuser_userrights (
  perm_user_id integer NOT NULL default '0',
  right_id integer NOT NULL default '0',
  right_level int2 default '3',
  PRIMARY KEY  (right_id,perm_user_id)
);
create index liveuser_userrights_perm_user_id on liveuser_userrights (perm_user_id);
create index liveuser_userrights_right_id on liveuser_userrights (right_id);
create sequence liveuser_userrights_seq;
-- --------------------------------------------------------

--
--  `liveuser_users`
--

create table liveuser_users (
  auth_user_id varchar(32) NOT NULL default '0',
  handle varchar(32) NOT NULL default '',
  passwd varchar(32) NOT NULL default '',
  lastlogin timestamp default NULL,
  owner_user_id integer default NULL,
  owner_group_id integer default NULL,
  is_active char(1) NOT NULL default 'N',
  PRIMARY KEY  (auth_user_id),
  UNIQUE (handle)
);
create sequence liveuser_users_seq;
-- --------------------------------------------------------

--
--  `right_permission`
--

create table right_permission (
  right_permission_id integer NOT NULL default '0',
  right_id integer NOT NULL default '0',
  permission_id integer NOT NULL default '0',
  PRIMARY KEY  (right_permission_id)
);
create sequence right_permission_seq;

COMMIT;