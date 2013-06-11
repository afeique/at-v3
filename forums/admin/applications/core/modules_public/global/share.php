<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Captcha
 * Last Updated: $Date: 2013-01-07 12:09:37 -0500 (Mon, 07 Jan 2013) $
 * </pre>
 *
 * @author 		$Author $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 11791 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_global_share extends ipsCommand
{
	/**
	 * Class entry point
	 *
	 * @param	object		Registry reference
	 * @return	@e void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		/* Disabled? */
		if ( ! $this->settings['sl_enable'] )
		{
			$this->registry->output->showError( 'no_permission', 100234.1, false, null, 403 );
		}
				
		$url   = IPSText::base64_decode_urlSafe( $this->request['url'] );
		$title = IPSText::base64_decode_urlSafe( $this->request['title'] );
		$key   = trim( $this->request['key'] );
		
		/* Get the lib */
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/share/links.php', 'share_links' );
		$share = new $classToLoad( $registry, $key );
		
		/* Share! */
		$share->share( $title, $url );
	}
}