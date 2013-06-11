<?php
/**
 * @file		bulkMailFilter.php		Abstract Bulk Mail Filter Class
 *
 * $Copyright: $
 * $License: $
 * $Author: mark $
 * $LastChangedDate: 2012-02-13 05:07:16 -0500 (Mon, 13 Feb 2012) $
 * $Revision: 10290 $
 * @since 		23rd August 2012
 */

abstract class bulkMailFilter
{
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry )
	{
		$this->registry		=  $registry;
		$this->DB			=  $this->registry->DB();
		$this->settings		=& $this->registry->fetchSettings();
		$this->request		=& $this->registry->fetchRequest();
		$this->lang			=  $this->registry->getClass('class_localization');
		$this->member		=  $this->registry->member();
		$this->memberData	=& $this->registry->member()->fetchMemberData();
		$this->cache		=  $this->registry->cache();
		$this->caches		=& $this->registry->cache()->fetchCaches();
	}
	
	/** 
	 * Get Setting Field
	 *
	 * @param	mixed	Value as returned by the save method
	 * @return	string	HTML to show in ACP when adding a reminder that will allow admins to set up a reminder
	 */
	abstract public function getSettingField( $criteria );
	
	/**
	 * Save Setting Field
	 *
	 * @param	array		POST data from the form using the form elements provided in the getSettingField method
	 * @return	mixed		If this criteria should be ignored, return FALSE. Otherwise, return whatever data you will need in your
	 *						getMembers method to fetch members that match the chosen criteria
	 * @throws	Exception	You can throw an Exception with the message being the error to display if the user has provided invalid data
	 */
	abstract public function save( $post );
	
	/**
	 * Get Members
	 *
	 * @param	mixed	Whatever data was returned by save method
	 * @return	array	Array with two elements:
	 *						'joins' should be an array of any additional tables to join (as per ipsRegistry::DB()->build)
	 *						'where' should be an array of where clauses
	 *					The table 'members' is already available with the prefix 'm'
	 */
	abstract public function getMembers( $data );
}