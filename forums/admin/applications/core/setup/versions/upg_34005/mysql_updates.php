<?php

if( !ipsRegistry::DB()->checkForIndex( 'wl_expire', 'members_warn_logs' ) )
{
	$SQL[] = "ALTER TABLE members_warn_logs ADD INDEX wl_expire ( wl_expire, wl_expire_date, wl_date )";
}

if( !ipsRegistry::DB()->checkForTable( 'search_keywords' ) )
{
	$SQL[] = "CREATE TABLE search_keywords (
		`keyword` varchar(250) NOT NULL,
		`count` int(11) NOT NULL DEFAULT '0',
		UNIQUE KEY `idx_keyword_unq` (`keyword`),
		KEY `idx_kw_cnt` (`keyword`,`count`)
	)";

}

if( !ipsRegistry::DB()->checkForTable( 'search_visitors' ) )
{
	$SQL[] = "CREATE TABLE search_visitors (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `member` int(11),
	  `date` int(11) NOT NULL DEFAULT '0',
	  `engine` varchar(50) NOT NULL,
	  `keywords` varchar(250) NOT NULL,
	  `url` varchar(2048) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `idx_date_engine` (`date`,`engine`)
	)";
}


//