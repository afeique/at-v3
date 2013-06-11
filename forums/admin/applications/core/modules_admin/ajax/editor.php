<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Handles Admin ajax functions for IP.Board Text Editor
 * Author: Matt "Matt Mecham" Mecham
 * Last Updated: $LastChangedDate: 2012-05-29 14:05:10 +0100 (Tue, 29 May 2012) $
 * </pre>
 *
 * @author 		$Author: AndyMillne $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Gallery
 * @link		http://www.invisionpower.com
 * @version		$Rev: 10807 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

require_once( IPSLib::getAppDir('core') . '/modules_public/ajax/editor.php' );

class admin_core_ajax_editor extends public_core_ajax_editor
{
	
}
