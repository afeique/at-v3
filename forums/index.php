<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Main public executable wrapper.
 * Set-up and load module to run
 * Last Updated: $Date: 2013-02-26 08:08:06 -0500 (Tue, 26 Feb 2013) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 12025 $
 *
 */	

define( 'IPB_THIS_SCRIPT', 'public' );
require_once( './initdata.php' );/*noLibHook*/

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );/*noLibHook*/
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );/*noLibHook*/
ipsController::run();

exit();