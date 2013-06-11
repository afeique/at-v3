<?php
/**
 * @file		archive.php 	Task to check and write incremental archives - Matt Mecham
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: bfarber $
 * @since		-
 * $LastChangedDate: 2011-05-13 03:28:10 +0100 (Fri, 13 May 2011) $
 * @version		v3.4.5
 * $Revision: 8754 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 *
 * @class		task_item
 * @brief		Task to update the topic views from the temporary table
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
	 * @var		$DB
	 * @var		$settings
	 * @var		$lang
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $lang;
	
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
		$this->DB		= $this->registry->DB();
		$this->settings	=& $this->registry->fetchSettings();
		$this->lang		= $this->registry->getClass('class_localization');
		
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
		if ( defined( IPS_BACK_UP_ENABLED ) && IPS_BACK_UP_ENABLED )
		{
			/* Language class */
			$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_global' ), 'core' );
			
			/* Load back up class */
			require_once( IPS_ROOT_PATH . 'sources/classes/backup.php' );
			$backup    = new ipsBackup();
			
			/* Set default limits for the batch pulling - will want to raise for production */
			$backup->setLimits( 'rows', 1500 );
			$backup->setLimits( 'bytes', 10 * 1024 * 1024 );
			
			$backup->sendBatch();
			
			if ( $count )
			{
				$this->class->appendTaskLog( $this->task, "Back up batch sent" );
			}
		}
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}
}