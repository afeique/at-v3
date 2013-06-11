<?php
/**
 * @file		bulkmail.php 	Task to send out bulk emails (dynamically enabled)
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: AndyMillne $
 * @since		-
 * $LastChangedDate: 2012-10-31 14:08:48 -0400 (Wed, 31 Oct 2012) $
 * @version		v3.4.5
 * $Revision: 11536 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 *
 * @class		task_item
 * @brief		Task to send out bulk emails (dynamically enabled)
 *
 */
class task_item
{
	/**
	 * Object that stores the parent task manager class
	 *
	 * @var		$class
	 */
	protected $class;
	
	/**
	 * Array that stores the task data
	 *
	 * @var		$task
	 */
	protected $task = array();
	
	/**
	 * Registry Object Shortcuts
	 *
	 * @var		$registry
	 */
	protected $registry;
	
	/**
	 * Constructor
	 *
	 * @param	object		$registry		Registry object
	 * @param	object		$class			Task manager class object
	 * @param	array		$task			Array with the task data
	 * @return	@e void
	 */
	public function __construct( ipsRegistry $registry, $class, $task )
	{
		/* Make registry objects */
		$this->registry	= $registry;
		
		$this->class	= $class;
		$this->task		= $task;
	}
	
	/**
	 * Run this task
	 *
	 * @return	@e void
	 */
	public function runTask()
	{
		//-----------------------------------------
		// Load Bulk Mailer thing
		//-----------------------------------------

		define( 'IN_ACP', 1 );
		
		require_once IPSLib::getAppDir('members') . '/sources/classes/bulkMailFilter.php';
		
		$classToLoad = IPSLib::loadActionOverloader( IPSLib::getAppDir( 'members' ) . '/modules_admin/bulkmail/bulkmail.php', 'admin_members_bulkmail_bulkmail' );
		$bulkmail    = new $classToLoad();
		$bulkmail->makeRegistryShortcuts( $this->registry );
		$bulkmail->mailSendProcess();
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}