<?php
/**
 * Invision Power Services
 * IP.SEO Upgrade Message
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2010-2011 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		Core
 * @link		http://www.invisionpower.com
 * @version		$Revision: 10721 $
 */

class admin_core_seo_upgrade extends ipsCommand
{
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
				
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_seo' ) );
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_seo' );
		
		//-----------------------------------------
		// What are we doing?
		//-----------------------------------------
		
		switch($this->request['do'])
		{
			case 'ok':
				$this->ok();
			break;
		
			default:
				$this->splash();
			break;
			
		}
		
		//-----------------------------------------
		// Display
		//-----------------------------------------
		
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Action: Splash
	 */
	public function splash()
	{		
		$this->registry->output->html  = $this->html->upgradeSplash();
	}

}