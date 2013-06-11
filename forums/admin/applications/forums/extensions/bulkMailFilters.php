<?php
/**
 * @file		bulkMailFilters.php		Bulk Mail Filters - Forums App
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
 * @class	bulkMailFilters_forums
 * @brief	Says what criteria is available for this application
 *
 */
class bulkMailFilters_forums
{
	public $filters = array( 'posts' );
}

/**
 *
 * @class	bulkMailFilter_forums_posts
 * @brief	Filter: Post Count
 *
 */
class bulkMailFilter_forums_posts extends bulkMailFilter
{
	/** 
	 * Get Setting Field
	 *
	 * @param	mixed	Value as returned by the save method
	 * @return	string	HTML to show in ACP when adding a reminder that will allow admins to set up a reminder
	 */
	public function getSettingField( $criteria )
	{	
		$selectBox = ipsRegistry::getClass('output')->formDropdown( 'bmf_forums_posts_type', array(
			array( '', $this->lang->words['bmf_ignore'] ),
			array( 'l', $this->lang->words['bmf_less'] ),
			array( 'e', $this->lang->words['bmf_exactly'] ),
			array( 'g', $this->lang->words['bmf_more'] )
			), isset( $criteria['type'] ) ? $criteria['type'] : $_POST['bmf_forums_posts_type'] );

		$inputBox = ipsRegistry::getClass('output')->formSimpleInput( 'bmf_forums_posts_number', isset( $_POST['bmf_forums_posts_number'] ) ? $_POST['bmf_forums_posts_number'] : isset( $criteria['number'] ) ? $criteria['number'] : '' );
		
		return "{$selectBox} {$inputBox}";
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
		if ( !$post['bmf_forums_posts_type'] )
		{
			return false;
		}
		else
		{
			return array( 'type' => $post['bmf_forums_posts_type'], 'number' => $post['bmf_forums_posts_number'] );
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
		$number = intval($data['number']);
		
		switch ( $data['type'] )
		{
			case 'g':
				return array( 'where' => array( "(m.posts>{$number})" ) );
				break;
				
			case 'e':
				return array( 'where' => array( "(m.posts={$number})" ) );
				break;
				
			case 'l':
				return array( 'where' => array( "(m.posts<{$number})" ) );
				break;
		}
	}
}