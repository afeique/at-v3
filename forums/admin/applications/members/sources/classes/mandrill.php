<?php
/**
 * @file		mandrill.php 	Mandrill Integration Class
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2012 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mark $
 * @since		10 October 2012
 * $LastChangedDate: 2012-06-20 10:50:23 +0100 (Wed, 20 Jun 2012) $
 * @version		v3.4.5
 * $Revision: 10952 $
 */

/**
 *
 * @class		Mandrill
 * @brief		Mandrill Integration Class
 */

class Mandrill
{
	/**
	 * Base URL
	 */
	const URL = 'https://mandrillapp.com/api/1.0/';
	
	/**
	 * API Key
	 *
	 * @var	string
	 */
	private $api_key;
	
	/**
	 * classFileManagement
	 *
	 * @var	classFileManagement
	 */
	private $cfm;
	
	/**
	 * Constructor
	 *
	 * @param	string	[Optional API key to override setting]
	 */
	public function __construct( $overrideApiKey=NULL )
	{
		require_once IPS_KERNEL_PATH . 'classFileManagement.php';
		$this->cfm = new classFileManagement();
		
		$this->api_key = $overrideApiKey ? $overrideApiKey : ipsRegistry::$settings['mandrill_api_key'];
	}
	
	/**
	 * Send API Call
	 *
	 * @param	string		Method
	 * @param	array		Arguments
	 * @return	stdClass	Object from returned JSON
	 */
	public function __call( $method, $args )
	{
		$send = array_merge( array( 'key' => $this->api_key ), ( isset( $args[0] ) and is_array( $args[0] ) ) ? $args[0] : array() );
		
		if ( IPS_DOC_CHAR_SET != 'UTF-8' )
		{
			$send['message']['html'] = IPSText::convertCharsets( $send['message']['html'], IPS_DOC_CHAR_SET, 'utf-8' );
		}
		
		$response = $this->cfm->postFileContents( self::URL . str_replace( '_', '/', $method ) . '.json', json_encode( $send ) );

		if ( $json = json_decode( $response ) )
		{
			return $json;
		}
		
		return NULL;

	}
}