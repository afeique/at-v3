<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Forum permissions mappings
 * Last Updated: $Date: 2012-08-22 10:11:25 -0400 (Wed, 22 Aug 2012) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 11249 $ 
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * Member Synchronization extensions
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage  Members
 * @link		http://www.invisionpower.com
 * @version		$Rev: 11249 $ 
 */
class membersMemberSync
{
	/**
	 * Registry reference
	 *
	 * @var		object
	 */
	public $registry;
	
	/**
	 * CONSTRUCTOR
	 *
	 * @return	@e void
	 */
	public function __construct()
	{
		$this->registry = ipsRegistry::instance();
	}
	
	/**
	 * This method is run when a member is flagged as a spammer
	 *
	 * @param	array 	$member	Array of member data
	 * @return	@e void
	 */
	public function onSetAsSpammer( $member )
	{
		/* Load status class */
		if ( ! $this->registry->isClassLoaded( 'memberStatus' ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/status.php', 'memberStatus' );
			$this->registry->setClass( 'memberStatus', new $classToLoad( ipsRegistry::instance() ) );
		}
		
		/* Delete the stuff */
		$this->registry->getClass('memberStatus')->setAuthor( $member );
		$this->registry->getClass('memberStatus')->deleteAllReplies();
		$this->registry->getClass('memberStatus')->deleteAllMemberStatus();
	}
	
	/**
	 * This method is called after a member account has been removed
	 *
	 * @param	string	$ids	SQL IN() clause
	 * @return	@e void
	 */
	public function onDelete( $mids )
	{
		/* Delete Status Updates - note, we can't do this via memberStatus class, since we no longer have the member data */
		ipsRegistry::DB()->delete( 'member_status_updates', "status_member_id" . $mids );
		ipsRegistry::DB()->delete( 'member_status_actions', "action_member_id" . $mids );
		ipsRegistry::DB()->delete( 'member_status_replies', "reply_member_id"  . $mids );
	}
}