<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Bulk Mail Recipients List Generator (AJAX)
 * Last Updated: $Date: 2012-07-12 18:15:50 +0100 (Thu, 12 Jul 2012) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @version		$Revision: 11070 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_members_ajax_bulkmail extends ipsAjaxCommand 
{
	/**
	 * Main class entry point
	 *
	 * @param	object		ipsRegistry reference
	 * @return	@e void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
	
		require_once IPSLib::getAppDir( 'members' ) . '/sources/classes/bulkMailFilter.php';
		$this->html			= $this->registry->output->loadTemplate('cp_skin_bulkmail');
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_bulkmail' ) );
		$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_send' );
		
		$controllerClass = IPSLib::loadActionOverloader( IPSLib::getAppDir('members') . '/modules_admin/bulkmail/bulkmail.php', 'admin_members_bulkmail_bulkmail' );
		$controller = new $controllerClass( $registry );
		$controller->makeRegistryShortcuts( $registry );
		
		//-----------------------------------------
		// Load Mail
		//-----------------------------------------
		
		$id = intval( $this->request['id'] );		
		$mail = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'bulk_mail', 'where' => 'mail_id=' . $id ) );
		
		if ( ! $mail['mail_id'] )
		{
			$this->returnHTML( '' );
		}
		if ( ! $mail['mail_subject'] and ! $mail['mail_content'] )
		{
			$this->returnHTML( '' );
		}
		
		$opts = unserialize( stripslashes( $mail['mail_opts'] ) );
		$mail['mail_html_on'] = $opts['mail_html_on'];
		
		//-----------------------------------------
 		// Get Members
 		//-----------------------------------------
 		
 		/* Start with a basic query */
		$queryData = array(
			'select'	=> 'm.member_id, m.members_display_name, m.email',
			'from'		=> array( 'members' => 'm' ),
			'order'		=> 'm.members_display_name',
			'limit'		=> array( ( 10000 * ( $this->request['page'] - 1 ) ), 10000 )
			);
			
		/* Add in filters */
 		$_queryData = $controller->_buildMembersQuery( $opts['filters'] );
 		$queryData['add_join'] = $_queryData['add_join'];
 		$queryData['where'] = implode( ' AND ' , $_queryData['where'] );
 		
 		/* Count */
		$this->DB->build( $queryData );
		$this->DB->execute();
		while ( $row = $this->DB->fetch() )
		{
			$members[] = $row;
		}
		
		//-----------------------------------------
		// Display
		//-----------------------------------------
		
		$this->returnHTML( $this->html->mss_recipients( $this->request['page'], $this->request['countmembers'], $members ) );
	}
}