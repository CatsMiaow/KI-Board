-- -----------------------------------------------------
-- Table .`ki_board`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_board` (
  `bo_table` VARCHAR(20) NOT NULL ,
  `gr_id` VARCHAR(20) NOT NULL ,
  `bo_subject` VARCHAR(20) NOT NULL ,
  `bo_admin` VARCHAR(20) NOT NULL ,
  `bo_list_level` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_read_level` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_write_level` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_reply_level` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_comment_level` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_upload_level` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_download_level` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_count_delete` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_count_modify` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_use_private` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_rss` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_sns` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_comment` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_category` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_sideview` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_secret` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_editor` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_name` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_ip_view` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_list_view` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_email` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_extra` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_use_syntax` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_skin` VARCHAR(20) NOT NULL ,
  `bo_page_rows` INT(10) UNSIGNED NOT NULL ,
  `bo_page_rows_comt` INT(10) UNSIGNED NOT NULL ,
  `bo_subject_len` INT(10) UNSIGNED NOT NULL ,
  `bo_new` INT(10) UNSIGNED NOT NULL ,
  `bo_hot` INT(10) UNSIGNED NOT NULL ,
  `bo_image_width` INT(10) UNSIGNED NOT NULL ,
  `bo_reply_order` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_sort_field` VARCHAR(50) NOT NULL ,
  `bo_upload_ext` VARCHAR(50) NOT NULL ,
  `bo_upload_size` INT(10) UNSIGNED NOT NULL ,
  `bo_head` VARCHAR(50) NOT NULL ,
  `bo_tail` VARCHAR(50) NOT NULL ,
  `bo_insert_content` TEXT NOT NULL ,
  `bo_use_search` TINYINT(2) UNSIGNED NOT NULL ,
  `bo_order_search` TINYINT(4) UNSIGNED NOT NULL ,
  `bo_count_write` INT(10) UNSIGNED NOT NULL ,
  `bo_count_comment` INT(10) UNSIGNED NOT NULL ,
  `bo_notice` VARCHAR(255) NOT NULL ,
  `bo_min_wr_num` INT(11) NOT NULL ,
  PRIMARY KEY (`bo_table`) );


-- -----------------------------------------------------
-- Table .`ki_board_file`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_board_file` (
  `bo_table` VARCHAR(20) NOT NULL ,
  `wr_id` INT(10) UNSIGNED NOT NULL ,
  `bf_editor` TINYINT(2) UNSIGNED NOT NULL ,
  `bf_no` INT(10) UNSIGNED NOT NULL ,
  `bf_source` VARCHAR(255) NOT NULL ,
  `bf_file` VARCHAR(255) NOT NULL ,
  `bf_download` INT(10) UNSIGNED NOT NULL ,
  `bf_filesize` INT(10) UNSIGNED NOT NULL ,
  `bf_width` SMALLINT(6) UNSIGNED NOT NULL ,
  `bf_height` SMALLINT(6) UNSIGNED NOT NULL ,
  `bf_type` TINYINT(4) NOT NULL ,
  `bf_datetime` DATETIME NOT NULL ,
  PRIMARY KEY (`bo_table`, `wr_id`, `bf_editor`, `bf_no`) );


-- -----------------------------------------------------
-- Table .`ki_board_group`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_board_group` (
  `gr_id` VARCHAR(20) NOT NULL ,
  `gr_subject` VARCHAR(20) NOT NULL ,
  `gr_admin` VARCHAR(20) NOT NULL ,
  PRIMARY KEY (`gr_id`) );


-- -----------------------------------------------------
-- Table .`ki_category`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_category` (
  `ca_type` VARCHAR(20) NOT NULL ,
  `ca_code` VARCHAR(255) NOT NULL ,
  `ca_name` VARCHAR(20) NOT NULL ,
  PRIMARY KEY (`ca_type`, `ca_code`) );


-- -----------------------------------------------------
-- Table .`ki_mail`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_mail` (
  `ma_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `ma_subject` VARCHAR(255) NOT NULL ,
  `ma_content` MEDIUMTEXT NOT NULL ,
  `ma_time` DATETIME NOT NULL ,
  `ma_ip` VARCHAR(20) NOT NULL ,
  `ma_last_option` TEXT NOT NULL ,
  PRIMARY KEY (`ma_id`) );


