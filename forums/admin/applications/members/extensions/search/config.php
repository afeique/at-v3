<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Config file
 * Last Updated: $Date: 2013-04-15 17:26:30 -0400 (Mon, 15 Apr 2013) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 12179 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

$memberData = ipsRegistry::member()->fetchMemberData();

/* Can search with this app */
$CONFIG['can_search']			= ( $memberData['g_mem_info'] ) ? 1 : 0;

/* Can view new content with this app */
$CONFIG['can_viewNewContent']	= ( $memberData['g_mem_info'] ) ? 1 : 0;
$CONFIG['can_vnc_filter_by_followed']	= 0;
$CONFIG['can_vnc_unread_content']		= 0;

/* Can fetch user generated content */
$CONFIG['can_userContent']		= ( $memberData['g_mem_info'] ) ? 1 : 0;

/* Content types, put the default one first */
if( $_REQUEST['do'] == 'user_activity' )
{
	$CONFIG['contentTypes']			= array( 'comments' );
}
else
{
	$CONFIG['contentTypes']			= array( 'members', 'comments' );
}