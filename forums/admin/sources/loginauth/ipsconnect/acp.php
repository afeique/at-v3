<?php
/**
 * @file		acp.php		IPS Connect - ACP Settings
 *
 * $Copyright: $
 * $License: $
 * $Author: mark $
 * $LastChangedDate: 2011-04-06 04:34:47 -0400 (Wed, 06 Apr 2011) $
 * $Revision: 8267 $
 * @since 		18th July 2012
 */

$config = array(
	array(
		'key'			=> 'master_url',
		'title'			=> 'Master URL',
		'description'	=> "Enter the URL to the Master's ipsconnect.php file.<br />If the Master is an IPS Community Suite installation, this information can be found in the Log In Management screen."
		),
	array(
		'key'			=> 'master_key',
		'title'			=> 'Master Key',
		'description'	=> "If the Master is an IPS Community Suite installation, this information can be found in the Log In Management screen."
		)
	);