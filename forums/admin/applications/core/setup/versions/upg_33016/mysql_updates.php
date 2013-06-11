<?php

$PRE = trim(ipsRegistry::dbFunctions()->getPrefix());
$DB  = ipsRegistry::DB();


$SQL[] = "ALTER TABLE upgrade_history CHANGE upgrade_notes upgrade_notes TEXT NULL default NULL;";

$SQL[] = "ALTER TABLE members_warn_logs ADD INDEX wl_expire ( wl_expire, wl_expire_date, wl_date );";

/* Do we have an old binary 16 column perhaps? */
$tableCheck = $DB->getTableSchematic('core_like');

if ( stripos( $tableCheck['Create Table'], 'binary' ) !== FALSE )
{
	/* Iz that zo? Thiz iz no good! */
	$SQL[] = "ALTER TABLE core_like CHANGE like_id like_id VARCHAR(32) NOT NULL DEFAULT '', CHANGE like_lookup_id like_lookup_id VARCHAR(32) NOT NULL DEFAULT '';";
	$SQL[] = "UPDATE core_like SET like_id=MD5( CONCAT( like_app, ';', like_area, ';', like_rel_id, ';', like_member_id  ) ), like_lookup_id=MD5( CONCAT( like_app, ';', like_area, ';', like_rel_id ) );";
}