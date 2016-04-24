-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2016 at 05:46 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: 'version-control2'
--

-- --------------------------------------------------------

--
-- Table structure for table 'acl_classes'
--

CREATE TABLE IF NOT EXISTS acl_classes (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  class_type varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY UNIQ_69DD750638A36066 (class_type)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'acl_entries'
--

CREATE TABLE IF NOT EXISTS acl_entries (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  class_id int(10) unsigned NOT NULL,
  object_identity_id int(10) unsigned DEFAULT NULL,
  security_identity_id int(10) unsigned NOT NULL,
  field_name varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  ace_order smallint(5) unsigned NOT NULL,
  mask int(11) NOT NULL,
  granting tinyint(1) NOT NULL,
  granting_strategy varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  audit_success tinyint(1) NOT NULL,
  audit_failure tinyint(1) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4 (class_id,object_identity_id,field_name,ace_order),
  KEY IDX_46C8B806EA000B103D9AB4A6DF9183C9 (class_id,object_identity_id,security_identity_id),
  KEY IDX_46C8B806EA000B10 (class_id),
  KEY IDX_46C8B8063D9AB4A6 (object_identity_id),
  KEY IDX_46C8B806DF9183C9 (security_identity_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'acl_object_identities'
--

CREATE TABLE IF NOT EXISTS acl_object_identities (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  parent_object_identity_id int(10) unsigned DEFAULT NULL,
  class_id int(10) unsigned NOT NULL,
  object_identifier varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  entries_inheriting tinyint(1) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY UNIQ_9407E5494B12AD6EA000B10 (object_identifier,class_id),
  KEY IDX_9407E54977FA751A (parent_object_identity_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'acl_object_identity_ancestors'
--

CREATE TABLE IF NOT EXISTS acl_object_identity_ancestors (
  object_identity_id int(10) unsigned NOT NULL,
  ancestor_id int(10) unsigned NOT NULL,
  PRIMARY KEY (object_identity_id,ancestor_id),
  KEY IDX_825DE2993D9AB4A6 (object_identity_id),
  KEY IDX_825DE299C671CEA1 (ancestor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'acl_security_identities'
--

CREATE TABLE IF NOT EXISTS acl_security_identities (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  identifier varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  username tinyint(1) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY UNIQ_8835EE78772E836AF85E0677 (identifier,username)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table 'issue'
--

CREATE TABLE IF NOT EXISTS issue (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) DEFAULT NULL,
  description longtext,
  `status` varchar(45) DEFAULT NULL,
  closed_at datetime DEFAULT NULL,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  github_number int(11) DEFAULT NULL,
  ver_user_id int(11) NOT NULL,
  project_id int(11) NOT NULL,
  issue_milestone_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_issue_ver_user1_idx (ver_user_id),
  KEY fk_issue_project1_idx (project_id),
  KEY fk_issue_issue_milestone1_idx (issue_milestone_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'issue_comment'
--

CREATE TABLE IF NOT EXISTS issue_comment (
  id int(11) NOT NULL AUTO_INCREMENT,
  `comment` longtext,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  ver_user_id int(11) DEFAULT NULL,
  issue_id int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY fk_issue_comment_ver_user1_idx (ver_user_id),
  KEY fk_issue_comment_issue1_idx (issue_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'issue_event'
--

CREATE TABLE IF NOT EXISTS issue_event (
  id int(11) NOT NULL AUTO_INCREMENT,
  commit_id varchar(255) DEFAULT NULL,
  `event` varchar(80) DEFAULT NULL COMMENT 'closed\nreopened\nreferenced\nmentioned\nassigned\nunassigned\nlabeled\nunlabeled\nmilestoned\ndemilestoned\nrenamed',
  created_at datetime DEFAULT NULL,
  issue_id int(11) NOT NULL,
  issue_milestone_id int(11) NOT NULL,
  issue_label_id int(11) NOT NULL,
  ver_user_id int(11) NOT NULL,
  assignee int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY fk_issue_event_issue1_idx (issue_id),
  KEY fk_issue_event_issue_milestone1_idx (issue_milestone_id),
  KEY fk_issue_event_issue_label1_idx (issue_label_id),
  KEY fk_issue_event_ver_user1_idx (ver_user_id),
  KEY fk_issue_event_ver_user2_idx (assignee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'issue_has_issue_label'
--

CREATE TABLE IF NOT EXISTS issue_has_issue_label (
  issue_id int(11) NOT NULL,
  issue_label_id int(11) NOT NULL,
  PRIMARY KEY (issue_id,issue_label_id),
  KEY fk_issue_has_issue_label_issue_label1_idx (issue_label_id),
  KEY fk_issue_has_issue_label_issue1_idx (issue_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'issue_label'
--

CREATE TABLE IF NOT EXISTS issue_label (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(80) DEFAULT NULL,
  hex_color varchar(80) DEFAULT NULL,
  all_projects tinyint(1) DEFAULT '0',
  project_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_issue_label_project1_idx (project_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'issue_milestone'
--

CREATE TABLE IF NOT EXISTS issue_milestone (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) DEFAULT NULL,
  description longtext,
  state varchar(45) DEFAULT NULL,
  due_on datetime DEFAULT NULL,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  closed_at datetime DEFAULT NULL,
  ver_user_id int(11) NOT NULL,
  project_id int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY fk_issue_milestone_ver_user1_idx (ver_user_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'project'
--

CREATE TABLE IF NOT EXISTS project (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(80) NOT NULL,
  description varchar(225) DEFAULT NULL,
  path varchar(225) DEFAULT NULL,
  ssh tinyint(1) DEFAULT NULL,
  `host` varchar(225) DEFAULT NULL,
  username varchar(225) DEFAULT NULL,
  `password` longtext,
  key_file varchar(225) DEFAULT NULL,
  creator_id int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY fk_project_ver_user_idx (creator_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'project_environment'
--

CREATE TABLE IF NOT EXISTS project_environment (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(80) DEFAULT NULL,
  description varchar(225) DEFAULT NULL,
  path varchar(225) DEFAULT NULL,
  ssh tinyint(1) DEFAULT NULL,
  `host` varchar(225) DEFAULT NULL,
  username varchar(225) DEFAULT NULL,
  `password` longtext,
  key_file varchar(225) DEFAULT NULL,
  project_id int(11) NOT NULL,
  project_environment_file_perm_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_project_environment_project1_idx (project_id),
  KEY fk_project_environment_project_environment_file_perm1_idx (project_environment_file_perm_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'project_environment_file_perm'
--

CREATE TABLE IF NOT EXISTS project_environment_file_perm (
  id int(11) NOT NULL AUTO_INCREMENT,
  file_owner varchar(80) DEFAULT NULL,
  file_group varchar(80) DEFAULT NULL,
  permission_owner_read tinyint(1) DEFAULT NULL,
  permission_owner_write tinyint(1) DEFAULT NULL,
  permission_owner_execute tinyint(1) DEFAULT NULL,
  permission_sticky_uid tinyint(1) DEFAULT NULL,
  permission_group_read tinyint(1) DEFAULT NULL,
  permission_group_write tinyint(1) DEFAULT NULL,
  permission_group_execute tinyint(1) DEFAULT NULL,
  permission_sticky_gid tinyint(1) DEFAULT NULL,
  permission_other_read tinyint(1) DEFAULT NULL,
  permission_other_write tinyint(1) DEFAULT NULL,
  permission_other_execute tinyint(1) DEFAULT NULL,
  permission_sticky_bit tinyint(1) DEFAULT NULL,
  enable_file_permissions tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'project_issue_integrator'
--

CREATE TABLE IF NOT EXISTS project_issue_integrator (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) NOT NULL,
  repo_type varchar(80) DEFAULT NULL,
  class_name varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY fk_project_issue_integrator_project1_idx (project_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'project_issue_integrator_github'
--

CREATE TABLE IF NOT EXISTS project_issue_integrator_github (
  id int(11) NOT NULL AUTO_INCREMENT,
  repo_name varchar(255) DEFAULT NULL,
  owner_name varchar(255) DEFAULT NULL,
  api_token varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'project_issue_integrator_gitlab'
--

CREATE TABLE IF NOT EXISTS project_issue_integrator_gitlab (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_name varchar(255) DEFAULT NULL,
  url varchar(255) DEFAULT NULL,
  api_token varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'user_projects'
--

CREATE TABLE IF NOT EXISTS user_projects (
  id int(11) NOT NULL AUTO_INCREMENT,
  roles varchar(225) DEFAULT NULL,
  project_id int(11) NOT NULL,
  ver_user_id int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY fk_table1_project1_idx (project_id),
  KEY fk_table1_ver_user1_idx (ver_user_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'ver_user'
--

CREATE TABLE IF NOT EXISTS ver_user (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  username_canonical varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  email varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  email_canonical varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  enabled tinyint(1) NOT NULL,
  salt varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  last_login datetime DEFAULT NULL,
  locked tinyint(1) NOT NULL,
  expired tinyint(1) NOT NULL,
  expires_at datetime DEFAULT NULL,
  confirmation_token varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  password_requested_at datetime DEFAULT NULL,
  roles longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  credentials_expired tinyint(1) NOT NULL,
  credentials_expire_at datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  github_id varchar(255) DEFAULT NULL,
  github_access_token varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY UNIQ_86EDD9B992FC23A8 (username_canonical),
  UNIQUE KEY UNIQ_86EDD9B9A0D96FBF (email_canonical)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table acl_entries
--
ALTER TABLE acl_entries
  ADD CONSTRAINT FK_46C8B8063D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT FK_46C8B806DF9183C9 FOREIGN KEY (security_identity_id) REFERENCES acl_security_identities (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT FK_46C8B806EA000B10 FOREIGN KEY (class_id) REFERENCES acl_classes (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table acl_object_identities
--
ALTER TABLE acl_object_identities
  ADD CONSTRAINT FK_9407E54977FA751A FOREIGN KEY (parent_object_identity_id) REFERENCES acl_object_identities (id);

--
-- Constraints for table acl_object_identity_ancestors
--
ALTER TABLE acl_object_identity_ancestors
  ADD CONSTRAINT FK_825DE2993D9AB4A6 FOREIGN KEY (object_identity_id) REFERENCES acl_object_identities (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT FK_825DE299C671CEA1 FOREIGN KEY (ancestor_id) REFERENCES acl_object_identities (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table issue
--
ALTER TABLE issue
  ADD CONSTRAINT fk_issue_issue_milestone1 FOREIGN KEY (issue_milestone_id) REFERENCES issue_milestone (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_issue_project1 FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_issue_ver_user1 FOREIGN KEY (ver_user_id) REFERENCES ver_user (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table issue_comment
--
ALTER TABLE issue_comment
  ADD CONSTRAINT fk_issue_comment_issue1 FOREIGN KEY (issue_id) REFERENCES issue (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_issue_comment_ver_user1 FOREIGN KEY (ver_user_id) REFERENCES ver_user (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table issue_event
--
ALTER TABLE issue_event
  ADD CONSTRAINT fk_issue_event_issue1 FOREIGN KEY (issue_id) REFERENCES issue (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_issue_event_issue_label1 FOREIGN KEY (issue_label_id) REFERENCES issue_label (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_issue_event_issue_milestone1 FOREIGN KEY (issue_milestone_id) REFERENCES issue_milestone (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_issue_event_ver_user1 FOREIGN KEY (ver_user_id) REFERENCES ver_user (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_issue_event_ver_user2 FOREIGN KEY (assignee) REFERENCES ver_user (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table issue_has_issue_label
--
ALTER TABLE issue_has_issue_label
  ADD CONSTRAINT fk_issue_has_issue_label_issue1 FOREIGN KEY (issue_id) REFERENCES issue (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_issue_has_issue_label_issue_label1 FOREIGN KEY (issue_label_id) REFERENCES issue_label (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table issue_label
--
ALTER TABLE issue_label
  ADD CONSTRAINT fk_issue_label_project1 FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table issue_milestone
--
ALTER TABLE issue_milestone
  ADD CONSTRAINT fk_issue_milestone_ver_user1 FOREIGN KEY (ver_user_id) REFERENCES ver_user (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table project
--
ALTER TABLE project
  ADD CONSTRAINT fk_project_ver_user FOREIGN KEY (creator_id) REFERENCES ver_user (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table project_environment
--
ALTER TABLE project_environment
  ADD CONSTRAINT fk_project_environment_project1 FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_project_environment_project_environment_file_perm1 FOREIGN KEY (project_environment_file_perm_id) REFERENCES project_environment_file_perm (id) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table project_issue_integrator
--
ALTER TABLE project_issue_integrator
  ADD CONSTRAINT fk_project_issue_integrator_project1 FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table user_projects
--
ALTER TABLE user_projects
  ADD CONSTRAINT fk_table1_project1 FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_table1_ver_user1 FOREIGN KEY (ver_user_id) REFERENCES ver_user (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
