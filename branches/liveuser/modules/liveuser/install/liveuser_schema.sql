-- phpMyAdmin SQL Dump
-- version 2.6.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 02, 2005 at 06:31 PM
-- Server version: 4.0.25
-- PHP Version: 4.3.11
-- 
-- Database: `liveuser_old`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_applications`
-- 

CREATE TABLE `liveuser_applications` (
  `application_id` int(11) unsigned NOT NULL default '0',
  `application_define_name` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`application_id`),
  UNIQUE KEY `application_define_name` (`application_define_name`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_area_admin_areas`
-- 

CREATE TABLE `liveuser_area_admin_areas` (
  `area_id` int(11) unsigned NOT NULL default '0',
  `perm_user_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`area_id`,`perm_user_id`),
  KEY `perm_user_id` (`perm_user_id`),
  KEY `area_id` (`area_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_areas`
-- 

CREATE TABLE `liveuser_areas` (
  `area_id` int(11) unsigned NOT NULL default '0',
  `application_id` int(11) unsigned NOT NULL default '0',
  `area_define_name` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`area_id`),
  UNIQUE KEY `area_define_name` (`application_id`,`area_define_name`),
  KEY `areas_application_id` (`application_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_group_subgroups`
-- 

CREATE TABLE `liveuser_group_subgroups` (
  `group_id` int(11) unsigned NOT NULL default '0',
  `subgroup_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`subgroup_id`),
  KEY `subgroup_id` (`subgroup_id`),
  KEY `group_id` (`group_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_grouprights`
-- 

CREATE TABLE `liveuser_grouprights` (
  `group_id` int(11) unsigned NOT NULL default '0',
  `right_id` int(11) unsigned NOT NULL default '0',
  `right_level` tinyint(3) unsigned default '3',
  PRIMARY KEY  (`group_id`,`right_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_groups`
-- 

CREATE TABLE `liveuser_groups` (
  `group_id` int(11) unsigned NOT NULL default '0',
  `group_type` int(11) unsigned default '1',
  `group_define_name` varchar(32) default NULL,
  `owner_user_id` int(11) unsigned default NULL,
  `owner_group_id` int(11) unsigned default NULL,
  `is_active` char(1) NOT NULL default 'N',
  `name` varchar(50) default NULL,
  `description` varchar(250) default NULL,
  PRIMARY KEY  (`group_id`),
  UNIQUE KEY `group_define_name` (`group_define_name`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_groupusers`
-- 

CREATE TABLE `liveuser_groupusers` (
  `perm_user_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`perm_user_id`),
  KEY `perm_user_id` (`perm_user_id`),
  KEY `group_id` (`group_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_perm_users`
-- 

CREATE TABLE `liveuser_perm_users` (
  `perm_user_id` int(11) unsigned NOT NULL default '0',
  `auth_user_id` varchar(32) NOT NULL default '0',
  `perm_type` tinyint(3) unsigned default NULL,
  `auth_container_name` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`perm_user_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_right_implied`
-- 

CREATE TABLE `liveuser_right_implied` (
  `right_id` int(11) unsigned NOT NULL default '0',
  `implied_right_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`right_id`,`implied_right_id`),
  KEY `right_id` (`right_id`),
  KEY `implied_right_id` (`implied_right_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_rights`
-- 

CREATE TABLE `liveuser_rights` (
  `right_id` int(11) unsigned NOT NULL default '0',
  `area_id` int(11) unsigned NOT NULL default '0',
  `right_define_name` varchar(32) NOT NULL default '',
  `has_implied` char(1) NOT NULL default 'N',
  `has_level` char(1) NOT NULL default 'N',
  `name` varchar(100) default NULL,
  `description` varchar(250) default NULL,
  PRIMARY KEY  (`right_id`),
  UNIQUE KEY `right_define_name` (`area_id`,`right_define_name`),
  KEY `rights_area_id` (`area_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_translations`
-- 

CREATE TABLE `liveuser_translations` (
  `section_id` int(11) unsigned NOT NULL default '0',
  `section_type` tinyint(3) unsigned NOT NULL default '0',
  `language_id` char(2) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`section_id`,`section_type`,`language_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_userrights`
-- 

CREATE TABLE `liveuser_userrights` (
  `perm_user_id` int(11) unsigned NOT NULL default '0',
  `right_id` int(11) unsigned NOT NULL default '0',
  `right_level` tinyint(3) default '3',
  PRIMARY KEY  (`right_id`,`perm_user_id`),
  KEY `perm_user_id` (`perm_user_id`),
  KEY `right_id` (`right_id`)
) TYPE=InnoDB;

-- --------------------------------------------------------

-- 
-- Table structure for table `liveuser_users`
-- 

CREATE TABLE `liveuser_users` (
  `auth_user_id` varchar(32) NOT NULL default '0',
  `handle` varchar(32) NOT NULL default '',
  `passwd` varchar(32) NOT NULL default '',
  `lastlogin` datetime default NULL,
  `owner_user_id` int(11) unsigned default NULL,
  `owner_group_id` int(11) unsigned default NULL,
  `is_active` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`auth_user_id`),
  UNIQUE KEY `handle` (`handle`)
) TYPE=MyISAM;