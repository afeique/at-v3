<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Login handler abstraction : Windows Live method
 * Last Updated: $Date: 2012-10-22 07:39:12 -0400 (Mon, 22 Oct 2012) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 11499 $
 *
 */

$config		= array(
					array(
							'title'			=> 'Location of key XML',
							'description'	=> "You must register your site as an application and receive an application ID to utilize Windows Live(tm) on your site.  See the <a href='http://msdn.microsoft.com/en-us/library/bb676626.aspx'>MSDN Library</a> for more information.  Note that it is recommended you store this file outside of your web root directory for security purposes.",
							'key'			=> 'key_file_location',
							'type'			=> 'string'
						),
					);

$help = "<a href='". ipsRegistry::$settings['base_url'] ."app=core&module=applications&section=enhancements&do=edit&service=enhancements_core_windowslive'>" . ipsRegistry::getClass('class_localization')->words['windowslive_help'] . '</a>';
					