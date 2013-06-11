<?php
/**
 * @file		bulkMailFilters.php		Bulk Mail Filters - Members App
 *
 * $Copyright: $
 * $License: $
 * $Author: mark $
 * $LastChangedDate: 2012-02-13 05:07:16 -0500 (Mon, 13 Feb 2012) $
 * $Revision: 10290 $
 * @since 		23rd August 2012
 */

/**
 *
 * @class	bulkMailFilters_members
 * @brief	Says what criteria is available for this application
 *
 */
class bulkMailFilters_members
{
	public $filters = array( 'group', 'joined', 'last_visit' );
}

/**
 *
 * @class	bulkMailFilter_members_group
 * @brief	Filter: Group
 *
 */
class bulkMailFilter_members_group extends bulkMailFilter
{
	/** 
	 * Get Setting Field
	 *
	 * @param	mixed	Value as returned by the save method
	 * @return	string	HTML to show in ACP when adding a reminder that will allow admins to set up a reminder
	 */
	public function getSettingField( $criteria )
	{	
		$groups = array();
		foreach ( $this->caches['group_cache'] as $gid => $group )
		{
			$groups[] = array( $gid, $group['g_title'] );
		}
	
		$selectBox = ipsRegistry::getClass('output')->formMultiDropdown( 'bmf_members_groups[]', $groups, isset( $criteria['groups'] ) ? explode( ',', $criteria['groups'] ) : $_POST['bmf_members_groups'] );
		$secondaryCheck = ipsRegistry::getClass('output')->formCheckbox( 'bmf_members_groups_secondary', isset( $criteria['secondary'] ) ? $criteria['secondary'] : $_POST['bmf_members_groups_secondary'] );
		
		return "{$selectBox}<br /><span class='desctext'>{$this->lang->words['bulkMailFilter_members_group_desc']}</span><br /><br />{$secondaryCheck} {$this->lang->words['bulkMailFilter_members_group_secondary']}";
	}
	
	/**
	 * Save Setting Field
	 *
	 * @param	array		POST data from the form using the form elements provided in the getSettingField method
	 * @return	mixed		If this criteria should be ignored, return FALSE. Otherwise, return whatever data you will need in your
	 *						getMembers method to fetch members that match the chosen criteria
	 * @throws	Exception	You can throw an Exception with the message being the error to display if the user has provided invalid data
	 */
	public function save( $post )
	{	
		if ( !$post['bmf_members_groups'] )
		{
			return false;
		}
		else
		{
			return array( 'groups' => implode( ',', $post['bmf_members_groups'] ), 'secondary' => (bool) $post['bmf_members_groups_secondary'] );
		}
	}
	
	/**
	 * Get Members
	 *
	 * @param	mixed	Whatever data was returned by save method
	 * @return	array	Array with two elements:
	 *						'joins' should be an array of any additional tables to join (as per ipsRegistry::DB()->build)
	 *						'where' should be an array of where clauses
	 *					The table 'members' is already available with the prefix 'm'
	 */
	public function getMembers( $data )
	{
		if ( $data['groups'] )
		{
			$return = $this->DB->buildWherePermission( explode( ',', $data['groups'] ), 'member_group_id', FALSE );
			
			if ( $data['secondary'] )
			{
				$return = "( {$return} OR " . $this->DB->buildWherePermission( explode( ',', $data['groups'] ), 'mgroup_others', FALSE ) . ' )';
			}
			
			return array( 'where' => array( $return ) );
		}
	}
}


/**
 *
 * @class	bulkMailFilters_members
 * @brief	Filter: Joined Date
 *
 */
class bulkMailFilter_members_joined extends bulkMailFilter
{
	/** 
	 * Get Setting Field
	 *
	 * @param	mixed	Value as returned by the save method
	 * @return	string	HTML to show in ACP when adding a reminder that will allow admins to set up a reminder
	 */
	public function getSettingField( $criteria )
	{	
		$selectBox = ipsRegistry::getClass('output')->formDropdown( 'bmf_members_joined_type', array(
			array( '', $this->lang->words['bmf_ignore'] ),
			array( 'b', $this->lang->words['bmf_before'] ),
			array( 'a', $this->lang->words['bmf_after'] )
			), isset( $criteria['type'] ) ? $criteria['type'] : $_POST['bmf_members_joined_type'] );
		
		$selectedDate = isset( $criteria['date'] ) ? $criteria['date'] : time();
		
		$dates = array();
		foreach ( range( 1, 31 ) as $d )
		{
			$dates[] = array( $d, $d );
		}
		$dateBox = ipsRegistry::getClass('output')->formDropdown( 'bmf_members_joined_d', $dates, isset( $_POST['bmf_members_joined_d'] ) ? $_POST['bmf_members_joined_d'] : date( 'j', $selectedDate ) );
		
		$months = array();
		foreach( range( 1, 12 ) as $m )
		{
			$months[] = array( $m, date( 'F', mktime( 0, 0, 0, $m, 1, 2000 ) ) );
		}
		$monthBox = ipsRegistry::getClass('output')->formDropdown( 'bmf_members_joined_m', $months, isset( $_POST['bmf_members_joined_m'] ) ? $_POST['bmf_members_joined_m'] : date( 'n', $selectedDate ) );
		
		$yearBox = ipsRegistry::getClass('output')->formSimpleInput( 'bmf_members_joined_y', isset( $_POST['bmf_members_joined_y'] ) ? $_POST['bmf_members_joined_y'] : date( 'Y', $selectedDate ) );
		
		return "{$selectBox} {$dateBox} {$monthBox} {$yearBox}";
	}
	
