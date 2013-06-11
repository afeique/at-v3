<?php
/**
 * @file		ipsconnect.php		IPS Connect
 *
 * $Copyright: $
 * $License: $
 * $Author: mark $
 * $LastChangedDate: 2011-04-06 04:34:47 -0400 (Wed, 06 Apr 2011) $
 * $Revision: 8267 $
 * @since 		18th July 2012
 */

/**
 *
 * @class	ipsConnect
 * @brief	This is where you put the code for your application
 *
 */
class ipsConnect
{
	/**
	 * Constructor
	 *
	 * Use this to do any initiation required by your application
	 */
	public function __construct()
	{
		//-----------------------------------------
		// Init IPB
		//-----------------------------------------
		
		define( 'IPS_ENFORCE_ACCESS', TRUE );
		define( 'IPB_THIS_SCRIPT', 'public' );
		require_once( '../../initdata.php' );
		
		require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
		require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );
		
		$this->registry = ipsRegistry::instance();
		$this->registry->init();
		
		//-----------------------------------------
		// Set up shortcuts
		//-----------------------------------------
		
		$this->DB			=  $this->registry->DB();
		$this->settings		=& $this->registry->fetchSettings();
		$this->request		=& $this->registry->fetchRequest();
		$this->lang			=  $this->registry->getClass('class_localization');
		$this->member		=  $this->registry->member();
		$this->cache		=  $this->registry->cache();
		$this->caches		=& $this->registry->cache()->fetchCaches();
		
		$this->masterKey = md5( md5( $this->settings['sql_user'] . $this->settings['sql_pass'] ) . $this->settings['board_start'] );
				
