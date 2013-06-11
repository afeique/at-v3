<?php
/**
 * Invision Power Services
 * IP.SEO Acronym Expansion
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 *
 * @author 		$Author: bfarber $ (Orginal: Mark)
 * @copyright	© 2011 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Nexus
 * @link		http://www.invisionpower.com
 * @since		16th August 2011
 * @version		$Revision: 10721 $
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}

class admin_core_posts_acronyms extends ipsCommand
{
	/**
	 * Skin object shortcut
	 *
	 * @var		$html
	 */
	public $html;
	
	/**
	 * String for the screen url bit
	 *
	 * @var		$form_code
	 */
	public $form_code    = '';
	
	/**
	 * String for the JS url bit
	 *
	 * @var		$form_code_js
	 */
	public $form_code_js = '';
	
	/**
	 * Main function executed automatically by the controller
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
	
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'acronyms_manage' );
		
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_seo' ) );
				
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_seo' );
		
		$this->form_code	= $this->html->form_code	= 'module=posts&amp;section=acronyms&amp;';
		$this->form_code_js	= $this->html->form_code_js	= 'module=posts&section=acronyms&';
		
		//-----------------------------------------
		// What are we doing
		//-----------------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'add':
				$this->form( 'add' );
				break;
				
			case 'edit':
				$this->form( 'edit' );
				break;
				
			case 'save':
				$this->save();
				break;
				
			case 'delete':
				$this->delete();
				break;
		
			default:
				$this->manage();
				break;
		}	
	
		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------
		
		$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
		$this->registry->getClass('output')->sendOutput();
	}
	
	/**
	 * Acronyms dashboard
	 * 
	 * @return	@e void
	 */
	private function manage()
	{
		$acronyms = array();
		
		$this->DB->build( array( 'select' => '*', 'from' => 'seo_acronyms', 'order' => 'a_short' ) );
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$acronyms[ $row['a_id'] ] = $row;
		}
		
		$this->registry->output->html .= $this->html->acronyms( $acronyms );
	}
	
	/**
	 * Displays a form to add/edit acronyms
	 * 
	 * @return	@e void
	 */
	private function form( $type )
	{
		/* Normal form logic */
		$current = array();
		
		if ( $type == 'edit' )
		{			
			$current = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'seo_acronyms', 'where' => 'a_id=' . intval($this->request['id']) ) );
			
			if ( !$current['a_id'] )
			{
				ipsRegistry::getClass('output')->showError( 'err_no_acronym', '11SEO100', FALSE, '', 404 );
			}
		}
		
		$this->registry->output->html .= $this->html->acronymForm( $current );
	}
	
	/**
	 * Saves the new/edited acronym
	 * 
	 * @return	@e void
	 */
	private function save()
	{
		//-----------------------------------------
		// Validate Data
		//-----------------------------------------
		
		if ( !$this->request['short'] or !$this->request['long'] )
		{
			ipsRegistry::getClass('output')->showError( 'err_acronym_details', '11SEO101', FALSE, '', 500 );
		}
		
		if ( strlen( $this->request['short'] ) > 255 or strlen( $this->request['long'] ) > 255 )
		{
			ipsRegistry::getClass('output')->showError( 'err_acronym_toolong', '21SEO102', FALSE, '', 500 );
		}
			
		//-----------------------------------------
		// Save
		//-----------------------------------------
		
		$save = array(  'a_short'			=> $this->request['short'],
						'a_long'			=> $this->request['long'],
						'a_semantic'		=> intval( $this->request['semantic'] ),
						'a_casesensitive'	=> intval( $this->request['casesensitive'] )
						);
		
		if ( ! empty($this->request['id']) )
		{
			$current = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'seo_acronyms', 'where' => 'a_id=' . intval($this->request['id']) ) );
			
			if ( empty($current['a_id']) )
			{
				ipsRegistry::getClass('output')->showError( 'err_no_acronym', '11SEO100', FALSE, '', 404 );
			}
			
			$this->DB->update( 'seo_acronyms', $save, "a_id={$current['a_id']}" );
		}
		else
		{
			$this->DB->insert( 'seo_acronyms', $save );
		}		
		
		// And recache
		$this->recache();
				
		//-----------------------------------------
		// Display
		//-----------------------------------------
		
		$this->registry->output->global_message = $this->lang->words['acronym_saved'];
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'] . $this->form_code );
	}
	
	/**
	 * Deletes acronyms
	 * 
	 * @return	@e void
	 */
	private function delete()
	{
		$id = intval( $this->request['id'] );
		$current = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'seo_acronyms', 'where' => "a_id={$id}" ) );
		if ( !$current['a_id'] )
		{
			ipsRegistry::getClass('output')->showError( 'err_no_acronym', '11SEO103', FALSE, '', 404 );
		}
		
		$this->DB->delete( 'seo_acronyms', "a_id={$id}" );
		
		$this->recache();
		
		$this->registry->output->global_message = $this->lang->words['acronym_deleted'];
		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'] . $this->form_code );
	}
	
    /**
	 * Rebuilds the acronyms cache
	 * 
	 * @return	@e void
	 */
	public function recache()
	{
		$cache = array();
		
		$this->DB->build( array( 'select' => '*', 'from' => 'seo_acronyms', 'order' => 'a_short' ) );
		$this->DB->execute();
					
		while( $row = $this->DB->fetch() )
		{
			$cache[ $row['a_short'] ] = $row;
		}
		
		$this->cache->setCache( 'ipseo_acronyms', $cache, array( 'array' => 1 ) );
	}
	
}