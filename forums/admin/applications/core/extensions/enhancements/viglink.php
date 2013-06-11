<?php
/**
 * @file		twitter.php 	Community Enhancements - Viglink
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
 * @class		enhancements_core_viglink
 * @brief		Community Enhancements - Viglink
 */
class enhancements_core_viglink
{
	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->title = $registry->getClass('class_localization')->words['enhancements_viglink'];
		$this->description = $registry->getClass('class_localization')->words['enhancements_viglink_desc'];
		$this->enabled = ipsRegistry::$settings['viglink_enabled'];
		
		if ( $this->enabled )
		{
			$this->message	= sprintf( $registry->getClass('class_localization')->words['viglink_forums_notice'], $registry->output->buildUrl('app=forums', 'admin') );
			$this->settings = array( 'viglink_enabled' );
			if ( !ipsRegistry::$settings['viglink_subid'] )
			{
				$this->settings[] = 'viglink_api_key';
			}
			$this->settings[] = 'viglink_groups';
			$this->settings[] = 'viglink_norewrite';
		}
	}
		
	/**
	 * Edit Settings
	 */
	public function editSettings()
	{
		$json = $this->_makeApiCall();
		return ipsRegistry::getClass('output')->loadTemplate('cp_skin_applications')->viglinkConvert( $json['URL'] );
	}
	
	/**
	 * Save Settings
	 */
	public function saveSettings()
	{
		if ( isset( ipsRegistry::$request['viglink_manual'] ) )
		{
			IPSLib::updateSettings( array( 'viglink_enabled' => '1', 'viglink_api_key' => ipsRegistry::$request['viglink_api_key'], 'viglink_subid' => '' ) );
		}
		else
		{
			$json = $this->_makeApiCall();
			IPSLib::updateSettings( array( 'viglink_enabled' => '1', 'viglink_api_key' => $json['API_KEY'], 'viglink_subid' => $json['SUBID'] ) );
		}
		
		ipsRegistry::getClass('output')->silentRedirect( ipsRegistry::$settings['_base_url'] . "app=core&amp;module=applications&amp;section=enhancements&amp;do=edit&amp;service=enhancements_core_viglink" );
		return;
	}
	
	/**
	 * Make call to IPS to get API keys
	 *
	 * @return	array
	 */
	private function _makeApiCall()
	{
		$subId = NULL;
		if ( ipsRegistry::$settings['ipb_reg_number'] )
		{
			$exploded = explode( '-', ipsRegistry::$settings['ipb_reg_number'] );
			if ( isset( $exploded[3] ) )
			{
				$subId = $exploded[3];
			}
		}
		if ( $subId === NULL )
		{
			$subId = ipsRegistry::$settings['board_url'];
			if ( strlen( $subId ) > 32 )
			{
				$subId = str_replace( array( 'http://', 'https://' ), '', $subId );
			}
			if ( strlen( $subId ) > 32 )
			{
				$subId = md5( $subId );
			}
		}
			
		require_once IPS_KERNEL_PATH . 'classFileManagement.php';
		$cfm = new classFileManagement();
		$return = $cfm->getFileContents( "http://license.invisionpower.com/viglink/?subId=" . urlencode( $subId ) );
		if ( $return and $json = @json_decode( $return, TRUE ) and $json['SUCCESS'] == TRUE )
		{	
			return $json;
		}
		else
		{
			ipsRegistry::getClass('output')->showError( ipsRegistry::getClass('class_localization')->words['enhancements_viglink_error'] );
		}
	}
}