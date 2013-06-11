<?php

$SQL[] = 'UPDATE bbcode_mediatag SET mediatag_match=\'http(?:s)?://(www.)?youtube.com/watch\\?(\\S+?)?v=([\\d\\w-_]+?)(&\\S+?)?\', mediatag_replace=\'<iframe id=\"ytplayer\" type=\"text/html\" width=\"640\" height=\"390\" src=\"http://youtube.com/embed/$3?version=3\" frameborder=\"0\"/></iframe>\' WHERE mediatag_name=\'YouTube\';';
$SQL[] = 'UPDATE bbcode_mediatag SET mediatag_match=\'http://(www.)?youtu.be/([\\d\\w-_]+?)\', mediatag_replace=\'<iframe id=\"ytplayer\" type=\"text/html\" width=\"640\" height=\"390\"\r\n  src=\"http://youtube.com/embed/$2?version=3\"\r\n  frameborder=\"0\"/></iframe>\' WHERE mediatag_name=\'YouTu.be\';';

$SQL[] = "ALTER TABLE members ADD COLUMN ipsconnect_revalidate_url TEXT NULL;";

$SQL[] = "DELETE FROM core_sys_conf_settings WHERE conf_key = 'upload_domain';";
$SQL[] = "UPDATE core_sys_settings_titles SET conf_title_noshow=0 WHERE conf_title_keyword='iphoneappsettings'";
$SQL[] = "DELETE FROM task_manager WHERE task_key='mobile_notifications';";

$SQL[] = "ALTER TABLE core_share_links ADD COLUMN share_groups TEXT NULL;";
$SQL[] = "UPDATE core_share_links SET share_groups='*';";

//