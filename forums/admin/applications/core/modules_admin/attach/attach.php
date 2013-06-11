<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Attachment Poster
 * Last Updated: $Date: 2012-05-21 21:37:50 +0100 (Mon, 21 May 2012) $
 * </pre>
 *
 * @author 		$Author: ips_terabyte $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage  Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 10777 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_core_attach_attach extends ipsCommand
{
	/**
	 * Class entry point
	 *
	 * @param	object		Registry reference
	 * @return	@e void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		/* Attachment Controller Class */
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'core' ) . '/sources/classes/attach/controller.php', 'classes_attach_controller' );
		$controller = new $classToLoad( $registry );
				
		$controller->run( $this->request['do'] );
	}
}