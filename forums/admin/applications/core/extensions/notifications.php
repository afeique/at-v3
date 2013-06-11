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
 * @subpackage	Core
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



class core_notifications
{
	public function getConfiguration()
	{
		/**
		 * Notification types - Needs to be a method so when require_once is used, $_NOTIFY isn't empty
		 */
		$_NOTIFY	= array(
							// Future...
							// array( 'key' => 'reputation_received', 'default' => array( 'inline' ), 'disabled' => array() ),
							array( 'key' => 'report_center', 'default' => array( 'email' ), 'disabled' => array(), 'show_callback' => TRUE, 'icon' => 'notify_reportcenter' ),
							array( 'key' => 'new_comment'  , 'default' => array( 'email' ), 'disabled' => array(), 'show_callback' => false, 'icon' => 'notify_profilecomment' ),
							);/*noLibHook*/
		return $_NOTIFY;
	}
	
	public function report_center( $member )
	{
		return $member['access_report_center'];
	}
}