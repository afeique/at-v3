<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Admin Bookmarks Model
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 * </pre>
 *
 * @author 		Matt Mecham
 * @copyright	(c) 2001 - 2012 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		Tue. 7th August 2012
 * @version		$Rev: 10721 $
 *
 */

/**
 * Bookmarks model
 * @author matt
 *
 */
class classes_admin_bookmarks
{
	private $Member    = array();
	private $Bookmarks = array();
	
	
	/**
	 * Construct
	 */
	public function __construct()
	{
		/* Make objects */
		$this->registry = ipsRegistry::instance();
		$this->DB	    = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang	    = $this->registry->getClass('class_localization');
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
		
		if ( $this->memberData['member_id'] )
		{
			$this->setMember( $this->memberData );
		}
	}
	
	/**
	 * Set a member for the scope of this class
	 * @param	mixed
	 */
	public function setMember( $member )
	{
		if ( is_numeric( $member ) )
		{
			$this->Member = IPSMember::load( $member );
		}
		else if ( is_array( $member ) && ! empty( $member['member_id'] ) )
		{
			$this->Member = $member;
		}
		else
		{
			trigger_error("Member data incorrect", E_USER_ERROR); 
		}
		
		/* Load bookmarks */		
		$bookmarks = array();
		
		$this->DB->build( array( 'select' => '*',
														 'from'   => 'core_sys_bookmarks',
														 'where'  => 'bookmark_member_id=' . intval( $this->Member['member_id'] ),
														 'order'  => 'bookmark_pos ASC' ) );	
		$b = $this->DB->execute();
		
		while( $row = $this->DB->fetch( $b ) )
		{
			$bookmarks[$row['bookmark_id']] = $row;
		}
		
		$this->Bookmarks = $bookmarks;
	}
	
	/**
	 * Get the home URL
	 */
	public function getHomeUrl()
	{
		$url = false;
		
		if ( is_array( $this->Bookmarks ) && count( $this->Bookmarks ) )
		{
			foreach( $this->Bookmarks as $id => $data )
			{
				if ( $data['bookmark_home'] )
				{
					$url = $data['bookmark_url'];
					break;
				}
			}
		}
		
		return $url;
	}
	
	/**
	 * Update the bookmark
	 * @param	int		Id
	 * @param	array	array( title, position )
	 */
	public function update( $id, $data=array() )
	{
		if ( $id && count( $data ) )
		{
			$save = array();
			
			if ( ! empty( $data['title'] ) )
			{
				$save['bookmark_title'] = $this->DB->addSlashes( $data['title'] );
			}
			
			if ( isset( $data['position'] ) )
			{
				$save['bookmark_pos'] = intval( $data['position'] );
			}
			
			$this->DB->update( 'core_sys_bookmarks', $save, 'bookmark_id=' . intval( $id ) . ' AND bookmark_member_id=' . $this->Member['member_id'] );
		}
		
		$this->setMember( $this->Member );
	}
	
	/**
	 * Get bookmarks
	 * @param	int
	 */
	public function getBookmarks()
	{
		return $this->Bookmarks;
	}
	
	/**
	 * Remove a bookmark
	 * @param   int     bookmark id
	 */
	public function removeBookmark( $id )
	{
		$this->DB->delete( 'core_sys_bookmarks', 'bookmark_member_id=' . $this->Member['member_id'] . ' AND bookmark_id=' . intval( $id ) );
	
		/* Reset stored markers */
		$this->setMember( $this->Member );
	}
	
	/**
	 * Add a bookmark
	 * @param   int     memberId
	 * @param	string	URL
	 * @param	string  Title
	 * @param	boolean	asHome
	 */
	public function addBookmark( $url, $title, $asHome=false )
	{
		$url = $this->cleanUrl( $url );
		
		if ( ! $url || ! $title )
		{
			throw new Exception("Data missing");
		}
		
		/* Already stored? */
		if ( $this->hasBookmarked( $url ) )
		{
			return true;
		}
		
		$this->DB->insert( 'core_sys_bookmarks', array( 'bookmark_member_id' => $this->Member['member_id'],
														'bookmark_title'     => $title,
														'bookmark_url'		 => $url,
														'bookmark_home'      => $asHome,
														'bookmark_pos'		 => count( $this->Bookmarks ) ) );
	
		$insertId = $this->DB->getInsertId();
		
		if ( $asHome )
		{
			$this->setAsHome( $insertId );
		}
		
		/* Reset stored markers */
		$this->setMember( $this->Member );
	}
	
	/**
	 * Set an ID as home
	 * @param int $id
	 */
	public function setAsHome( $id )
	{
		$this->DB->update( 'core_sys_bookmarks', array( 'bookmark_home' => 0 ), 'bookmark_member_id=' . $this->Member['member_id'] );
		
		if ( ! empty( $id ) )
		{
			$this->DB->update( 'core_sys_bookmarks', array( 'bookmark_home' => 1 ), 'bookmark_member_id=' . $this->Member['member_id'] . ' AND bookmark_id=' . intval( $id ) );
		}
	}
	
	/**
	 * Test to see if the URL has already been bookmarked
	 * @param string $url
	 * @return boolean
	 */
	public function hasBookmarked( $url )
	{
		$url = $this->cleanUrl( $url );
		
		if ( is_array( $this->Bookmarks ) && count( $this->Bookmarks ) )
		{
			foreach( $this->Bookmarks as $id => $data )
			{
				if ( $data['bookmark_url'] == $url )
				{
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Return bookmarks as Bob, er, I mean, Json.
	 */
	public function asJson()
	{
		$bob = array();
		
		if ( is_array( $this->Bookmarks ) && count( $this->Bookmarks ) )
		{
			foreach( $this->Bookmarks as $id => $data )
			{
				$bob[ $id ] = array( 'url'   => $data['bookmark_url'],
									 'title' => $data['bookmark_title'],
									 'home'  => $data['bookmark_home'] );
			}
		}
		
		return json_encode( $bob );
	}
	
	/**
	 * Clean up the URL, remove encoded &amps, missing key=value pairs, etc
	 * @param	string
	 * @return	string
	 */
	public function cleanUrl( $url )
	{ 
		if ( strstr( $url, 'adsess=' ) )
		{
			preg_match( '#^(?:.+?)?adsess=(?:[\d\w]{32})(?:&amp;|&)(.*)$#', $url, $matches );
			
			if ( $matches[1] )
			{
				$url = $matches[1]; 
			}
		}
		
		$url = str_replace( '&amp;', '&', $url );
		
		parse_str( $url, $array );
		
		if ( ! count( $array ) )
		{
			return false;
		}
		
		/* Remove empty elements */
		foreach( $array as $k => $v )
		{
			if ( ! is_array( $v ) && ! strlen( $v ) )
			{
				unset( $array[ $k ] );
			}
		}
		
		return http_build_query( $array );
	}
	
}


