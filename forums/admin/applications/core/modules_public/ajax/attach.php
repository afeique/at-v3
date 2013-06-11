<?php
/**
 * @file		attach.php 	Provides ajax methods to switch uploader type
 *
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mmecham $
 * @since		-
 * $LastChangedDate: 2013-03-19 09:02:31 -0400 (Tue, 19 Mar 2013) $
 * @version		v3.4.5
 * $Revision: 12085 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 *
 * @class		public_core_ajax_attach
 * @brief		Provides ajax methods for the attach functions
 */
class public_core_ajax_attach extends ipsAjaxCommand
{
	/**
	 * Main function executed automatically by the controller
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Guest? */
		if ( !$this->memberData['member_id'] )
		{
			$this->returnJsonError('no_permission');
		}
		
		/* What to do? */
		switch( $this->request['do'] )
		{
			case 'setPref':
				$this->_setPref();
				break;
			case 'getForumsAppUploader':
				$this->_getForumsAppUploader();
			break;
        }
    }
    
    /**
     * Gets the uploader form/data for ajaxy stuffs
     */
    protected function _getForumsAppUploader()
    {
    	$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_post', 'public_topics' ), 'forums' );
    	
		$attach_post_key = trim( $this->request['attach_post_key'] );
		$attach_rel_id   = intval( $this->request['attach_rel_id'] );
		$forum_id 	 	 = intval( $this->request['forum_id'] );
						 
	    $classToLoad  = IPSLib::loadLibrary( IPSLib::getAppDir( 'core' ) . '/sources/classes/attach/class_attach.php', 'class_attach' );
		$class_attach = new $classToLoad( $this->registry );
		$class_attach->type				= 'post';
		$class_attach->attach_post_key	= $attach_post_key;
		$class_attach->init();
		$class_attach->getUploadFormSettings();
		
		$html = $this->registry->getClass('output')->getTemplate('post')->uploadForm( $this->post_key, 'post', $class_attach->attach_stats, $attach_rel_id, $forum_id );
		
		/* Need to make sure they are unique - if we implement, change template to accept a prefix */
		$html = str_replace( "id='add_files_attach_" , "id='add_files_attach_pu_" , $html );
		$html = str_replace( "id='nojs_attach_"      , "id='nojs_attach_pu_"      , $html );
		$html = str_replace( "id='help_msg'"         , "id='pu_help_msg'"         , $html );
		$html = str_replace( "id='space_info_attach_", "id='space_info_attach_pu_", $html );
		$html = str_replace( "<ul id='attachments'>" , "<ul id='pu_attachments'>" , $html );
		
		/* Remove help msg */
		
		/* remove inline execution */
		preg_match_all( '#<script type=\'text/javascript\'>(.+?)</script>#is', $html, $matches, PREG_SET_ORDER );
		
		foreach( $matches as $val )
		{
			$all        = $val[0];
			$javascript = $val[1];
			
			if ( stristr( $all, 'ipb.attach.registerUploader' ) )
			{
				$html = str_replace( $all, '', $html );
			}
		}
		
		$this->returnHtml( $html );
    }
    
	/**
     * Sets uploader preference
     *
     * @return	@e void
     */
    protected function _setPref()
    {
    	/* Init */
    	$uploader = ( $this->request['pref'] == 'flash' ) ? 'flash' : 'default';
    
    	IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'member_uploader' => $uploader ) ) );
    		
 		/* Fetch data */
 		return $this->returnJsonArray( array( 'status' => 'ok' ) );
    }
}