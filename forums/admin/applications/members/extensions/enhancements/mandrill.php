<?php
/**
 * @file		mandrill.php 	Community Enhancements - Mandrill
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
 * @class		enhancements_members_mandrill
 * @brief		Community Enhancements - Mandrill
 */
class enhancements_members_mandrill
{
	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->title = $registry->getClass('class_localization')->words['enhancements_mandrill'];
		$this->description = $registry->getClass('class_localization')->words['enhancements_mandrill_desc'];
		$this->enabled = ( ipsRegistry::$settings['mandrill_api_key'] );
	}
	
	/**
	 * Edit Settings
	 *
	 * @param	string	Error
	 * @return	string	Output
	 */
	public function editSettings( $error='' )
	{
		ipsRegistry::getClass('class_localization')->loadLanguageFile( 'admin_bulkmail', 'members' );
	
		if ( $this->enabled )
		{
			return ipsRegistry::getClass('output')->loadTemplate( 'cp_skin_bulkmail', 'members' )->mandrillManage( $error );
		}
		else
		{
			return ipsRegistry::getClass('output')->loadTemplate( 'cp_skin_bulkmail', 'members' )->mandrillSignup( $error );
		}
	}
	
	/**
	 * Save Settings
	 */
	public function saveSettings()
	{
		/* Are we turning off? */
		if ( ipsRegistry::$request['off'] )
		{
			IPSLib::updateSettings( array( 'mandrill_username' => '', 'mandrill_api_key' => '' ) );
			ipsRegistry::getClass('output')->silentRedirect( ipsRegistry::$settings['_base_url'] . "app=core&amp;module=applications&amp;section=enhancements&amp;do=edit&amp;service=enhancements_members_mandrill" );
			return;
		}
			
		/* Load language files so we have error messages if we need em */
		ipsRegistry::getClass('class_localization')->loadLanguageFile( 'admin_bulkmail', 'members' );
		
		/* Are we adding new values or just enabling SMTP? */
		if ( ipsRegistry::$request['smtp_on'] )
		{
			ipsRegistry::$request['username'] = ipsRegistry::$settings['mandrill_username'];
			ipsRegistry::$request['api_key'] = ipsRegistry::$settings['mandrill_api_key'];
			ipsRegistry::$request['smtp'] = 1;
			$update = array();
		}
		else
		{		
			/* Trim (like a haircut, but with strings) */
			ipsRegistry::$request['username'] = trim( ipsRegistry::$request['username'] );
			ipsRegistry::$request['api_key'] = trim( ipsRegistry::$request['api_key'] );
			
			/* If we don't have anything, tell them off */
			if ( !ipsRegistry::$request['username'] or !ipsRegistry::$request['username'] )
			{
				return $this->editSettings( 'mandrill_setup_noinfo' );
			}
			
			/* Now shoot that over to Mandrill to make sure they're cool with it */
			require_once IPSLib::getAppDir('members') . '/sources/classes/mandrill.php';
			$mandrill = new Mandrill( ipsRegistry::$request['api_key'] );
			$info = $mandrill->users_info();
			if ( $info === NULL or $info->username != ipsRegistry::$request['username'] )
			{
				return $this->editSettings( 'mandrill_bad_credentials' );
			}
			
			/* So we're saving at least the API key and the username */
			$update = array( 'mandrill_username' => ipsRegistry::$request['username'], 'mandrill_api_key' => ipsRegistry::$request['api_key'] );
		}
		
		/* Fire that off to IPS so Mandrill knows it's one of ours */
		$classToLoad = IPSLib::loadLibrary( IPS_KERNEL_PATH . 'classFileManagement.php', 'classFileManagement' );
		$file = new $classToLoad();
		$json = $file->getFileContents( 'http://license.invisionpower.com/mandrill/?key='.urlencode( ipsRegistry::$request['api_key'] ) . '&username=' . urlencode( ipsRegistry::$request['username'] ) . '&lkey=' . urldecode( ipsRegistry::$settings['ipb_reg_number'] ) . '&version=' . IPB_LONG_VERSION );
		
		/* If they want to use Mandrill for SMTP too, that call will contain the SMTP info,
		   so set it if we got it, or if the call failed, throw an error */
		if ( ipsRegistry::$request['smtp'] )
		{
			if ( $json )
			{
				$json = json_decode( $json, TRUE );
				$update = array_merge( $update, $json );
			}
			else
			{
				return $this->editSettings( 'mandrill_error' );
			}
		}
		
		/* Update the settings */
		IPSLib::updateSettings( $update );
				
		/* And boink the hell out of it */
		ipsRegistry::getClass('output')->silentRedirect( ipsRegistry::$settings['_base_url'] . "app=core&amp;module=applications&amp;section=enhancements&amp;do=edit&amp;service=enhancements_members_mandrill" );
		return;
	}

}