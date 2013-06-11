<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Dashboard
 * Last Updated: $Date: 2012-07-12 18:15:50 +0100 (Thu, 12 Jul 2012) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		5th January 2005
 * @version		$Revision: 11070 $
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_core_mycp_tabs extends ipsCommand
{
	/**
	 * Skin object
	 *
	 * @var		object			Skin templates
	 */
	protected $html;

	/**
	 * Shortcut for url
	 *
	 * @var		string			URL shortcut
	 */
	protected $form_code;

	/**
	 * Shortcut for url (javascript)
	 *
	 * @var		string			JS URL shortcut
	 */
	protected $form_code_js;

	/**
	 * Main function executed automatically by the controller
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Load skin
		//-----------------------------------------

		$this->html = $this->registry->output->loadTemplate('cp_skin_mycp');

		//-----------------------------------------
		// Load language
		//-----------------------------------------

		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'admin_mycp' ) );

		//-----------------------------------------
		// Set up stuff
		//-----------------------------------------

		$this->form_code	= $this->html->form_code	= 'module=mycp&amp;section=tabs';
		$this->form_code_js	= $this->html->form_code_js	= 'module=mycp&section=tabs';
		
		/* Geddit */
		if ( $this->request['do'] == 'save' )
		{
			$this->tabsSave();
		}
		else
		{
			$this->tabsForm();
		}
		
		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------

		$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
		$this->registry->getClass('output')->sendOutput();
	}
	
	/**
	 * Saves the tab form
	 *
	 * @return	array
	 */
	public function tabsSave()
	{
		$tabBar    = array();
		$otherMenu = array();

		if ( empty( $_GET['reset'] ) && is_array( $_GET ) )
		{
			foreach( $_GET as $k => $v )
			{
				if ( substr( $k, 0, 7 ) == 'tabBar_' )
				{
					$tabBar[ str_replace( 'tabBar_', '', $k ) ] = $v;
				}
				
				if ( substr( $k, 0, 10 ) == 'otherMenu_' )
				{
					$otherMenu[ str_replace( 'otherMenu_', '', $k ) ] = $v;
				}
			}
		}
		
		/* Save it */
		$this->registry->adminFunctions->staffSaveCookie( 'tabPrefs', array( 'tabBar' => $tabBar, 'otherMenu' => $otherMenu ) );
		
		/* Reload */
		$this->registry->output->silentRedirect( $this->settings['base_url'] . '&' . $this->form_code . '&do=show' );
	}
	
	/**
	 * Builds the tab form
	 *
	 * @return	array
	 */
	public function tabsForm()
	{
		$applications = ipsRegistry::$applications;
		$mainTabs     = $this->registry->output->getMainTabKeys();
		$otherTabs    = $this->registry->output->getOtherTabKeys();
		
		$mainTabData  = $this->registry->output->getTabDataFromKeys( $mainTabs );
		$otherTabData = $this->registry->output->getTabDataFromKeys( $otherTabs );
		
		$this->registry->output->html = $this->html->tabsForm( $mainTabData, $otherTabData );
	}
}