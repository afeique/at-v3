<?php

/* Disable non-IPS hooks */
$SQL[] = "UPDATE core_hooks SET hook_enabled=0 WHERE hook_author!='Invision Power Services, Inc.' AND hook_author!='Invision Power Services, Inc';";

$SQL[] = "DROP TABLE converge_local;";
$SQL[] = "DELETE FROM core_sys_conf_settings WHERE conf_key IN( 'ipconverge_enabled', 'ipconverge_url', 'ipconverge_pid' );";
$SQL[] = "DELETE FROM core_sys_settings_titles WHERE conf_title_keyword='ipconverge';";
$SQL[] = "DELETE FROM login_methods WHERE login_folder_name='ipconverge';";

$SQL[] = "ALTER TABLE members ADD COLUMN ipsconnect_id INT(10) NOT NULL DEFAULT 0;";
$SQL[] = "ALTER TABLE mail_queue ADD mail_html_content MEDIUMTEXT;";

$SQL[] = "ALTER TABLE forums ADD COLUMN viglink TINYINT(1) NOT NULL DEFAULT 1;";

$SQL[] = "UPDATE custom_bbcode SET bbcode_replace='', bbcode_php_plugin='defaults.php' where bbcode_tag='code';";

$SQL[] = "ALTER TABLE core_sys_login ADD sys_bookmarks MEDIUMTEXT;";

$SQL[] = "CREATE TABLE core_sys_bookmarks (
bookmark_id			INT(10) NOT NULL AUTO_INCREMENT,
bookmark_member_id	INT(10) NOT NULL DEFAULT 0,
bookmark_title		VARCHAR(255) NOT NULL DEFAULT '',
bookmark_url		VARCHAR(255) NOT NULL DEFAULT '',
bookmark_home		INT(1) NOT NULL DEFAULT 0,
bookmark_pos		INT(5) NOT NULL DEFAULT 0,
PRIMARY KEY (bookmark_id),
KEY bookmark_member_id (bookmark_member_id)
);";

$SQL[] = "ALTER TABLE topics ADD topic_answered_pid INT(10) NOT NULL DEFAULT 0;";

$SQL[] = "DELETE FROM core_uagents WHERE uagent_key='mob_saf';";

$SQL[] = "DELETE FROM bbcode_mediatag WHERE mediatag_name='GameTrailers';";

$SQL[] = "ALTER TABLE forums DROP rules_raw_html;";

$SQL[] = "DROP TABLE core_share_links_caches;";

$SQL[] = "CREATE TABLE backup_vars (
	backup_var_key 		VARCHAR(255) NOT NULL DEFAULT '',
	backup_var_value	TEXT,
	PRIMARY KEY (backup_var_key)
);";

$SQL[] = "CREATE TABLE backup_log (
	log_id			BIGINT(20) NOT NULL AUTO_INCREMENT,
	log_row_count	INT(10) NOT NULL DEFAULT 0,
	log_result		TEXT,
	PRIMARY KEY (log_id)
);";

$SQL[] = "CREATE TABLE backup_queue (
	queue_id			BIGINT(20) NOT NULL AUTO_INCREMENT,
	queue_entry_date	INT(10) NOT NULL DEFAULT 0,
	queue_entry_type	INT(1) NOT NULL DEFAULT 0,
	queue_entry_table	VARCHAR(255) NOT NULL DEFAULT '',
	queue_entry_key		VARCHAR(255) NOT NULL DEFAULT '',
	queue_entry_value	VARCHAR(255) NOT NULL DEFAULT '',
	queue_entry_sql		MEDIUMTEXT,
	PRIMARY KEY (queue_id),
	KEY date (queue_entry_date)
);";


if( !ipsRegistry::DB()->checkForTable( 'seo_meta' ) )
{
	$SQL[] = "CREATE TABLE seo_meta (
		url varchar(255) NOT NULL DEFAULT '*',
		name varchar(50) NOT NULL DEFAULT '',
		content text NOT NULL
	)";
}

if( !ipsRegistry::DB()->checkForTable( 'seo_acronyms' ) )
{
	$SQL[] = "CREATE TABLE seo_acronyms (
		  a_id int(10) unsigned NOT NULL AUTO_INCREMENT,
		  a_short varchar(255) DEFAULT NULL,
		  a_long varchar(255) DEFAULT NULL,
		  a_semantic tinyint(1) DEFAULT NULL,
		  a_casesensitive tinyint(1) DEFAULT NULL,
		  PRIMARY KEY (a_id),
		  KEY a_short (a_short)
		)";
}

//