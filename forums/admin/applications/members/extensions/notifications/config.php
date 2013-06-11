<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Define the core notification types
 * Last Updated: $Date: 2013-05-21 20:51:17 -0400 (Tue, 21 May 2013) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 12263 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * Notification types
 */

class members_notifications
{
	public function getConfiguration()
	{
		/**
		 * Notification types - Needs to be a method so when require_once is used, $_NOTIFY isn't empty
		 */
		$_NOTIFY	= array(
							array( 'key' => 'profile_comment', 'default' => array( 'inline' ), 'disabled' => array(), 'icon' => 'notify_profilecomment' ),
							array( 'key' => 'friend_request', 'default' => array( 'inline' ), 'disabled' => array(), 'show_callback' => true, 'icon' => 'notify_friendrequest' ),
							array( 'key' => 'friend_request_approve', 'default' => array( 'inline' ), 'disabled' => array(), 'show_callback' => true, 'icon' => 'notify_friendrequest' ),
							array( 'key' => 'new_private_message', 'default' => array( 'email' ), 'disabled' => array( 'inline' ), 'icon' => 'notify_pm' ),
							array( 'key' => 'reply_private_message', 'default' => array( 'email' ), 'disabled' => array( 'inline' ), 'icon' => 'notify_pm' ),
							array( 'key' => 'invite_private_message', 'default' => array( 'email' ), 'disabled' => array( 'inline' ), 'icon' => 'notify_pm' ),
							array( 'key' => 'reply_your_status', 'default' => array(), 'disabled' => array(), 'show_callback' => true, 'icon' => 'notify_statusreply' ),
							array( 'key' => 'reply_any_status', 'default' => array(), 'disabled' => array(), 'show_callback' => true, 'icon' => 'notify_statusreply' ),
							array( 'key' => 'friend_status_update', 'default' => array(), 'disabled' => array(), 'show_callback' => true, 'icon' => 'notify_statusreply' ),
							array( 'key' => 'warning', 'default' => array( 'email' ), 'disabled' => array(), 'show_callback' => true, 'icon' => 'notify_warning' ),
							array( 'key' => 'warning_mods', 'default' => array( 'inline' ), 'disabled' => array(), 'show_callback' => true, 'icon' => 'notify_warning' ),
							);/*noLibHook*/
							
		return $_NOTIFY;
	}

	public function friend_request( $member )
	{
		return (bool) ( ipsRegistry::$settings['friends_enabled'] && $member['g_can_add_friends'] );
	}

	public function friend_request_approve( $member )
	{
		return (bool) ( ipsRegistry::$settings['friends_enabled'] && $member['g_can_add_friends'] );
	}

	public function friend_status_update( $member )
	{
		return (bool) ( ipsRegistry::$settings['su_enabled'] && ipsRegistry::$settings['friends_enabled'] && $member['g_can_add_friends'] );
	}
	
	public function reply_your_status( $member )
	{
		return (bool) ( ipsRegistry::$settings['su_enabled'] );
	}

	public function reply_any_status( $member )
	{
		return (bool) ( ipsRegistry::$settings['su_enabled'] );
	}

	public function warning( $member )
	{
		if ( !ipsRegistry::$settings['warn_on'] )
		{
			return false;
		}
		
		if ( ipsRegistry::$settings['warn_protected'] )
		{
			if ( IPSMember::isInGroup( $member, explode( ',', ipsRegistry::$settings['warn_protected'] ) ) )
			{
				return false;
			}
		}
		
		return true;
	}
	
	public function warning_mods( $member )
	{
		if ( !ipsRegistry::$settings['warn_on'] )
		{
			return FALSE;
		}
	
		if ( $member['g_is_supmod'] )
		{
			return TRUE;
		}
		elseif ( $member['is_mod'] )
		{
			$other_mgroups	= array();
			$_other_mgroups	= IPSText::cleanPermString( $member['mgroup_others'] );
			
			if( $_other_mgroups )
			{
				$other_mgroups	= explode( ",", $_other_mgroups );
			}
			
			$other_mgroups[] = $member['member_group_id'];

			ipsRegistry::DB()->build( array( 
									'select' => '*',
									'from'   => 'moderators',
									'where'  => "(member_id='" . $member['member_id'] . "' OR (is_group=1 AND group_id IN(" . implode( ",", $other_mgroups ) . ")))" 
							)	);
										  
			ipsRegistry::DB()->execute();
			
			while ( $this->moderator = ipsRegistry::DB()->fetch() )
			{
				if ( $this->moderator['allow_warn'] )
				{
					return TRUE;
				}
			}
		}
		
		return FALSE;
	}
}