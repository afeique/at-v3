<?php

$PRE = trim(ipsRegistry::dbFunctions()->getPrefix());

$hook = ipsRegistry::DB()->buildAndFetch( array( 'select' => '*', 'from' => 'core_hooks', 'where'=> "hook_key = 'othRandomTopic'" ) );

if( !$hook )
{
	$SQL[] = "DELETE FROM core_sys_conf_settings WHERE conf_key IN( 'oth_rand_topics_no_archived_plz', 'oth_rand_topic_fids', 'oth_rand_enabled' );";
	$SQL[] = "DELETE FROM core_sys_settings_titles WHERE conf_title_keyword='othRandomTopic';";
}

$SQL[] = "ALTER TABLE forums ADD INDEX ( last_poster_id );";
$SQL[] = "ALTER TABLE topics ADD INDEX ( last_poster_id );";

$SQL[] = "ALTER TABLE core_hooks CHANGE hook_key hook_key VARCHAR( 128 ) NULL DEFAULT NULL;";

$SQL[] = "ALTER TABLE core_like DROP INDEX like_lookup_area, ADD INDEX like_lookup_area ( like_lookup_area , like_visible , like_added );";