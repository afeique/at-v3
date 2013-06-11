<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Sitemap Forwarder
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 * </pre>
 *
 * @author 		$Author $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		27th June 2012
 * @version		$Rev: 10721 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_global_sitemap extends ipsCommand
{
	public function doExecute( ipsRegistry $registry )
	{
		// Requesting a sitemap?
		if(!array_key_exists('sitemap', $this->request))
		{
			$this->registry->output->showError( 'error_generic', 10850, null, null, 404 );
			return false;
		}
		
		// Got a valid name?
		if(!preg_match('/sitemap\_([a-zA-Z0-9\_]+)\.xml(\.gz)?/', $this->request['sitemap']))
		{
			$this->registry->output->showError( 'error_generic', 10850, null, null, 404 );
			return false;
		}
		
		// Got a valid file?
		if(!file_exists( IPS_CACHE_PATH . 'cache/' . $this->request['sitemap']))
		{
			$this->registry->output->showError( 'error_generic', 10850, null, null, 404 );
			return false;
		}
		
		header('Content-Type: ' . (strpos($this->request['sitemap'], '.gz') ? 'application/x-gzip' : 'application/xml'));
		header('Content-Disposition: attachment; filename=' . $this->request['sitemap']);
		
		print file_get_contents( IPS_CACHE_PATH . 'cache/' . $this->request['sitemap']);
	}
}