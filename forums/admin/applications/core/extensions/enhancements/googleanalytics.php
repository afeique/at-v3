<?php
/**
 * @file		facebook.php 	Community Enhancements - Google Analytics
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
 * @class		enhancements_core_googleanalytics
 * @brief		Community Enhancements - Google Analytics
 */
class enhancements_core_googleanalytics
{
	/**
	 * Applicable Settings
	 */
	public $settings = array( 'ipseo_ga' );
	
	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->title = $registry->getClass('class_localization')->words['enhancements_googleanalytics'];
		$this->description = $registry->getClass('class_localization')->words['enhancements_googleanalytics_desc'];
		$this->message = "<a href='http://www.google.com/analytics/' target='_blank'>{$registry->getClass('class_localization')->words['enhancements_googleanalytics_help']}</a>";
		$this->enabled = (bool) ipsRegistry::$settings['ipseo_ga'];
	}
}