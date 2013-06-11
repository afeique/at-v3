<?php
	/**
	 * @desc			This file is only an example for loading IPBWI. Feel free to copy
	 * 					this code to your own website files.
	 * @copyright		2007-2010 IPBWI development team
	 * @package			liveExample
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 * @version			$LastChangedDate: 2008-09-19 18:49:53 +0000 (Fr, 19 Sep 2008) $
	 * @since			2.0
	 * @link			http://ipbwi.com
	 * @ignore
	 */

	// Initialization
	$pageTitle		= 'Forum List';
	require_once('../ipbwi/ipbwi.inc.php');
	require_once('lib/php/includes.inc.php');

	echo $header;

	// Error Output
	echo $ipbwi->printSystemMessages();

	// dump all forums, limit them from entry 1 - 5
	$forums_a = $ipbwi->forum->getAllSubs('*','name_id_with_indent',false,false,0,5);
	
	// dump all forums, no limit
	$forums_b = $ipbwi->forum->getAllSubs('*','html_form',false,false);
	
	echo '<h1>List as Array (recursive, including subforums)</h1>';
	foreach($forums_a as $forums){
		echo '<pre>'.$forums['name'].'</pre>';
	}
	
	echo '<h1>No limit as Select Form</h1>';
	echo '<select>'.$forums_b.'</select>';
	
	echo $footer;
?>