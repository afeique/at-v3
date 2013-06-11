<?php
/**
 * @file		emailLists.php		Members Email Lists Extension
 *
 * $Copyright: $
 * $License: $
 * $Author: mark $
 * $LastChangedDate: 2012-06-20 05:50:23 -0400 (Wed, 20 Jun 2012) $
 * $Revision: 10952 $
 * @since 		11th June 2012
 */

/**
 *
 * @class	emailLists_members
 * @brief	Members Email Lists Extension
 *
 */
class emailLists_members
{
	/**
	 * Get Form Data
	 *
	 * @return	array
	 */
	public function getFormData( $current )
	{
		/* Get groups */
		$groups = array();
		foreach ( ipsRegistry::cache()->getCache('group_cache') as $g )
		{
			$groups[] = array( $g['g_id'], $g['g_title'] );
		}
			
		/* Return */
		return array(
			'group' => array(
				"Is in group",
				ipsRegistry::getClass('output')->formMultiDropdown( 'members-group[]', $groups, ( empty( $current ) ) ? array() : $current['group'] )
				),
				
			'last_visit' => array(
				"Last Visit Was",
				ipsRegistry::getClass('output')->formDropdown( 'members-last_visit_1', array( array( 'l', "Less Than" ), array( 'e', "Equal to" ), array( 'g', "Greater Than" ) ), ( empty( $current ) ) ? 'l' : $current['last_visit_1'] ) .
				ipsRegistry::getClass('output')->formSimpleInput( 'members-last_visit_2', ( empty( $current ) ) ? '' : $current['last_visit_2'] ) . 
				" days ago"
				),
				
			'joined' => array(
				"Joined",
				ipsRegistry::getClass('output')->formDropdown( 'members-joined_1', array( array( 'l', "Less Than" ), array( 'e', "Equal to" ), array( 'g', "Greater Than" ) ), ( empty( $current ) ) ? 'l' : $current['joined_1'] ) .
				ipsRegistry::getClass('output')->formSimpleInput( 'members-joined_2', ( empty( $current ) ) ? '' : $current['joined_2'] ) . 
				" days ago"
				),

			
			);
	}
	
	/**
	 * Get Query Data
	 *
	 * @param	array	Rules
	 * @return	array	Query Data
	 */
	public function getQueryData( $rules )
	{
		$return = array();
		
		/* Member Group */
		if ( !empty( $rules['group'] ) )
		{
			$return['where'][] = ipsRegistry::DB()->buildWherePermission( $rules['group'], 'members.member_group_id', FALSE );
		}
		
		/* Last Visit */
		if ( $rules['last_visit_2'] !== '' )
		{
			switch ( $rules['last_visit_1'] )
			{
				case 'l':
					$return['where'][] = "( members.last_visit<{$rules['last_visit_2']} )";
					break;
					
				case 'g':
					$return['where'][] = "( members.last_visit>{$rules['last_visit_2']} )";
					break;
					
				case 'w':
					$return['where'][] = "( members.last_visit={$rules['last_visit_2']} )";
					break;
			}
		}
		
		/* Join Date */
		if ( $rules['joined_2'] !== '' )
		{
			switch ( $rules['joined_1'] )
			{
				case 'l':
					$return['where'][] = "( members.joined<{$rules['joined_2']} )";
					break;
					
				case 'g':
					$return['where'][] = "( members.joined>{$rules['joined_2']} )";
					break;
					
				case 'w':
					$return['where'][] = "( members.joined={$rules['joined_2']} )";
					break;
			}
		}

		
		return $return;
	}
}