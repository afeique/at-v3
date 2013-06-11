<?php
/**
 * @file		twitter.php 	Community Enhancements - IPS Backup
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mark $
 * @since		24 July 2012
 * $LastChangedDate: 2012-06-20 10:50:23 +0100 (Wed, 20 Jun 2012) $
 * @version		v3.4.5
 * $Revision: 10952 $
 */


//-----------------------------------------
// admin_core_applications_enhancements::manage()
// contains code to disable this enhancement
// from showing right now
//-----------------------------------------



/**
 *
 * @class		enhancements_core_ipsbackup
 * @brief		Community Enhancements - IPS Backup
 */
class enhancements_core_ipsbackup
{
	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{		
		$this->title = 'IPS Backup Service';
		$this->description = 'Automatically backup your community.';
		$this->icon = '';
		$this->enabled = FALSE;
	}
}