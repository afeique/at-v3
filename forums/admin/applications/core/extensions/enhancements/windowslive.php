<?php
/**
 * @file		twitter.php 	Community Enhancements - Windows Live
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
 * @class		enhancements_core_windowslive
 * @brief		Community Enhancements - Windows Live
 */
class enhancements_core_windowslive
{
	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->title = $registry->getClass('class_localization')->words['enhancements_windowslive'];
		$this->description = $registry->getClass('class_localization')->words['enhancements_windowslive_desc'];
		
		$this->enabled = FALSE;
		$loginMethods = ipsRegistry::cache()->getCache('login_methods');
		foreach ( $loginMethods as $method )
		{
			if ( $method['login_folder_name'] == 'live' and $method['login_enabled'] )
			{
				$this->enabled = TRUE;
			}
		}
		
		$this->html = $registry->output->loadTemplate( 'cp_skin_applications' );
	}
	
	/**
	 * Edit Settings
	 */
	public function editSettings()
	{
		ipsRegistry::getClass('class_permissions')->checkPermissionAutoMsg( 'login_manage' );
	
		$method = ipsRegistry::DB()->buildAndFetch( array( 'select' => '*', 'from' => 'login_methods', 'where' => "login_folder_name='live'" ) );
		if ( !$method['login_id'] )
		{
			ipsRegistry::getClass('output')->showError( sprintf( ipsRegistry::getClass('class_localization')->words['enhancements_windowslive_error'], ipsRegistry::$settings['_base_url'] . 'app=core&amp;module=tools&amp;section=login' ) );
		}
		
		if ( ipsRegistry::$request['downloadxml'] )
		{
			$clientId = ipsRegistry::$request['client_id'];
			$clientSecret = ipsRegistry::$request['client_secret'];
			
			header( "Content-Disposition: attachment; filename=\"Application-Key.xml\"" );
			header( "Content-type: application/xml" );
			
			echo <<<XML
<windowslivelogin>
	<appid>{$clientId}</appid>
	<secret>{$clientSecret}</secret>
	<securityalgorithm>wsignin1.0</securityalgorithm>
</windowslivelogin>
XML;
			exit;
		}
		
		$config = unserialize( $method['login_custom_config'] );
		$settings = array( 'enabled' => $this->enabled );
		if ( $config['key_file_location'] )
		{
			$settings['xml'] = $config['key_file_location'];
			
			$xml = file_get_contents( $settings['xml'] );
			
			preg_match( "/<appid>(.+?)<\/appid>/" , $xml, $matches );
			$settings['client_id'] = $matches[1];
			
			preg_match( "/<secret>(.+?)<\/secret>/" , $xml, $matches );
			$settings['client_secret'] = $matches[1];
			
		}
		else
		{
			$exploded = explode( '/', rtrim( DOC_IPS_ROOT_PATH, '/' ) );
			do 
			{
				$popped = array_pop( $exploded );
			}
			while ( strstr( ipsRegistry::$settings['base_url'], $popped ) !== FALSE );
			$exploded[] = $popped;
			
			$settings['xml'] = implode( '/', $exploded ) . '/Application-Key.xml';
		}
		
		return $this->html->windowsLiveSetup( $settings );
	}
	
	/**
	 * Save Settings
	 */
	public function saveSettings()
	{
		ipsRegistry::getClass('class_permissions')->checkPermissionAutoMsg( 'login_manage' );
	
		$login = ipsRegistry::DB()->buildAndFetch( array( 'select' => '*', 'from' => 'login_methods', 'where' => "login_folder_name='live'" ) );
		if ( !$login['login_id'] )
		{
			ipsRegistry::getClass('output')->showError( sprintf( ipsRegistry::getClass('class_localization')->words['enhancements_windowslive_error'], ipsRegistry::$settings['_base_url'] . 'app=core&amp;module=tools&amp;section=login' ) );
		}
		
		if ( ipsRegistry::$request['disable'] )
		{
			ipsRegistry::DB()->update( 'login_methods', array( 'login_enabled' => 0 ), 'login_id=' . $login['login_id'] );
			ipsRegistry::cache()->rebuildCache( 'login_methods' );
			return;
		}
		
		if ( !is_file( ipsRegistry::$request['xml'] ) )
		{
			ipsRegistry::getClass('output')->showError( ipsRegistry::getClass('class_localization')->words['enhancements_windowslive_error2'] );
		}
		
		$config = unserialize( $login['login_custom_config'] );
		$config['key_file_location'] = ipsRegistry::$request['xml'];
		$toSave = array( 'login_custom_config' => serialize( $config ) );
		$toSave['login_enabled'] = '1';
		
		ipsRegistry::DB()->update( 'login_methods', $toSave, 'login_id=' . $login['login_id'] );
		
		ipsRegistry::cache()->rebuildCache( 'login_methods' );
	}
}