<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * QA system methods (Matt mecham)
 * Last Updated: $Date: 2012-08-28 22:56:22 +0100 (Tue, 28 Aug 2012) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 11296 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_forums_extras_answer extends ipsCommand
{
	private $_topicData = array();
	private $_postData  = array();
	private $_forumData = array();
	
	/**
	* Class entry point
	*
	* @param	object		Registry reference
	* @return	@e void		[Outputs to screen/redirects]
	*/
	public function doExecute( ipsRegistry $registry )
	{
		/* Security Check */
		if ( $this->request['auth_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'no_permission', 'extra_answer_001', null, null, 403 );
		}
		
		/* Init */
		if ( ! $this->registry->isClassLoaded('topics') )
		{
			$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . "/sources/classes/topics.php", 'app_forums_classes_topics', 'forums' );
			$this->registry->setClass( 'topics', new $classToLoad( $this->registry ) );
		}
		
		/* Language file */
		$this->registry->class_localization->loadLanguageFile( array( 'public_topic' ) );
		
		/* Get data */
		$tid  = intval( $this->request['t'] );
		$pid  = intval( $this->request['pid'] );
		
		/* Quick check */
		if ( ! $tid || ! $pid )
		{
			$this->registry->output->showError( 'no_permission', 'extra_answer_002', null, null, 404 );
		}
		
		/* Get topic */
		$this->_topicData = $this->registry->topics->getTopicById( $tid );
		$this->_postData  = $this->registry->topics->getPostById( $pid );
		$this->_forumData = $this->registry->class_forums->getForumById( $this->_topicData['forum_id'] );
		
		/* Another check */
		if ( ! $this->_topicData['tid'] || ! $this->_postData['pid'] )
		{
			$this->registry->output->showError( 'no_permission', 'extra_answer_003', null, null, 404 );
		}
		
		/* We being silly? */
		if ( $this->_postData['topic_id'] != $tid )
		{
			$this->registry->output->showError( 'no_permission', 'extra_answer_004', null, null, 403 );
		}
		
		/* Have permission to see this topic? */
		if ( ! $this->registry->topics->canView( $this->_topicData ) )
		{
			$this->registry->output->showError( 'no_permission', 'extra_answer_005', null, null, 403 );
		}
		
		/* Locked? */
		if ( ! $this->memberData['g_is_supmod'] )
		{
			if ( $this->_topicData['state'] != 'open' )
			{
				$this->registry->output->showError( 'topic_locked', 'extra_answer_006', true, null, 403 );
			}
		}
	
		/* What to do? */
		switch( $this->request['do'] )
		{
			default:
			case 'answer':
				$this->_answer();
			break;
			
			case 'unanswer':
				$this->_unanswer();
			break;
		}
	}
	
	/**
	 * Mark post as answered
	 *
	 * @return	@e void
	 */
	protected function _unanswer()
	{
		/* Mark as read */
		try
		{
			$this->registry->topics->unAnswerTopicSingle( $this->_postData, $this->_topicData );
		}
		catch( Exception $ex )
		{
			$this->registry->output->showError( 'no_permission', 'extra_answer_unanswer_001', null, null, 403 );
		}
	
		$this->registry->output->silentRedirect( $this->settings['base_url'] . "showtopic=".$this->_topicData['tid'] . "&view=findpost&p=" . $this->_postData['pid'] );
	}
	
	/**
	 * Mark post as answered
	 *
	 * @return	@e void
	 */
	protected function _answer()
	{	
		/* Mark as read */
		try
		{
			$this->registry->topics->answerTopicSingle( $this->_postData, $this->_topicData );
		}
		catch( Exception $ex )
		{
			$this->registry->output->showError( 'no_permission', 'extra_answer_answer_001', null, null, 403 );
		}
		
		$this->registry->output->silentRedirect( $this->settings['base_url'] . "showtopic=".$this->_topicData['tid'] . "&view=findpost&p=" . $this->_postData['pid'] );
	}
}