	/**
	 * Save Setting Field
	 *
	 * @param	array		POST data from the form using the form elements provided in the getSettingField method
	 * @return	mixed		If this criteria should be ignored, return FALSE. Otherwise, return whatever data you will need in your
	 *						getMembers method to fetch members that match the chosen criteria
	 * @throws	Exception	You can throw an Exception with the message being the error to display if the user has provided invalid data
	 */
	public function save( $post )
	{
		if ( !$post['bmf_members_joined_type'] )
		{
			return false;
		}
		else
		{
			$date = mktime( 0, 0, 0, $post['bmf_members_joined_m'], $post['bmf_members_joined_d'], $post['bmf_members_joined_y'] );
			
			if ( !checkdate( $post['bmf_members_joined_m'], $post['bmf_members_joined_d'], $post['bmf_members_joined_y'] ) or $date === FALSE or $date === -1 )
			{
				throw new Exception( $this->lang->words['bulkMailFilter_members_joined_error'] );
			}
		
			return array( 'type' => $post['bmf_members_joined_type'], 'date' => $date );
		}
	}
	
	/**
	 * Get Members
	 *
	 * @param	mixed	Whatever data was returned by save method
	 * @return	array	Array with two elements:
	 *						'joins' should be an array of any additional tables to join (as per ipsRegistry::DB()->build)
	 *						'where' should be an array of where clauses
	 *					The table 'members' is already available with the prefix 'm'
	 */
	public function getMembers( $data )
	{
		switch ( $data['type'] )
		{
			case 'a':
				return array( 'where' => array( "(m.joined>{$data['date']})" ) );
				break;
				
			case 'b':
				return array( 'where' => array( "(m.joined<{$data['date']})" ) );
				break;
		}
	}
}


/**
 *
 * @class	bulkMailFilters_members
 * @brief	Filter: Last Visit Date
 *
 */
class bulkMailFilter_members_last_visit extends bulkMailFilter
{
	/** 
	 * Get Setting Field
	 *
	 * @param	array
	 * @return	string	HTML to show in ACP when adding a reminder that will allow admins to set up a reminder
	 */
	public function getSettingField( $criteria )
	{	
		$selectBox = ipsRegistry::getClass('output')->formDropdown( 'bmf_members_last_visit_type', array(
			array( '', $this->lang->words['bmf_ignore'] ),
			array( 'b', $this->lang->words['bmf_before'] ),
			array( 'a', $this->lang->words['bmf_after'] )
			), isset( $criteria['type'] ) ? $criteria['type'] : $_POST['bmf_members_last_visit_type'] );
			
		$selectedDate = isset( $criteria['date'] ) ? $criteria['date'] : time();
		
		$dates = array();
		foreach ( range( 1, 31 ) as $d )
		{
			$dates[] = array( $d, $d );
		}
		$dateBox = ipsRegistry::getClass('output')->formDropdown( 'bmf_members_last_visit_d', $dates, isset( $_POST['bmf_members_last_visit_d'] ) ? $_POST['bmf_members_last_visit_d'] : date( 'j', $selectedDate ) );
		
		$months = array();
		foreach( range( 1, 12 ) as $m )
		{
			$months[] = array( $m, date( 'F', mktime( 0, 0, 0, $m, 1, 2000 ) ) );
		}
		$monthBox = ipsRegistry::getClass('output')->formDropdown( 'bmf_members_last_visit_m', $months, isset( $_POST['bmf_members_last_visit_m'] ) ? $_POST['bmf_members_last_visit_m'] : date( 'n', $selectedDate ) );
		
		$yearBox = ipsRegistry::getClass('output')->formSimpleInput( 'bmf_members_last_visit_y', isset( $_POST['bmf_members_last_visit_y'] ) ? $_POST['bmf_members_last_visit_y'] : date( 'Y', $selectedDate ) );
		
		return "{$selectBox} {$dateBox} {$monthBox} {$yearBox}";
		
	}
	
	/**
	 * Save Setting Field
	 *
	 * @param	array		POST data from the form using the form elements provided in the getSettingField method
	 * @return	mixed		If this criteria should be ignored, return FALSE. Otherwise, return whatever data you will need in your
	 *						getMembers method to fetch members that match the chosen criteria
	 * @throws	Exception	You can throw an Exception with the message being the error to display if the user has provided invalid data
	 */
	public function save( $post )
	{
		if ( !$post['bmf_members_last_visit_type'] )
		{
			return false;
		}
		else
		{
			$date = mktime( 0, 0, 0, $post['bmf_members_last_visit_m'], $post['bmf_members_last_visit_d'], $post['bmf_members_last_visit_y'] );
			
			if ( !checkdate( $post['bmf_members_last_visit_m'], $post['bmf_members_last_visit_d'], $post['bmf_members_last_visit_y'] ) or $date === FALSE or $date === -1 )
			{
				throw new Exception( $this->lang->words['bulkMailFilter_members_last_visit_error'] );
			}
		
			return array( 'type' => $post['bmf_members_last_visit_type'], 'date' => $date );
		}
	}
	
	/**
	 * Get Members
	 *
	 * @param	mixed	Whatever data was returned by save method
	 * @return	array	Array with two elements:
	 *						'joins' should be an array of any additional tables to join (as per ipsRegistry::DB()->build)
	 *						'where' should be an array of where clauses
	 *					The table 'members' is already available with the prefix 'm'
	 */
	public function getMembers( $data )
	{
		switch ( $data['type'] )
		{
			case 'a':
				return array( 'where' => array( "(m.last_visit>{$data['date']})" ) );
				break;
				
			case 'b':
				return array( 'where' => array( "(m.last_visit<{$data['date']})" ) );
				break;
		}
	}
}