		//-----------------------------------------
		// Init han_login
		//-----------------------------------------
		
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/handlers/han_login.php', 'han_login' );
    	$this->han_login =  new $classToLoad( $this->registry );
    	$this->han_login->init();
	}
	
	/**
	 * Process Login
	 *
	 * @param	string	Identifier - may be 'id', 'email' or 'username'
	 * @param	string	Value for identifier (for example, the user's ID number)
	 * @param	string	The password, md5 encoded
	 * @param	string	md5( IPS Connect Key (see login method) . Identifier Value )
	 * @param	string	Redirect URL, Base64 encoded
	 * @param	string	md5( IPS Connect Key . $redirect )
	 * @return	mixed	If the redirect URL is provided, this function should redirect the user to that URL with additional paramaters:
	 *						connect_status		value from below
	 *						connect_id			the ID number in this app
	 *						connect_username	the username
	 *						connect_displayname	the display name
	 *						connect_email		the email address
	 *						connect_unlock		If the account is locked, the time that it was locked
	 *						connect_unlock_period	The number of minutes until the account is unlocked (will be 0 if account does not automatically unlock)
	 *					If blank, will output to screen a JSON object with the same parameters
	 *					Values:
	 *						SUCCESS			login successful
	 *						WRONG_AUTH		Password incorrect
	 *						NO_USER			Identifier did not match member account
	 *						MISSING_DATA	Identifier or password was blank
	 *						ACCOUNT_LOCKED	Account has been locked by brute-force prevention
	 */
	public function login( $identifier, $identifierValue, $md5Password, $key, $redirect, $redirectHash )
	{
		$member = NULL;
		$statusCode = 'MISSING_DATA';
		$secondsUntilUnlock = 0;
		$revalidateUrl = '';
	
		/* Check */
		if ( in_array( $identifier, array( 'id', 'email', 'username' ) ) )
		{
			$member = IPSMember::load( $identifierValue, 'none', $identifier );
			if ( $member['member_id'] )
			{
				/* Check we're not blocked */
				if ( $this->settings['ipb_bruteforce_attempts'] > 0 )
				{
					$failed_attempts = explode( ",", IPSText::cleanPermString( $member['failed_logins'] ) );
					$failed_count	 = 0;
					$total_failed	 = 0;
					$thisip_failed	 = 0;
					$non_expired_att = array();
					
					if( is_array($failed_attempts) AND count($failed_attempts) )
					{
						foreach( $failed_attempts as $entry )
						{
							if ( ! strpos( $entry, "-" ) )
							{
								continue;
							}
							
							list ( $timestamp, $ipaddress ) = explode( "-", $entry );
							
							if ( ! $timestamp )
							{
								continue;
							}
							
							$total_failed++;
							
							if ( $ipaddress != $this->member->ip_address )
							{
								continue;
							}
							
							$thisip_failed++;
							
							if ( $this->settings['ipb_bruteforce_period'] AND
								$timestamp < time() - ($this->settings['ipb_bruteforce_period']*60) )
							{
								continue;
							}
							
							$non_expired_att[] = $entry;
							$failed_count++;
						}
						
						sort($non_expired_att);
						$oldest_entry  = array_shift( $non_expired_att );
						list($oldest,) = explode( "-", $oldest_entry );
					}
		
					if( $thisip_failed >= $this->settings['ipb_bruteforce_attempts'] )
					{
						if( $this->settings['ipb_bruteforce_unlock'] )
						{
							if( $failed_count >= $this->settings['ipb_bruteforce_attempts'] )
							{
								$secondsUntilUnlock	= $oldest;
								$statusCode			= 'ACCOUNT_LOCKED';
							}
						}
						else
						{
							$statusCode = 'ACCOUNT_LOCKED';
						}
					}
				}
				
				/* Check the password is valid */
				if ( $statusCode != 'ACCOUNT_LOCKED' )
				{		
					if ( IPSMember::authenticateMember( $member['member_id'], $md5Password ) )
					{
						/* Are we validating? */
						if ( $member['ipsconnect_revalidate_url'] )
						{
							$statusCode = 'VALIDATING';
							$revalidateUrl = $member['ipsconnect_revalidate_url'];
						}
						else
						{				
							$validating = ipsRegistry::DB()->buildAndFetch( array( 'select' => '*', 'from' => 'validating', 'where' => "member_id={$member['member_id']} AND new_reg=1" ) );
							if ( $validating['vid'] )
							{
								$statusCode = 'VALIDATING';

								if( $validating['user_verified'] == 1 OR $this->settings['reg_auth_type'] == 'admin' )
								{
									$revalidateUrl = 'ADMIN_VALIDATION';
								}
								else
								{
									$revalidateUrl = ipsRegistry::getClass('output')->buildUrl( 'app=core&amp;module=global&amp;section=register&amp;do=reval', 'public' );
								}
							}
						}
						
						if ( $statusCode != 'VALIDATING' )
						{
							/* Login Successful */
							$statusCode = 'SUCCESS';
							
							/* Log us in locally */
							$this->han_login->loginWithoutCheckingCredentials( $member['member_id'], TRUE );
							
							/* Run memberSync */
							IPSLib::runMemberSync( 'onLogin', $member );
						}
					}
					else
					{
						/* Login Failed */
						$statusCode = 'WRONG_AUTH';
						
						/* Append failed login */
						if( $this->settings['ipb_bruteforce_attempts'] > 0 )
						{
							$failed_logins 	 = explode( ",", $member['failed_logins'] );
							$failed_logins[] = time() . '-' . $this->member->ip_address;
							
							$failed_count	 = 0;
							$total_failed	 = 0;
							$non_expired_att = array();
				
							foreach( $failed_logins as $entry )
							{
								list($timestamp,$ipaddress) = explode( "-", $entry );
								
								if( !$timestamp )
								{
									continue;
								}
								
								$total_failed++;
								
								if( $ipaddress != $this->member->ip_address )
								{
									continue;
								}
								
								if( $this->settings['ipb_bruteforce_period'] > 0
									AND $timestamp < time() - ($this->settings['ipb_bruteforce_period']*60) )
								{
									continue;
								}
								
								$failed_count++;
								$non_expired_att[] = $entry;
							}
				
							if( $member['member_id'] AND !$this->settings['failed_done'] )
							{
								IPSMember::save( $member['email'], array( 
																					'core' => array(
																									'failed_logins' => implode( ",", $non_expired_att ), 
																									'failed_login_count' => $total_failed 
																									) 
																					)		);
							}
						}
					}
				}
			}
			else
			{
				$statusCode = 'NO_USER';
			}
		}
		
		/* Run any custom code */
		$this->_runCustom( 'login', array( $member, $statusCode ) );
		
		/* Hide the email if necessary */
		if ( $statusCode != 'SUCCESS' and $identifier != 'email' )
		{
			$member['email'] = '';
		}
		
		/* Return */
		if ( $redirect )
		{
			$redirect = ( ( $key == md5( $this->masterKey . $identifierValue ) ) and ( $redirectHash == md5( $this->masterKey . $redirect ) ) ) ? $redirect : base64_encode( $this->settings['board_url'] );
		}
		$this->_return( $redirect, array( 'connect_status' => $statusCode, 'connect_id' => $member['member_id'], 'connect_username' => $member['name'], 'connect_displayname' => $member['members_display_name'], 'connect_email' => $member['email'], 'connect_unlock' => $secondsUntilUnlock, 'connect_revalidate_url'=> $revalidateUrl, 'connect_unlock_period' => $this->settings['ipb_bruteforce_period'] ) );
	}
	
	/**
	 * Process Logout
	 *
	 * @param	int		ID number
	 * @param	string	md5( IPS Connect Key (see login method) . ID number )
	 * @param	string	Redirect URL, Base64 encoded
	 * @param	string	md5( IPS Connect Key . $redirect )
	 * @return	mixed	If the redirect URL is provided, this function should redirect the user to that URL
	 *					If blank, will output blank screen
	 */
	public function logout( $id, $key, $redirect, $redirectHash )
	{
		if ( $key != md5( $this->masterKey . $id ) )
		{
			$this->_return( base64_encode( $this->settings['board_url'] ) );
		}
				
		IPSCookie::set( "ipsconnect_" . md5( $this->settings['board_url'] . '/interface/ipsconnect/ipsconnect.php' ), '0', 1, 0, FALSE, FALSE );
			
		$member = IPSMember::load( intval( $id ), 'none', 'id' );
		if ( $member['member_id'] )
		{
			IPSCookie::set( "member_id" , "0"  );
			IPSCookie::set( "pass_hash" , "0"  );
			
			if( is_array( $_COOKIE ) )
	 		{
	 			foreach( $_COOKIE as $cookie => $value)
	 			{
	 				if ( stripos( $cookie, $this->settings['cookie_id'] . 'ipbforumpass' ) !== false AND ! strstr( $value, 'mobileApp' ) )
	 				{
	 					IPSCookie::set( str_replace( $this->settings['cookie_id'], "", $cookie ), '-', -1 );
	 				}
	 			}
	 		}
	 		
	 		$this->member->sessionClass()->convertMemberToGuest();
			$privacy = intval( IPSMember::isLoggedInAnon($member) );
			IPSMember::save( $member['member_id'], array( 'core' => array( 'login_anonymous' => "{$privacy}&0", 'last_activity' => IPS_UNIX_TIME_NOW ) ) );
			
			IPSLib::runMemberSync( 'onLogOut', $member );
			$this->han_login->logoutCallback( $member );
			
			/* Run any custom code */
			$this->_runCustom( 'logout', array( $member ) );
		}
		
		if ( $redirect )
		{
			$redirect = ( $redirectHash == md5( $this->masterKey . $redirect ) ) ? $redirect : base64_encode( $this->settings['board_url'] );		
		}
		$this->_return( $redirect );
	}
	
	/**
	 * Register a new account
	 *
	 * @param	string	Key - this can be anything which is known only to the applications. Never reveal this key publically.
	 *					For IPS Community Suite installs, this key can be obtained in the Login Management page in the ACP
	 * @param	string	Username
	 * @param	string	Display name
	 * @param	string	The password, md5 encoded
	 * @param	string	Email address
	 * @return	void	Outputs to screen JSON object with 2 parameters 
	 					'status'	One of the following values:
	 									BAD_KEY				The key provided was invalid
	 									SUCCESS				Account created
	 									EMAIL_IN_USE		Email is already in use
	 									USERNAME_IN_USE		Username is already in use
	 									BAD_KEY				Key was invalid
	 									MISSING_DATA		Not all data was provided
	 									FAIL				Other error
	 					'id' with master ID number (0 if fail) - if user already exists, will provide ID of existing user
	 */
	public function register( $key, $username, $displayname, $md5Password, $email, $revalidateUrl )
	{
		//-----------------------------------------
		// Checks
		//-----------------------------------------
		
		/* Key is good */
		if ( $key != $this->masterKey )
		{
			echo json_encode( array( 'status' => 'BAD_KEY', 'id' => 0 ) );
			exit;
		}

		/* We have the data */
		if ( !$email or !$md5Password )
		{
			echo json_encode( array( 'status' => 'MISSING_DATA', 'id' => 0 ) );
			exit;
		}
		
		/* Email/Username is not in use */
		$member = IPSMember::load( $email, 'none', 'email' );
		if ( $member['member_id'] )
		{
			echo json_encode( array( 'status' => 'EMAIL_IN_USE', 'id' => $member['member_id'] ) );
			exit;
		}
		if ( $username )
		{
			$member = IPSMember::load( $username, 'none', 'username' );
			if ( $member['member_id'] )
			{
				echo json_encode( array( 'status' => 'USERNAME_IN_USE', 'id' => $member['member_id'] ) );
				exit;
			}
		}
		
		//-----------------------------------------
		// Create
		//-----------------------------------------
		
		/* Set basic data */
		$tables = array( 'members' => array( 'email' => $email, 'md5_hash_password' => $md5Password ) );
		if ( $displayname and !$username )
		{
			$username = $displayname;
		}
		
		/* Are we validating? */
		if ( $revalidateUrl )
		{
			$tables['members']['member_group_id'] = ipsRegistry::$settings['auth_group'];
			$tables['members']['ipsconnect_revalidate_url'] = base64_decode( $revalidateUrl );
		}
		
		/* Create */
		if ( $username )
		{
			$tables['members']['name'] = $username;
			if ( $displayname )
			{
				$tables['members']['members_display_name'] = $displayname;
			}
			$member = IPSMember::create( $tables, TRUE, TRUE );
		}
		else
		{
			$member = IPSMember::create( $tables, FALSE, TRUE, FALSE );
		}
		
		//-----------------------------------------
		// Return
		//-----------------------------------------
		
		if ( $member['member_id'] )
		{
			echo json_encode( array( 'status' => 'SUCCESS', 'id' => $member['member_id'] ) );
			exit;
		}
		else
		{
			echo json_encode( array( 'status' => 'FAIL', 'id' => 0 ) );
			exit;
		}
	}
	
	/**
	 * Validate Cookie Data
	 *
	 * @param	string	JSON encoded cookie data
	 * @return	void	Outputs to screen a JSON object with the bollowing properties:
	 *						connect_status		SUCCESS or FAIL
	 *						connect_id			the ID number in this app
	 *						connect_username	the username
	 *						connect_displayname	the display name
	 *						connect_email		the email address
	 */
	public function cookies( $data )
	{
		$cookies = json_decode( stripslashes( urldecode( $data ) ), TRUE );
		
		if ( isset( $cookies[ ipsRegistry::$settings['cookie_id'] . 'member_id' ] ) )
		{
			$member = IPSMember::load( $cookies[ ipsRegistry::$settings['cookie_id'] . 'member_id' ] );
			if ( $member['member_id'] and $member['member_login_key'] == $cookies[ ipsRegistry::$settings['cookie_id'] . 'pass_hash' ] and ( !$member['member_login_key_expire'] or time() <= $member['member_login_key_expire'] ) )
			{
				$statusCode = 'SUCCESS';
				$revalidateUrl = '';
				
				if ( $member['ipsconnect_revalidate_url'] )
				{
					$statusCode = 'VALIDATING';
					$revalidateUrl = $member['ipsconnect_revalidate_url'];
				}
				else
				{				
					$validating = ipsRegistry::DB()->buildAndFetch( array( 'select' => '*', 'from' => 'validating', 'where' => "member_id={$member['member_id']} AND new_reg=1" ) );
					if ( $validating['vid'] )
					{
						$statusCode = 'VALIDATING';

						if( $validating['user_verified'] == 1 OR $this->settings['reg_auth_type'] == 'admin' )
						{
							$revalidateUrl = 'ADMIN_VALIDATION';
						}
						else
						{
							$revalidateUrl = ipsRegistry::getClass('output')->buildUrl( 'app=core&amp;module=global&amp;section=register&amp;do=reval', 'public' );
						}
					}
				}
			
				echo json_encode( array(
					'connect_status'		=> $statusCode,
					'connect_id'			=> $member['member_id'],
					'connect_username'		=> $member['name'],
					'connect_displayname'	=> $member['members_display_name'],
					'connect_email'			=> $member['email'],
					'connect_revalidate_url'=> $revalidateUrl,
					) );
				exit;
			}
		}
		
		echo json_encode( array( 'connect_status' => 'FAIL' ) );
		exit;
	}
	
	/**
	 * Check data
	 *
	 * @param	string	Key - this can be anything which is known only to the applications. Never reveal this key publically.
	 *					For IPS Community Suite installs, this key can be obtained in the Login Management page in the ACP
	 * @param	int		If provided, do not throw an error if the "existing user" is the user with this ID
	 * @param	string	Username
	 * @param	string	Display Name
	 * @param	string	Email address
	 * @return	void	Outputs to screen a JSON object with four properties (status, username, displayname, email) - 'status' will say "SUCCESS" - the remainding 3 properties will each contain a boolean value, or NULL if no value was provided.
	 *					The boolean value indicates if it is OK to register a new account with that data (this may be because there is no existing user with that, or the app allows duplicates of that data)
	 *					If the key is incorrect - 'status' will be "BAD_KEY" and the remaining 3 parameters will all be NULL.
	 */
	public function check( $key, $id, $username, $displayname, $email )
	{
		$return = array( 'username' => NULL, 'displayname' => NULL, 'email' => NULL );
		
		$member = array();
		if ( $id )
		{
			$member = IPSMember::load( $id );
		}
	
		if ( $key != $this->masterKey )
		{
			echo json_encode( array_merge( array( 'status' => 'BAD_KEY' ), $return ) );
			exit;
		}
		
		if ( $username )
		{
			$return['username'] = IPSMember::getFunction()->checkNameExists( $username, $member, 'name', TRUE ) ? FALSE : TRUE;
		}
		
		if ( $displayname )
		{
			$return['displayname'] = IPSMember::getFunction()->checkNameExists( $displayname, $member, 'members_display_name', TRUE ) ? FALSE : TRUE;
		}
		
		if ( $email )
		{
			$return['email'] = IPSMember::checkByEmail( $email ) ? FALSE : TRUE;
		}
		
		echo json_encode( array_merge( array( 'status' => 'SUCCESS' ), $return ) );
		exit;
	}
	
	/**
	 * Change account data
	 *
	 * @param	int		ID number
	 * @param	string	md5( IPS Connect Key (see login method) . ID number )
	 * @param	string	New username (blank means do not change)
	 * @param	string	New displayname (blank means do not change)
	 * @param	string	New email address (blank means do not change)
	 * @param	string	New password, md5 encoded (blank means do not change)
	 * @param	string	Redirect URL, Base64 encoded
	 * @param	string	md5( IPS Connect Key . $redirect )
	 * @return	mixed	If the redirect URL is provided, this function should redirect the user to that URL with a single paramater - 'status'
	 *					If blank, will output to screen a JSON object with the same parameter
	 *					Values:
	 *						BAD_KEY				Invalid Key
	 *						NO_USER				ID number not match any member account
	 *						SUCCESS				Information changed successfully
	 *						USERNAME_IN_USE		The chosen username was in use and as a result NO information was changed
	 *						DISPLAYNAME_IN_USE	The chosen username was in use and as a result NO information was changed
	 *						EMAIL_IN_USE		The chosen username was in use and as a result NO information was changed
	 *						MISSING_DATA		No details to be changed were provided
	 */
	public function change( $id, $key, $username, $displayname, $email, $md5Password, $redirect, $redirectHash )
	{
		if ( $key != md5( $this->masterKey . $id ) )
		{
			$this->_return( base64_encode( $this->settings['board_url'] ), array( 'status' => 'BAD_KEY' ) );
		}
	
		$member = IPSMember::load( intval( $id ), 'none', 'id' );
		if ( !$member['member_id'] )
		{
			$this->_return( $redirect, array( 'status' => 'NO_USER' ) );
		}
		
		$update = array();
		if ( $username )
		{
			if ( IPSMember::getFunction()->checkNameExists( $username, $member, 'name', TRUE ) )
			{
				$this->_return( $redirect, array( 'status' => 'USERNAME_IN_USE' ) );
			}
			
			$update['name'] = $username;
		}
		
		if ( $displayname )
		{
			if ( IPSMember::getFunction()->checkNameExists( $displayname, $member, 'members_display_name', TRUE ) )
			{
				$this->_return( $redirect, array( 'status' => 'DISPLAYNAME_IN_USE' ) );
			}
			
			$update['members_display_name'] = $displayname;
		}
		
		if ( $email )
		{
			if ( IPSMember::checkByEmail( $email ) )
			{
				$this->_return( $redirect, array( 'status' => 'EMAIL_IN_USE' ) );
			}
			
			$update['email'] = $email;
		}
		
		if ( empty( $update ) )
		{
			if ( !$md5Password )
			{
				$this->_return( $redirect, array( 'status' => 'MISSING_DATA' ) );
			}
		}
		else
		{
			IPSMember::save( $member['member_id'], array( 'members' => $update ) );
		}
		
		if ( $md5Password )
		{
			IPSMember::updatePassword( $member['member_id'], $md5Password );
		}
		
		if ( $redirect )
		{
			$redirect = ( $redirectHash == md5( $this->masterKey . $redirect ) ) ? $redirect : base64_encode( $this->settings['board_url'] );
		}
		$this->_return( $redirect, array( 'status' => 'SUCCESS' ) );
		
	}
	
	/**
	 * Account is validated
	 *
	 * @param	int		ID number
	 * @param	string	md5( IPS Connect Key (see login method) . ID number )
	 */
	public function validate( $id, $key )
	{
		if ( $key != md5( $this->masterKey . $id ) )
		{
			$this->_return( base64_encode( $this->settings['board_url'] ), array( 'status' => 'BAD_KEY' ) );
		}
	
		$member = IPSMember::load( intval( $id ), 'none', 'id' );
		if ( !$member['member_id'] )
		{
			$this->_return( $redirect, array( 'status' => 'NO_USER' ) );
		}
		
		if ( $member['member_group_id'] == ipsRegistry::$settings['auth_group'] )
		{
			IPSMember::save( $member['member_id'], array( 'members' => array( 'member_group_id' => ipsRegistry::$settings['member_group'], 'ipsconnect_revalidate_url' => '' ) ) );
		}
		ipsRegistry::DB()->delete( 'validating', "member_id={$member['member_id']} and new_reg=1" );
		
		$this->_return( $redirect, array( 'status' => 'SUCCESS' ) );
	}
	
	/**
	 * Delete account(s)
	 *
	 * @param	array	ID Numbers
	 * @param	string	md5(  IPS Connect Key (see login method) . json_encode( ID number ) )
	 */
	public function delete( $ids, $key )
	{
		if ( $key != md5( $this->masterKey . json_encode( $ids ) ) )
		{
			$this->_return( base64_encode( $this->settings['board_url'] ), array( 'status' => 'BAD_KEY' ) );
		}
		
		IPSMember::remove( $ids );
		
		$this->_return( $redirect, array( 'status' => 'SUCCESS' ) );
	}
	
	/**
	 * Handle redirect / output
	 *
	 * @param	string	Redirect URL, Base64 encoded
	 * @param	array	Params
	 * @return	null	Outputs to screen or redirects
	 */
	protected function _return( $redirect, $params=array() )
	{
		if ( $redirect )
		{
			$this->registry->output->silentRedirect( base64_decode( $redirect ) . ( $_REQUEST['noparams'] ? '' : ( '&' . http_build_query( $params ) ) ) );
			exit;
		}
		else
		{
			if ( !empty( $params ) )
			{
				echo json_encode( $params );
			}
			exit;
		}
	}
	
	/**
	 * Run custom actions
	 *
	 * @param	string	method
	 * @param	array	params
	 */
	protected function _runCustom( $method, $params )
	{
		if ( file_exists( './custom.php' ) )
		{
			require_once './custom.php';
			if ( class_exists( 'ipsConnect_custom' ) )
			{
				$custom = new ipsConnect_custom( $this->registry );
				if ( method_exists( $custom, $method ) )
				{
					call_user_func_array( array( $custom, $method ), $params );
				}
			}
		}
	}
	
}

