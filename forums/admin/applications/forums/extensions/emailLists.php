<?php
/**
 * @file		emailLists.php		Forums Email Lists Extension
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
 * @class	emailLists_forums
 * @brief	Forums Email Lists Extension
 *
 */
class emailLists_forums
{
	/**
	 * Get Form Data
	 *
	 * @return	array
	 */
	public function getFormData( $current )
	{
		/* Return */
		return array(
			'posts' => array(
				"Number of posts is",
				ipsRegistry::getClass('output')->formDropdown( 'forums-posts_1', array( array( 'l', "Less Than" ), array( 'e', "Equal to" ), array( 'g', "Greater Than" ) ), ( empty( $current ) ) ? 'l' : $current['posts_1'] ) .
				ipsRegistry::getClass('output')->formSimpleInput( 'forums-posts_2', ( empty( $current ) ) ? '' : $current['posts_2'] )
				)
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
		
		if ( $rules['posts_2'] !== '' )
		{
			switch ( $rules['posts_1'] )
			{
				case 'l':
					$return['where'][] = "( members.posts<{$rules['posts_2']} )";
					break;
					
				case 'g':
					$return['where'][] = "( members.posts>{$rules['posts_2']} )";
					break;
					
				case 'w':
					$return['where'][] = "( members.posts={$rules['posts_2']} )";
					break;
			}
		}
		
		return $return;
	}
}