<?php
/**
 * @file		facebook.php 	Community Enhancements - IPS Spam Monitoring
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
 * @class		enhancements_members_spammonitoring
 * @brief		Community Enhancements - IPS Spam Monitoring
 */
class enhancements_members_spammonitoring
{
	/**
	 * Applicable Settings
	 */
	public $settings = array( 'spam_service_enabled', 'spam_service_send_to_ips', 'spam_service_timeout', 'spam_service_action_timeout', 'spam_service_action_0', 'spam_service_action_1', 'spam_service_action_2', 'spam_service_action_3', 'spam_service_action_4' );

	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->title = $registry->getClass('class_localization')->words['enhancements_spammonitoring'];
		$this->description = $registry->getClass('class_localization')->words['enhancements_spammonitoring_desc'];
		$this->message = "<a href='http://external.ipslink.com/ipboard30/landing/?p=spamserviceinfo' target='_blank'>{$registry->getClass('class_localization')->words['enhancements_spammonitoring_help']}</a>";
		$this->enabled = ( ipsRegistry::$settings['spam_service_enabled'] and $this->check( TRUE ) );
	}
	
	/**
	 * Check service is available
	 */
	public function check( $return=FALSE )
	{
		$ok = FALSE;
	
		$licenseData = ipsRegistry::cache()->getCache('licenseData');
		if ( isset( $licenseData['ipbMain'] ) )
		{
			foreach ( $licenseData['ipbMain'] as $service )
			{
				if ( $service['name'] == 'Spam Monitoring Service' and $service['status'] == 'Ok' )
				{
					$ok = TRUE;
					break;
				}
			}
		}
		
		if ( $return )
		{
			return $ok;
		}
		
		if ( !$ok )
		{
			ipsRegistry::getClass('output')->showError( ipsRegistry::getClass('class_localization')->words['enhancements_spammonitoring_error'] );
		}
	}
}