<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Captcha
 * Last Updated: $Date: 2013-04-16 15:13:53 -0400 (Tue, 16 Apr 2013) $
 * </pre>
 *
 * @author 		$Author $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 12182 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_global_navigation extends ipsCommand
{
	/**
	 * Class entry point
	 *
	 * @param	object		Registry reference
	 * @return	@e void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		/* Set up */
				
		$inapp = is_string( $this->request['inapp'] ) ? trim( $this->request['inapp'] ) : "";
		
		if ( !$inapp )
		{
			$this->registry->output->showError( 'invalid_app', 1040007, null, null, 500 );
		}
		
		/* Load navigation stuff */
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/navigation/build.php', 'classes_navigation_build' );
		$navigation = new $classToLoad( $inapp );
		
		/* Return */
		$html = $this->registry->output->getTemplate( 'global_other' )->quickNavigationWrapper( $navigation->loadApplicationTabs(), $navigation->loadNavigationData(), $inapp );
		
		$this->registry->getClass('output')->setTitle( $this->lang->words['navigation_title'] . ' - ' . IPSLib::getAppTitle( $inapp ) );
		$this->registry->getClass('output')->addContent( $html );
        $this->registry->getClass('output')->sendOutput();
	}
}