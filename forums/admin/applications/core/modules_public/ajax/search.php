<?php
/**
 * @file		search.php 	AJAX configure VNC filters
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: AndyMillne $
 * @since		2/14/2011
 * $LastChangedDate: 2013-03-22 17:35:37 -0400 (Fri, 22 Mar 2013) $
 * @version		v3.4.5
 * $Revision: 12112 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

/**
 *
 * @class		public_core_ajax_search
 * @brief		Search VNC configurator
 * 
 */
class public_core_ajax_search extends ipsAjaxCommand
{	
	/**
	 * Main function executed automatically by the controller
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		//-----------------------------------------
		// Get forums class
		//-----------------------------------------
		
		if ( ! $this->registry->isClassLoaded('class_forums' ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php", 'class_forums', 'forums' );
			$this->registry->setClass( 'class_forums', new $classToLoad( $this->registry ) );
			$this->registry->getClass('class_forums')->strip_invisible = 1;
			$this->registry->getClass('class_forums')->forumsInit();
		}
		
		$this->lang->loadLanguageFile( array( 'public_search' ) );

		//-----------------------------------------
		// What to do?
		//-----------------------------------------
		
		switch( $this->request['do'] )
		{
			default:
			case 'showForumsVncFilter':
				$this->showForm();
			break;
			
			case 'saveForumsVncFilter':
				$this->saveForm();
			break;

			case 'saveFollow':
				$this->saveFollow();
			break;
		}
	}

	/**
	 * Save the 'like' preferences for a single object
	 *
	 * @return @e void
	 */
	protected function saveFollow()
	{
		//-----------------------------------------
		// Get like helper class
		//-----------------------------------------
		
		$bootstraps		= array();
		
		require_once( IPS_ROOT_PATH . 'sources/classes/like/composite.php' );/*noLibHook*/
		$_bootstrap		= classes_like::bootstrap( $this->request['searchApp'], $this->request['contentType'] );
		$_likeKey		= classes_like_registry::getKey( $this->request['id'], $this->memberData['member_id'] );
		$_frequencies	= $_bootstrap->allowedFrequencies();

		//-----------------------------------------
		// What action to take?
		//-----------------------------------------
		
		switch( $this->request['modaction'] )
		{
			case 'delete':
				$_bootstrap->remove( $this->request['id'], $this->memberData['member_id'] );
			break;

			case 'change-donotify':
				$this->DB->update( 'core_like', array( 'like_notify_do' => 1, 'like_notify_freq' => 'immediate' ), "like_id='" . addslashes($_likeKey) . "'" );
			break;

			case 'change-donotnotify':
				$this->DB->update( 'core_like', array( 'like_notify_do' => 0 ), "like_id='" . addslashes($_likeKey) . "'" );
			break;

			case 'change-immediate':
				if( in_array( 'immediate', $_frequencies ) )
				{
					$this->DB->update( 'core_like', array( 'like_notify_do' => 1, 'like_notify_freq' => 'immediate' ), "like_id='" . addslashes($_likeKey) . "'" );
				}
			break;

			case 'change-offline':
				if( in_array( 'offline', $_frequencies ) )
				{
					$this->DB->update( 'core_like', array( 'like_notify_do' => 1, 'like_notify_freq' => 'offline' ), "like_id='" . addslashes($_likeKey) . "'" );
				}
			break;
			
			case 'change-daily':
				if( in_array( 'daily', $_frequencies ) )
				{
					$this->DB->update( 'core_like', array( 'like_notify_do' => 1, 'like_notify_freq' => 'daily' ), "like_id='" . addslashes($_likeKey) . "'" );
				}
			break;
			
			case 'change-weekly':
				if( in_array( 'weekly', $_frequencies ) )
				{
					$this->DB->update( 'core_like', array( 'like_notify_do' => 1, 'like_notify_freq' => 'weekly' ), "like_id='" . addslashes($_likeKey) . "'" );
				}
			break;

			case 'change-anon':
				$this->DB->update( 'core_like', array( 'like_is_anon' => 1 ), "like_id='" . addslashes($_likeKey) . "'" );
			break;

			case 'change-noanon':
				$this->DB->update( 'core_like', array( 'like_is_anon' => 0 ), "like_id='" . addslashes($_likeKey) . "'" );
			break;
			default:
				$this->returnJsonError("follow_no_action");
			break;
		}

		$_data	= $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'core_like', 'where' => "like_id='" . addslashes($_likeKey) . "'" ) );

		$this->returnJsonArray( array( 'html' => $this->registry->output->getTemplate('search')->followData( $_data ) ) );
	}

	/**
	 * Save the form to configure VNC forum filters
	 *
	 * @return	@e void
	 */
	public function saveForm()
	{
		$vncPrefs	= IPSMember::getFromMemberCache( $this->memberData, 'vncPrefs' );

		/* Filter forums for VNC */
		if( !empty($this->request['saveVncFilters']) )
		{
			$this->request['saveVncFilters']	= rtrim( $this->request['saveVncFilters'], ',' );
			
			if( $this->request['saveVncFilters'] == 'all' )
			{
				unset($vncPrefs['forums']['vnc_forum_filter']);
			}
			else if( strpos( $this->request['saveVncFilters'], ',' ) !== false )
			{
				$vncPrefs['forums']['vnc_forum_filter']	= explode( ',', $this->request['saveVncFilters'] );
			}
			else if( !empty($this->request['saveVncFilters']) )
			{
				$vncPrefs['forums']['vnc_forum_filter']	= array( $this->request['saveVncFilters'] );
			}
		}
		
		IPSMember::setToMemberCache( $this->memberData, array( 'vncPrefs' => $vncPrefs ) );
		
		$this->returnJsonArray( array( 'ok' => true ) );
	}
	
	/**
	 * Show the form to configure VNC forum filters
	 *
	 * @return	@e void
	 */
	public function showForm()
	{
		$_data		= $this->_getData();
		$vncPrefs	= IPSMember::getFromMemberCache( $this->memberData, 'vncPrefs' );
		$fFP		= $vncPrefs == null ? null : ( empty($vncPrefs['forums']['vnc_forum_filter']) ? null : $vncPrefs['forums']['vnc_forum_filter'] );
		
		$this->returnHtml( $this->registry->output->getTemplate('search')->forumsVncFilters( $_data, $fFP ) );
	}

	/**
	 * Fetches forum jump data
	 *
	 * @return	string
	 */
	private function _getData()
	{
		$depth_guide = 0;
		$links		 = array();
		
		if( is_array($this->registry->class_forums->forum_cache['root'] ) AND count($this->registry->class_forums->forum_cache['root'] ) )
		{
			foreach($this->registry->class_forums->forum_cache['root'] as $forum_data )
			{
				if ( $forum_data['sub_can_post'] or ( isset($this->registry->class_forums->forum_cache[ $forum_data['id'] ] ) AND is_array($this->registry->class_forums->forum_cache[ $forum_data['id'] ] ) AND count($this->registry->class_forums->forum_cache[ $forum_data['id'] ] ) ) )
				{
					$forum_data['redirect_on'] = isset( $forum_data['redirect_on'] ) ? $forum_data['redirect_on'] : 0;
					
					if ( $forum_data['redirect_on'] == 1 )
					{
						continue;
					}
					
					$links[] = array( 'important' => true, 'depth' => $depth_guide, 'title' => $forum_data['name'], 'id' => $forum_data['id'] );
					
					if ( isset($this->registry->class_forums->forum_cache[ $forum_data['id'] ]) AND is_array($this->registry->class_forums->forum_cache[ $forum_data['id'] ] ) )
					{
						$depth_guide++;
						
						foreach($this->registry->class_forums->forum_cache[ $forum_data['id'] ] as $forum_data )
						{
							if ( $forum_data['redirect_on'] == 1 )
							{
								continue;
							}						
						
							$links[] = array( 'depth' => $depth_guide, 'title' => $forum_data['name'], 'id' => $forum_data['id'] );
					
							$links = $this->_getDataRecursively( $forum_data['id'], $links, $depth_guide );			
						}
						
						$depth_guide--;
					}
				}
			}
		}
		
		return $links;
	}
	
	/**
	 * Internal helper function for forumsForumJump
	 *
	 * @param	integer	$root_id
	 * @param	array	$links
	 * @param	string	$depth_guide
	 * @return	string
	 */
	private function _getDataRecursively( $root_id, $links=array(), $depth_guide=0 )
	{
		if ( isset( $this->registry->class_forums->forum_cache[ $root_id ] ) AND is_array($this->registry->class_forums->forum_cache[ $root_id ] ) )
		{
			$depth_guide++;
			
			foreach($this->registry->class_forums->forum_cache[ $root_id ] as $forum_data )
			{
				if ( $forum_data['redirect_on'] == 1 )
				{
					continue;
				}
				
				$links[] = array( 'depth' => $depth_guide, 'title' => $forum_data['name'], 'id' => $forum_data['id'] );
				
				$links = $this->_getDataRecursively( $forum_data['id'], $links, $depth_guide );
			}
		}

		return $links;
	}
}