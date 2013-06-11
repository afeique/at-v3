<?php
/**
 * @file		bookmarks.php 	AJAX storage of bookmarks
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2012 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: bfarber $
 * @since		7th August 2012
 * $LastChangedDate: 2011-03-10 21:00:38 +0000 (Thu, 10 Mar 2011) $
 * @version		v3.4.5
 * $Revision: 8021 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 *
 * @class		AJAX storage of bookmarks
 * @brief		AJAX storage of tab order preference
 *
 */
class admin_core_ajax_bookmarks extends ipsAjaxCommand 
{
	private $_classBookmark;
	
	/**
	 * Main function executed automatically by the controller
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry )
	{
		$registry->getClass('class_localization')->loadLanguageFile( array( 'admin_global' ), 'core' );
		
		require_once( IPS_ROOT_PATH . 'sources/classes/admin/bookmarks.php' );
		$this->_classBookmark = new classes_admin_bookmarks();
		
		//-----------------------------------------
		// What shall we do?
		//-----------------------------------------
		
		switch( $this->request['do'] )
		{
			default:
			case 'add':
				$this->_add();
			break;
			case 'managePane':
				$this->_managePane();
			break;
			case 'delete':
				$this->_delete();
			break;
			case 'save':
				$this->_save();
			break;
		}
	}
	
	/**
	 * Save all from manage screen
	 */
	protected function _save()
	{
		$home     = intval( $this->request['home'] );
		$names    = $_POST['names'];
		$position = array();
		
		if ( is_array( $_GET['bookmark'] ) )
		{
			foreach( $_GET['bookmark'] as $id )
			{
				$position[ $id ] = count( $position ) + 1;
			}
		}
		
		if ( is_array( $names ) )
		{
			foreach( $names as $id => $name )
			{
				$this->_classBookmark->update( $id, array( 'title' => $name, 'position' => intval( $position[ $id ] ) ) );
			}
		}
		
		$this->_classBookmark->setAsHome( $home );
		
		$this->returnJsonArray( array( 'status' => 'ok', 'json' => json_decode( $this->_classBookmark->asJson(), true ) ) );
	}
	
	/**
	 * Remove a book mark
	 */
	protected function _delete()
	{
		$id = intval( $this->request['id'] );
		
		$this->_classBookmark->removeBookmark( $id );
			
		$this->returnJsonArray( array( 'status' => 'ok', 'json' => json_decode( $this->_classBookmark->asJson(), true ) ) );
	}
	
	/**
	 * Manage panel
	 */
	protected function _managePane()
	{
		$bookmarks = $this->_classBookmark->getBookmarks();
		
		/* Return it then */
		$this->returnHtml( $this->registry->output->global_template->manageBookmarks( $bookmarks ) );
	}
	
	/**
	 * Add a book mark
	 */
	protected function _add()
	{
		$title = $this->request['title'];
		$url   = $this->request['url'];
		$home  = intval( $this->request['home'] );
		
		try
		{
			$this->_classBookmark->addBookmark( $url, $title, $home );
			
			$this->returnJsonArray( array( 'status' => 'ok', 'json' => json_decode( $this->_classBookmark->asJson(), true ) ) );
		}
		catch( Exception $err )
		{
			$this->returnJsonArray( array( 'status' => 'fail', 'msg' => $err->getMessage() ) );
		}
	} 
}