/**
 *
 * Map - can modify to add additional parameters, but the IPS Community Suite will only send the defaults
 *
 */
$map = array(
	'login'		=> array( 'idType', 'id', 'password', 'key', 'redirect', 'redirectHash' ),
	'logout'	=> array( 'id', 'key', 'redirect', 'redirectHash' ),
	'register'	=> array( 'key', 'username', 'displayname', 'password', 'email', 'revalidateurl' ),
	'cookies'	=> array( 'data' ),
	'check'		=> array( 'key', 'id', 'username', 'displayname', 'email' ),
	'change'	=> array( 'id', 'key', 'username', 'displayname', 'email', 'password', 'redirect', 'redirectHash' ),
	'validate'	=> array( 'id', 'key' ),
	'delete'	=> array( 'id', 'key' )
	);

/**
 *
 * Process Logic - do not modify
 *
 */ 
$ipsConnect = new ipsConnect();
if ( isset( $_REQUEST['act'] ) and isset( $map[ $_REQUEST['act'] ] ) )
{
	$params = array();
	foreach ( $map[ $_REQUEST['act'] ] as $k )
	{
		$params[ $k ] = $_REQUEST[ $k ];
	}

	call_user_func_array( array( $ipsConnect, $_REQUEST['act'] ), $params );
}

exit;