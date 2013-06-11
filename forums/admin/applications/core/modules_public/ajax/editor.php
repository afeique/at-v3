<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Handles ajax functions for IP.Board Text Editor
 * Author: Matt "Matt Mecham" Mecham
 * Last Updated: $LastChangedDate: 2012-10-10 06:39:40 -0400 (Wed, 10 Oct 2012) $
 * </pre>
 *
 * @author 		$Author: mmecham $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Gallery
 * @link		http://www.invisionpower.com
 * @version		$Rev: 11434 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_ajax_editor extends ipsAjaxCommand
{
	/**
	 * Main class entry point
	 *
	 * @param	object		ipsRegistry reference
	 * @return	@e void		[Outputs to screen]
	 */	
	public function doExecute( ipsRegistry $registry )
	{
		
		/* Load editor stuff */
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/editor/composite.php', 'classes_editor_composite' );
		$this->editor = new $classToLoad();
		
		$this->lang->loadLanguageFile( 'public_editors', 'core' );

		/* What to do? */
		switch( $this->request['do'] )
		{
			case 'autoSave':
				$this->_autoSave();
			break;
			case 'switch':
				$this->_switch();
			break;
			case 'showSettings':
				$this->_showSettings();
			break;
			case 'getEmoticons':
				$this->_getEmoticons();
			break;
			case 'saveSettings':
				$this->_saveSettings();
			break;
        }
    }
    
    /**
     * Show settings box
     *
     * @return	json
     */
    protected function _getEmoticons()
    {
    	return $this->returnJsonArray( $this->editor->fetchEmoticons( 250 ) );
    }
    
    /**
     * Show settings box
     *
     * @return	@e void
     */
    protected function _showSettings()
    {
    	$this->returnHtml( $this->registry->getClass('output')->getTemplate('editors')->editorSettings() );
    }
    
	/**
     * Save settings
     *
     * @return	@e void
     */
    protected function _saveSettings()
    {
    	if( !$this->memberData['member_id'] )
    	{
    		$this->returnJsonError( 'nopermission' );
    	}
    	
    	$clearSavedContent  = intval( $this->request['clearSavedContent'] );
    	$pastePlain         = intval( $this->request['pastePlain'] );
    	
    	if ( $clearSavedContent )
    	{
    		$this->DB->delete( 'core_editor_autosave', 'eas_member_id=' . $this->memberData['member_id'] );
    	}
    	
    	IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'bw_paste_plain' => $pastePlain ) ) );
    	$member = IPSMember::load( $this->memberData['member_id'] );
    	
    	/* return if no errors occurred */
		return $this->returnJsonArray( array( 'status' => 'ok' ) );
    }
    
	/**
     * Switch between bbcode and rte on the fly, man
     *
     * @return	@e void
     */
    protected function _switch()
    { 
    	$content    = $_POST['content'];
    	$htmlStatus = intval( $_REQUEST['htmlStatus'] );
    	
    	IPSDebug::fireBug( 'info', array( 'Content received: ' . $content ) );
    	
		if ( $content )
		{
			if ( $htmlStatus )
			{
				$this->editor->setAllowHtml( $htmlStatus );
			}
			
 			$content = $this->editor->switchContent( $content, intval( $_POST['isRte'] ) );
		}
		
		IPSDebug::fireBug( 'info', array( 'Content after conversion: ' . $content ) );
		
		/* return if no errors occurred */
		return $this->returnString( $content );
    }
    
	/**
     * Show more dialogue
     *
     * @param	string		App
     * @param 	string		Area
     * @param	int			Relationship ID
     * @return	@e void
     */
    protected function _autoSave()
    {
    	/* From App */
    	$autoSaveKey = trim( $this->request['autoSaveKey'] );
    	
    	if ( ! $autoSaveKey )
    	{
    		trigger_error( "Missing data in " . __FILE__ . ' ' . __LINE__ );
    	}
    	
    	if ( ! trim( $_POST['content'] ) )
    	{
    		return $this->returnJsonArray( array( 'status' => 'nothingToSave' ) );
    	}
    	
		if ( $_POST['content'] && $autoSaveKey )
		{
 			$this->editor->autoSave( $_POST['content'], $autoSaveKey );
		}
		
		/* return if no errors occurred */
		return $this->returnJsonArray( array( 'status' => 'ok' ) );
    }
    
	
}
