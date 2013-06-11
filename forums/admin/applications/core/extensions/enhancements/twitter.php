<?php
/**
 * @file		twitter.php 	Community Enhancements - Twitter
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mark $
 * @since		24 July 2012
 * $LastChangedDate: 2012-06-20 10:50:23 +0100 (Wed, 20 Jun 2012) $
 * @version		v3.4.5
 * $Revision: 10952 $
 */

/**
 *
 * @class		enhancements_core_twitter
 * @brief		Community Enhancements - Twitter
 */
class enhancements_core_twitter
{
	/**
	 * Applicable Settings
	 */
	public $settings = array( 'tc_enabled', 'tc_token', 'tc_secret', 'tc_mgid' );

	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->title = $registry->getClass('class_localization')->words['enhancements_twitter'];
		$this->description = $registry->getClass('class_localization')->words['enhancements_twitter_desc'];
		$this->enabled = ipsRegistry::$settings['tc_enabled'];
		$this->message = '<a href="http://external.ipslink.com/ipboard30/landing/?p=twitter" target="_blank">' . ipsRegistry::getClass('class_localization')->words['enhancements_twitter_help'] . '</a>';
	}
}