-- -----------------------------------------------------
-- Table .`ki_member`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_member` (
  `mb_no` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `mb_id` VARCHAR(20) NOT NULL ,
  `mb_password` CHAR(128) NOT NULL ,
  `mb_name` VARCHAR(10) NOT NULL ,
  `mb_nick` VARCHAR(20) NOT NULL ,
  `mb_nick_date` DATE NOT NULL ,
  `mb_email` VARCHAR(50) NOT NULL ,
  `mb_homepage` VARCHAR(40) NOT NULL ,
  `mb_password_q` VARCHAR(50) NOT NULL ,
  `mb_password_a` VARCHAR(50) NOT NULL ,
  `mb_level` TINYINT(4) UNSIGNED NOT NULL ,
  `mb_sex` CHAR(1) NOT NULL ,
  `mb_birth` DATE NOT NULL ,
  `mb_tel` VARCHAR(14) NOT NULL ,
  `mb_hp` VARCHAR(14) NOT NULL ,
  `mb_zip` CHAR(7) NOT NULL ,
  `mb_addr1` VARCHAR(100) NOT NULL ,
  `mb_addr2` VARCHAR(100) NOT NULL ,
  `mb_point` INT(11) NOT NULL ,
  `mb_today_login` DATETIME NOT NULL ,
  `mb_login_ip` VARCHAR(20) NOT NULL ,
  `mb_datetime` DATETIME NOT NULL ,
  `mb_ip` VARCHAR(20) NOT NULL ,
  `mb_leave_date` VARCHAR(8) NOT NULL ,
  `mb_email_certify` DATETIME NOT NULL ,
  `mb_mailling` TINYINT(2) UNSIGNED NOT NULL ,
  `mb_open` TINYINT(2) UNSIGNED NOT NULL ,
  `mb_open_date` DATE NOT NULL ,
  `mb_profile` TEXT NOT NULL ,
  `mb_memo_call` VARCHAR(20) NOT NULL ,
  `mb_memo_cnt` TINYINT(4) UNSIGNED NOT NULL ,
  PRIMARY KEY (`mb_no`) ,
  UNIQUE INDEX `mb_id` (`mb_id` ASC) );


-- -----------------------------------------------------
-- Table .`ki_memo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_memo` (
  `me_no` INT(10) UNSIGNED NOT NULL ,
  `me_parent` INT(10) UNSIGNED NOT NULL ,
  `me_flag` ENUM('R','S') NOT NULL ,
  `mb_id` VARCHAR(20) NOT NULL ,
  `me_mb_id` VARCHAR(20) NOT NULL ,
  `me_content` TEXT NOT NULL ,
  `me_datetime` DATETIME NOT NULL ,
  `me_check` DATETIME NOT NULL ,
  PRIMARY KEY (`me_no`) ,
  INDEX `list` (`me_flag` ASC, `mb_id` ASC) );


-- -----------------------------------------------------
-- Table .`ki_point`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_point` (
  `po_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `mb_id` VARCHAR(20) NOT NULL ,
  `po_datetime` DATETIME NOT NULL ,
  `po_content` VARCHAR(255) NOT NULL ,
  `po_point` INT(11) NOT NULL ,
  `po_rel_table` VARCHAR(20) NOT NULL ,
  `po_rel_id` VARCHAR(20) NOT NULL ,
  `po_rel_action` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`po_id`) ,
  INDEX `where` (`mb_id` ASC, `po_rel_table` ASC, `po_rel_id` ASC, `po_rel_action` ASC) );


-- -----------------------------------------------------
-- Table .`ki_popular`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_popular` (
  `pp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `pp_word` VARCHAR(50) NOT NULL ,
  `pp_date` DATE NOT NULL ,
  `pp_ip` VARCHAR(20) NOT NULL ,
  PRIMARY KEY (`pp_id`) ,
  UNIQUE INDEX `isset` (`pp_word` ASC, `pp_date` ASC, `pp_ip` ASC) );


-- -----------------------------------------------------
-- Table .`ki_popup`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_popup` (
  `pu_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `pu_name` VARCHAR(20) NOT NULL ,
  `pu_file` VARCHAR(20) NOT NULL ,
  `pu_use` TINYINT(2) UNSIGNED NOT NULL ,
  `pu_type` TINYINT(2) UNSIGNED NOT NULL ,
  `pu_sdate` DATETIME NOT NULL ,
  `pu_edate` DATETIME NOT NULL ,
  `pu_width` SMALLINT(6) UNSIGNED NOT NULL ,
  `pu_height` SMALLINT(6) UNSIGNED NOT NULL ,
  `pu_x` SMALLINT(6) UNSIGNED NOT NULL ,
  `pu_y` SMALLINT(6) UNSIGNED NOT NULL ,
  `pu_datetime` DATETIME NOT NULL ,
  PRIMARY KEY (`pu_id`) ,
  INDEX `po_use` (`pu_sdate` ASC, `pu_edate` ASC, `pu_use` ASC) );


-- -----------------------------------------------------
-- Table .`ki_session`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_session` (
  `session_id` VARCHAR(40) NOT NULL DEFAULT '0' ,
  `ip_address` VARCHAR(45) NOT NULL DEFAULT '0' ,
  `user_agent` VARCHAR(120) NOT NULL ,
  `last_activity` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
  `user_data` TEXT NOT NULL ,
  PRIMARY KEY (`session_id`) ,
  INDEX `last_activity_idx` (`last_activity` ASC) );


-- -----------------------------------------------------
-- Table .`ki_visit`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_visit` (
  `vi_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `vi_ip` VARCHAR(20) NOT NULL ,
  `vi_date` DATE NOT NULL ,
  `vi_time` TIME NOT NULL ,
  `vi_referer` TEXT NOT NULL ,
  `vi_agent` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`vi_id`) ,
  UNIQUE INDEX `unique` (`vi_ip` ASC, `vi_date` ASC) ,
  INDEX `vi_date` (`vi_date` ASC) );


-- -----------------------------------------------------
-- Table .`ki_write`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_write` (
  `bo_table` VARCHAR(20) NOT NULL ,
  `wr_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `wr_num` INT(11) NOT NULL ,
  `wr_reply` VARCHAR(10) NOT NULL ,
  `ca_code` VARCHAR(255) NOT NULL ,
  `wr_comment` INT(10) UNSIGNED NOT NULL ,
  `wr_option` SET('editor','secret','mail','nocomt') NOT NULL ,
  `wr_subject` VARCHAR(255) NOT NULL ,
  `wr_content` TEXT NOT NULL ,
  `wr_hit` INT(10) UNSIGNED NOT NULL ,
  `mb_id` VARCHAR(20) NOT NULL ,
  `wr_password` CHAR(128) NOT NULL ,
  `wr_name` VARCHAR(10) NOT NULL ,
  `wr_email` VARCHAR(50) NOT NULL ,
  `wr_datetime` DATETIME NOT NULL ,
  `wr_last` DATETIME NOT NULL ,
  `wr_ip` VARCHAR(20) NOT NULL ,
  `wr_count_file` TINYINT(4) UNSIGNED NOT NULL ,
  `wr_count_image` TINYINT(4) UNSIGNED NOT NULL ,
  PRIMARY KEY (`bo_table`, `wr_id`) ,
  INDEX `list` (`bo_table` ASC, `wr_num` ASC, `wr_reply` ASC, `ca_code` ASC) );


-- -----------------------------------------------------
-- Table .`ki_comment`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS .`ki_comment` (
  `bo_table` VARCHAR(20) NOT NULL ,
  `wr_id` INT(10) UNSIGNED NOT NULL ,
  `co_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `co_num` INT(11) NOT NULL ,
  `co_reply` VARCHAR(10) NOT NULL ,
  `ca_code` VARCHAR(255) NOT NULL ,
  `co_option` SET('editor','secret') NOT NULL ,
  `co_content` TEXT NOT NULL ,
  `mb_id` VARCHAR(20) NOT NULL ,
  `co_password` CHAR(128) NOT NULL ,
  `co_name` VARCHAR(10) NOT NULL ,
  `co_datetime` DATETIME NOT NULL ,
  `co_last` DATETIME NOT NULL ,
  `co_ip` VARCHAR(20) NOT NULL ,
  PRIMARY KEY (`bo_table`, `wr_id`, `co_id`) ,
  INDEX `list` (`bo_table` ASC, `wr_id` ASC, `co_num` ASC, `co_reply` ASC, `ca_code` ASC) );
