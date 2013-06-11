<?php

class ipsConnect_custom
{
	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->registry		=  $registry;
		$this->DB			=  $this->registry->DB();
		$this->settings		=& $this->registry->fetchSettings();
		$this->request		=& $this->registry->fetchRequest();
		$this->lang			=  $this->registry->getClass('class_localization');
		$this->member		=  $this->registry->member();
		$this->cache		=  $this->registry->cache();
		$this->caches		=& $this->registry->cache()->fetchCaches();
	}
	
	/**
	 * Login
	 * Note: will be fired even if login fails
	 *
	 * @param	array|null	Member data
	 * @param	string		Status code
	 *						SUCCESS			login successful
	 *						WRONG_AUTH		Password incorrect
	 *						NO_USER			Identifier did not match member account
	 *						MISSING_DATA	Identifier or password was blank
	 *						ACCOUNT_LOCKED	Account has been locked by brute-force prevention
	 */
	public function login( $member, $statusCode )
	{
		
	}
	
	/**
	 * Logout
	 *
	 * @param	array		Member data
	 */
	public function logout( $member )
	{
		
	}
}