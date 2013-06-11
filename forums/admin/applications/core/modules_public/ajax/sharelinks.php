<?php
/**
 * @file		sharelinks.php 	Ajax method for sharelinks
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2012 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mmecham $
 * $LastChangedDate: 2012-09-19 10:57:30 -0400 (Wed, 19 Sep 2012) $
 * @version		v3.4.5
 * $Revision: 11350 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * @class		public_core_ajax_sharelinks
 * @brief		Ajax method for sharelinks
 */
class public_core_ajax_sharelinks extends ipsAjaxCommand 
{
	/**
	 * Main function executed automatically by the controller
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
    	/* load language */
    	$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_emails' ), 'core' );
    	
    	/* Do it */
    	switch( $this->request['do'] )
    	{
    		case 'twitterForm':
    			return $this->_twitterForm();
    		break;
    		case 'twitterGo':
    			return $this->_twitterGo();
    		break;
    		case 'facebookForm':
    			return $this->_facebookForm();
    		break;
    		case 'facebookGo':
    			return $this->_facebookGo();
    		break;
    		case 'savePostPrefs':
    			return $this->_savePostPrefs();
    		break;
    	}
	}
	
	/**
	 * Stores post prefs for share links
	 * 
	 * @return	@e void		[Outputs JSON to browser AJAX call]
	 */
	protected function _savePostPrefs()
	{
		IPSMember::setToMemberCache( $this->memberData, array( 'postSocialPrefs' => array( 'facebook' => intval( $_POST['facebook'] ), 'twitter' => intval( $_POST['twitter'] ) ) ) );
		
		$this->returnJsonArray( array( 'status' => 'ok' ) );
	}
	
	/**
	 * Displays a form of facebook stuff. It's really that exciting.
	 *
	 * @deprecated as of 3.3 - now using Facebook standard share button
	 * @return	@e void		[Outputs HTML to browser AJAX call]
	 */
	protected function _facebookForm()
	{
		/* Ensure we have a twitter account and that */
		if ( $this->memberData['member_id'] AND $this->memberData['fb_uid'] AND $this->memberData['fb_token'] )
		{
			/* Connect to the Facebook */
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php', 'facebook_connect' );
			$connect	 = new $classToLoad( $this->registry );
			
			try
			{
				$userData = $connect->fetchUserData();
				
				$this->returnHtml( $this->registry->output->getTemplate('global_other')->facebookPop( $userData ) );
			}
			catch( Exception $e )
			{
				$this->returnHtml( '.' );
			}
		}
		else
		{
			/* Oh go on then */
			$this->returnHtml( $this->registry->output->getTemplate('global_other')->facebookPop( array() ) );
		}
	}
	
	/**
	 * Go go Facebook go
	 * 
	 * @Deprecated as of 3.3 - now using Facebook standard share button
	 * @return	@e void		[Outputs HTML to browser AJAX call]
	 */
	protected function _facebookGo()
	{
		/* INIT */
		$comment = trim( urldecode( $_POST['comment'] ) );
		$url     = trim( urldecode( $_POST['url'] ) );
		$title   = trim( urldecode( $_POST['title'] ) );
		$comment = ( $comment == $this->lang->words['fb_share_default'] ) ? '' : $comment;
		
		/* Ensure title is correctly de-html-ized */
		$title = IPSText::UNhtmlspecialchars( $title );
		
		/* Ensure we have a twitter account and that */
		if ( $this->memberData['member_id'] AND $this->memberData['fb_uid'] AND $this->memberData['fb_token'] )
		{
			/* Connect to the Facebook */
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php', 'facebook_connect' );
			$connect	 = new $classToLoad( $this->registry );
			
			try
			{
				$userData = $connect->fetchUserData();
				
				if ( $userData['first_name'] )
				{
					/* Log it */
					$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/share/links.php', 'share_links' );
					$share = new $classToLoad( $this->registry, 'facebook' );
					$share->log( $url, $title );
					
					$connect->postLinkToWall( $url, $comment );
					
					$this->returnHtml( $this->registry->output->getTemplate('global_other')->facebookDone( $userData ) );
				}
				
			}
			catch( Exception $e )
			{
				// Fallsback to returnString below
			}
		}
		
		/* Bog off */
		$this->returnString( 'finchersaysno' );
	}

		
	/**
	 * Go go twitter go
	 * 
	 * @return	@e void		[Outputs HTML to browser AJAX call]
	 */
	protected function _twitterGo()
	{
		/* INIT */
		$tweet = trim( urldecode( $_POST['tweet'] ) );
		$url   = trim( urldecode( $_POST['url'] ) );
		$title = trim( urldecode( $_POST['title'] ) );
		
		/* Ensure title is correctly de-html-ized */
		$title = IPSText::UNhtmlspecialchars( $title );
		
		/* Ensure we have a twitter account and that */
		if ( $this->memberData['member_id'] AND $this->memberData['twitter_id'] AND $this->memberData['twitter_token'] AND $this->memberData['twitter_secret'] )
		{
			/* Connect to the twitter */
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/twitter/connect.php', 'twitter_connect' );
			$connect = new $classToLoad( $this->registry, $this->memberData['twitter_token'], $this->memberData['twitter_secret'] );
			$user    = $connect->fetchUserData();
			
			if ( $user['id'] )
			{
				$sid = $connect->updateStatusWithUrl( $tweet, $url, true, $this->settings['twitter_hashtag'] );
				
				if ( $sid )
				{	
					/* Log it */
					$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/share/links.php', 'share_links' );
					$share = new $classToLoad( $this->registry, 'twitter' );
					$share->log( $url, $title );
					
					$user['status']['id'] = $sid;
					$this->returnHtml( $this->registry->output->getTemplate('global_other')->twitterDone( $user ) );
				}
			}
		}
		
		/* Bog off */
		$this->returnString( 'failwhale' );
	}
	
	/**
	 * Displays a form of twitter stuff. It's really that exciting.
	 *
	 * @return	@e void		[Outputs HTML to browser AJAX call]
	 */
	protected function _twitterForm()
	{
		/* Ensure we have a twitter account and that */
		if ( $this->memberData['member_id'] AND $this->memberData['twitter_id'] AND $this->memberData['twitter_token'] AND $this->memberData['twitter_secret'] )
		{
			/* Connect to the twitter */
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/twitter/connect.php', 'twitter_connect' );
			$connect = new $classToLoad( $this->registry, $this->memberData['twitter_token'], $this->memberData['twitter_secret'] );
			$user    = $connect->fetchUserData();
			
			if ( $user['id'] )
			{
				$this->returnHtml( $this->registry->output->getTemplate('global_other')->twitterPop( $user ) );
			}
		}
		
		/* Bog off */
		$this->returnHtml( 'x' );
	}
}