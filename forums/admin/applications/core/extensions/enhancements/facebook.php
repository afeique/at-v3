<?php
/**
 * @file		facebook.php 	Community Enhancements - Facebook
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
 * @class		enhancements_core_facebook
 * @brief		Community Enhancements - Facebook
 */
class enhancements_core_facebook
{
	/**
	 * Applicable Settings
	 */
	public $settings = array( 'fbc_enable', 'fbc_appid', 'fbc_secret', 'fbc_mgid', 'fbc_bot_group', 'fb_locale', 'fb_realname' );

	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->title = $registry->getClass('class_localization')->words['enhancements_facebook'];
		$this->description = $registry->getClass('class_localization')->words['enhancements_facebook_desc'];
		$this->enabled = ipsRegistry::$settings['fbc_enable'];
		$this->message = '<a href="http://external.ipslink.com/ipboard30/landing/?p=facebook" target="_blank">' . ipsRegistry::getClass('class_localization')->words['enhancements_facebook_help'] . '</a>';
	}
}