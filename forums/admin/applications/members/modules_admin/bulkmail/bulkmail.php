<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Bulk mail management
 * Last Updated: $Date: 2013-05-09 23:39:45 -0400 (Thu, 09 May 2013) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @version		$Revision: 12243 $
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}

class admin_members_bulkmail_bulkmail extends ipsCommand
{
	/**
	 * Skin object
	 *
	 * @var		object			Skin templates
	 */
	protected $html;
	
	/**
	 * Shortcut for url
	 *
	 * @var		string			URL shortcut
	 */
	protected $form_code;
	
	/**
	 * Shortcut for url (javascript)
	 *
	 * @var		string			JS URL shortcut
	 */
	protected $form_code_js;
	
	/**
	 * Main class entry point
	 *
	 * @param	object		ipsRegistry reference
	 * @return	@e void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		require_once IPSLib::getAppDir( 'members' ) . '/sources/classes/bulkMailFilter.php';
		
		//-----------------------------------------
		// Load skin
		//-----------------------------------------
		
		$this->html			= $this->registry->output->loadTemplate('cp_skin_bulkmail');
		
		//-----------------------------------------
		// Set up stuff
		//-----------------------------------------
		
		$this->form_code	= $this->html->form_code	= 'module=bulkmail&amp;section=bulkmail';
		$this->form_code_js	= $this->html->form_code_js	= 'module=bulkmail&section=bulkmail';
		
		//-----------------------------------------
		// Load lang
		//-----------------------------------------
				
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_bulkmail' ) );
		
		switch( $this->request['do'] )
		{
			case 'mail_new':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_addedit' );
				$this->_mailForm('add');
			break;

			case 'mail_edit':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_addedit' );
				$this->_mailForm('edit');
			break;

			case 'mail_save':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_addedit' );
				$this->_mailSave();
			break;
			
			case 'mail_preview':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_view' );
				$this->_mailPreviewStart();
			break;
			
			case 'mail_preview_do':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_view' );
				$this->_mailPreviewComplete();
			break;
			
			case 'mail_send_start':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_send' );
				$this->_mailSendStart();
			break;
			
			case 'mail_send_complete':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_send' );
				$this->_mailSendComplete();
			break;
			
			case 'mail_send_mandrill':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_send' );
				$this->_mailSendMandrill();
			break;
			
			case 'mail_send_cancel':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_cancel' );
				$this->_mailSendCancel();
			break;
			
			case 'mail_delete':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_delete' );
				$this->_mailDelete();
			break;

			default:
			case 'bulk_mail':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'bulkmail_view' );
				$this->_mailStart();
			break;
		}

		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------
		
		$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
		$this->registry->getClass('output')->sendOutput();
	}
	
	/**
	 * Delete a bulk mail
	 *
	 * @return	@e void		[Outputs to screen]
	 */
	protected function _mailDelete()
	{
		$id = intval( $this->request['id'] );
		
		$active = $this->DB->buildAndFetch( array( 'select' => 'mail_id', 'from' => 'bulk_mail', 'where' => 'mail_active=1 AND mail_id <>' . $id ) );
		
		if( !$active['mail_id'] )
		{
			$this->DB->update( 'task_manager', array( 'task_enabled' => 0 ), "task_key='bulkmail'" );
		}
		
		$this->DB->delete( 'bulk_mail', 'mail_id=' . $id );
											
		$this->registry->output->global_message = $this->lang->words['b_deleted'];
		$this->_mailStart();
	}
	
	/**
	 * Cancels a bulk mail
	 *
	 * @return	@e void		[Outputs to screen]
	 */
	protected function _mailSendCancel()
	{
		$this->DB->update( 'bulk_mail', array(	'mail_active'	=> 0,
													'mail_updated'	=> time(),
										  		), "mail_active=1" );
											
		$this->DB->update( 'task_manager', array( 'task_enabled' => 0 ), "task_key='bulkmail'" );
		
		$this->registry->output->global_message = $this->lang->words['b_cancelled'];
		$this->_mailStart();
	}
	
	/**
	 * Processes a bulk mail
	 *
	 * @return	@e void
	 */
	public function mailSendProcess()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------

		$done	= 0;
		$sent	= 0;

		$mail = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'bulk_mail', 'where' => 'mail_active=1' ) );
		if ( ! $mail['mail_subject'] and ! $mail['mail_content'] )
		{
			/* Just return, if there's nothing to send.  Bug #21494 */
			return;
		}
		
		$opts = unserialize( $mail['mail_opts'] );
		
		//-----------------------------------------
		// What's the plan, Stan?
		// Let's limit to 100 to be safe - tickets have come in where duplicates are received because the task times out
		//-----------------------------------------
		
		$pergo = intval( $mail['mail_pergo'] );
		if ( ! $pergo or $pergo > 100 )
		{
			$pergo = 50;
		}
		
		$sofar = intval($mail['mail_sentto']);
 		
 		//-----------------------------------------
 		// Clear out any other temp headers
 		//-----------------------------------------
 		
 		IPSText::getTextClass('email')->clearHeaders();
 		
 		//-----------------------------------------
 		// Get Members
 		//-----------------------------------------
 		
 		/* Start with a basic query */
		$queryData = array(
			'select'	=> 'm.*',
			'from'		=> array( 'members' => 'm' ),
			'order'		=> 'm.member_id',
			'limit'		=> array( $sofar, $pergo )
			);
			
		/* Add in filters */
 		$_queryData = $this->_buildMembersQuery( $opts['filters'] );
 		$queryData['add_join'] = $_queryData['add_join'];
 		$queryData['where'] = implode( ' AND ' , $_queryData['where'] );
 		
 		/* Count */
		$this->DB->build( $queryData );
		$e = $this->DB->execute();
		while ( $r = $this->DB->fetch( $e ) )
		{
			/* Convert Tags */
			$contents = $this->_convertQuicktags( $mail['mail_content'], $r );
		
			/* Clear out previous data */
			IPSText::getTextClass('email')->clearContent();
			
			/* We need an unsubscribe link */
			IPSText::getTextClass('email')->unsubscribe = true;
			
			/* What kinf of email IS this!? */
			if ( $opts['mail_html_on'] )
			{
				IPSText::getTextClass('email')->setHtmlEmail( true );
				IPSText::getTextClass('email')->setHtmlTemplate( str_replace( "\n", "", $contents ) );
				IPSText::getTextClass('email')->setHtmlWrapper( '<#content#>' );
			}
			else if ( $this->settings['email_use_html'] )
			{
				IPSText::getTextClass('email')->setHtmlEmail( true );
				IPSText::getTextClass('email')->setHtmlTemplate( $contents );
			}
			else
			{
				IPSText::getTextClass('email')->setPlainTextTemplate( $contents );
			}
			
			/* Build it */	
			IPSText::getTextClass('email')->from		= $this->settings['email_out'];
			IPSText::getTextClass('email')->to			= $r['email'];
			IPSText::getTextClass('email')->subject		= $mail['mail_subject'];
			IPSText::getTextClass('email')->setHeader( 'Precedence', 'bulk' );
			IPSText::getTextClass('email')->buildMessage( array(), false, true, $r );

			/* Send it */
			IPSText::getTextClass('email')->sendMail();
		
			/* Increase sent count */
			$sent++;
		}
		
		//-----------------------------------------
		// Did we send any?
		//-----------------------------------------
		
		if ( ! $sent )
		{
			$done	= 1;
		}

		//-----------------------------------------
		// Save out..
		//-----------------------------------------
		
		if ( $done )
		{
			$this->DB->update( 'bulk_mail', array( 	'mail_active'	=> 0,
														'mail_updated'	=> time(),
														'mail_sentto'	=> $sofar + $sent 
													), 'mail_id=' . $mail['mail_id'] );
												
			$this->DB->update( 'task_manager', array( 'task_enabled' => 0 ), "task_key='bulkmail'" );
		}
		else
		{
			$this->DB->update( 'bulk_mail', array(	'mail_updated'	=> time(),
														'mail_sentto'	=> $sofar + $sent 
													), 'mail_id=' . $mail['mail_id'] );
		}			
	}
	
	/**
	 * Send Bulk Mail via Mandrill
	 */
	protected function _mailSendMandrill()
	{
		//-----------------------------------------
		// Load it
		//-----------------------------------------
	
		$id    = intval( $this->request['id'] );
		
		$mail = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'bulk_mail', 'where' => 'mail_id=' . $id ) );
		if ( !$mail['mail_id'] or !$mail['mail_subject'] or !$mail['mail_content'] )
		{
			$this->registry->output->global_message = $this->lang->words['b_nosend'];
			$this->_mailStart();
			return;
		}
		
		$opts = unserialize( $mail['mail_opts'] );
		
		//-----------------------------------------
		// Work out which vars we've actually used
		//-----------------------------------------
		
		$usedVars = array( 'unsubscribe' );
		foreach( array_keys( $this->_getVariableInformation( $this->memberData ) ) as $k )
		{
			if ( strpos( $mail['mail_content'], '{' . $k . '}' ) !== FALSE )
			{
				$usedVars[] = $k;
			}
		}
				
		//-----------------------------------------
 		// Build the JSON document
 		//-----------------------------------------
 		
 		$pergo = 2000;
 		
 		$recipientsTo = array();
 		$recipientsMerge = array();
 		
 		/* Start with a basic query */
		$queryData = array(
			'select'	=> 'm.*',
			'from'		=> array( 'members' => 'm' ),
			'order'		=> 'm.member_id',
			'limit'		=> array( $this->request['st'], $pergo )
			);
			
		/* Add in filters */
		$done = 0;
		$complete = FALSE;
 		$_queryData = $this->_buildMembersQuery( $opts['filters'] );
 		$queryData['add_join'] = $_queryData['add_join'];
 		$queryData['where'] = implode( ' AND ' , $_queryData['where'] );
 		
 		/* Write the file */
		$this->DB->build( $queryData );
		$e = $this->DB->execute();
		
		if ( !$this->DB->getTotalRows( $e ) )
		{
			$complete = TRUE;
		}
		
		while ( $r = $this->DB->fetch( $e ) )
		{
			/* Skip any invalid emails - the chars presented here are allowed via RFC (note that _ and - are already allowed in alphanumericClean and don't need to be specified) */
			if ( !$r['email'] or !$r['members_display_name'] OR !IPSText::checkEmailAddress( $r['email'] ) OR $r['email'] != IPSText::alphanumericalClean( $r['email'], '@.+!#$%&\'*/=?^`{|}~ ' ) )
			{
				continue;
			}
		
			$recipientsTo[] = array( 'email' => $r['email'], 'name' => $r['members_display_name'] );
			
			$vars = array();
			foreach ( $this->_getVariableInformation( $r, 1 ) as $k => $v )
			{
				if ( in_array( $k, $usedVars ) )
				{
					$vars[] = array( 'name' => $k, 'content' => $v );
				}
			}
			if ( !empty( $vars ) )
			{
				$recipientsMerge[] = array( 'rcpt' => $r['email'], 'vars' => $vars );
			}
			
			$done++;
		}
						
		//-----------------------------------------
		// Build Content
		//-----------------------------------------
		
		/* Sort out member vars */				
		$content = $mail['mail_content'];
		foreach ( $this->_getVariableInformation( $this->memberData ) as $k => $v )
		{
			$content = str_replace( '{'.$k.'}', '*|'.$k.'|*', $content );
		}
		
		/* Sort out global vars */
		$globalMergeVars = array();
		foreach ( $this->_getVariableInformation( NULL, 2 ) as $k => $v )
		{
			if ( in_array( $k, $usedVars ) )
			{
				$globalMergeVars[] = array( 'name' => $k, 'content' => $v );
			}
		}
		
		/* Get the full content */
		IPSText::getTextClass('email')->clearContent();
		IPSText::getTextClass('email')->unsubscribe = true;
		if ( $opts['mail_html_on'] )
		{
			IPSText::getTextClass('email')->setHtmlEmail( true );
			IPSText::getTextClass('email')->setHtmlTemplate( str_replace( "\n", "", $content ) );
			IPSText::getTextClass('email')->setHtmlWrapper( '<#content#>' );
		}
		else if ( $this->settings['email_use_html'] )
		{
			IPSText::getTextClass('email')->setHtmlEmail( true );
			IPSText::getTextClass('email')->setHtmlTemplate( $content );
		}
		else
		{
			IPSText::getTextClass('email')->setPlainTextTemplate( $content, true );
		}
		
		if ( $opts['mail_html_on'] or $this->settings['email_use_html'] )
		{
			IPSText::getTextClass('email')->buildMessage( array( 'UNSUBSCRIBE' => '*|unsubscribe|*' ), true, true );
			$content = IPSText::getTextClass('email')->getHtmlContent();
		}
		else
		{
			IPSText::getTextClass('email')->buildMessage( array( 'UNSUBSCRIBE' => '*|unsubscribe|*' ) );
			$content = nl2br( IPSText::getTextClass('email')->getPlainTextContent() );
		}
		
		//-----------------------------------------
		// Send to Mandrill
		//-----------------------------------------

		if( IPS_DOC_CHAR_SET != "UTF-8" )
		{
			$mail['mail_subject'] = IPSText::convertCharsets( $mail['mail_subject'], IPS_DOC_CHAR_SET, "UTF-8" );
		}
		
		require_once IPSLib::getAppDir('members') . '/sources/classes/mandrill.php';
		$mandrill = new Mandrill();
		$response = $mandrill->messages_send( array(
			'message'	=> array(
				'html'					=> $content,
				'subject'				=> $mail['mail_subject'],
				'from_email'			=> $this->settings['email_out'],
				'from_name'				=> $this->settings['board_name'],
				'to'					=> $recipientsTo,
				'auto_text'				=> true,
				'url_strip_qs'			=> false,
				'preserve_recipients'	=> false,
				'merge'					=> true,
				'global_merge_vars'		=> $globalMergeVars,
				'merge_vars'			=> $recipientsMerge,
				'tags'					=> array_merge( array( 'ips' ), array_filter( $opts['mandrill_tags'], create_function( '$v', 'return (bool) $v;' ) ) )
				),
			'async'		=> true
			) );
					
		if ( isset( $response->status ) and $response->status == 'error' )
		{
			$this->registry->output->showError( 'mandrill_error' );
		}
		
		//-----------------------------------------
		// Save
		//-----------------------------------------
		
		$this->DB->update( 'bulk_mail', array( 'mail_active' => 0, 'mail_updated' => time(), 'mail_sentto' => $mail['mail_sentto'] + count( $recipientsTo ) ), 'mail_id=' . $mail['mail_id'] );
		
		if ( $complete !== TRUE )
		{
			$url = "{$this->settings['base_url']}app=members&module=bulkmail&section=bulkmail&do=mail_send_mandrill&id={$id}&countmembers={$this->request['countmembers']}&st=" . ( $this->request['st'] + $pergo );
		
			if ( !$this->request['st'] )
			{
				$this->registry->output->multipleRedirectInit( $url );
				$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
				$this->registry->getClass('output')->sendOutput();
			}
			else
			{
				$percentage = ( ( 100 / $this->request['countmembers'] ) * $this->request['st'] );
				$percentage = floor( $percentage );
				$this->registry->output->multipleRedirectHit( $url, "Processing ({$percentage}% complete)" );
			}
			
			return;
		}
		else
		{
			$this->registry->output->multipleRedirectFinish();
		}
	}
	
	/**
	 * Complete bulk mail processing
	 *
	 * @return	@e void
	 */
	protected function _mailSendComplete()
	{
		$pergo = intval( $this->request['pergo'] );
		$id    = intval( $this->request['id'] );
		
		if ( ! $id )
		{
			$this->registry->output->global_message = $this->lang->words['b_norecord'];
			$this->_mailStart();
			return;
		}
		
		//-----------------------------------------
		// Get it from the db
		//-----------------------------------------
		
		$mail = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'bulk_mail', 'where' => 'mail_id=' . $id ) );
		
		if ( ! $mail['mail_subject'] and ! $mail['mail_content'] )
		{
			$this->registry->output->global_message = $this->lang->words['b_nosend'];
			$this->_mailStart();
			return;
		}
		
		//-----------------------------------------
		// Update mail
		//-----------------------------------------
		
		if ( ! $pergo or $pergo > 100 )
		{
			$pergo = 50;
		}
		
		$this->DB->update( 'bulk_mail', array( 'mail_active' => 1, 'mail_pergo' => $pergo, 'mail_sentto' => 0, 'mail_start' => time() ), 'mail_id=' . $id );
		$this->DB->update( 'bulk_mail', array( 'mail_active' => 0 ) , 'mail_id <> ' . $id );
		
		//-----------------------------------------
		// Wake up task manager
		//-----------------------------------------
		
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/class_taskmanager.php', 'class_taskmanager' );
		$task        = new $classToLoad( $this->registry );

		$this->DB->update( 'task_manager', array( 'task_enabled' => 1 ), "task_key='bulkmail'" );
		
		$this_task = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'task_manager', 'where' => "task_key='bulkmail'" ) );

		$newdate = $task->generateNextRun( $this_task );
		
		$this->DB->update( 'task_manager', array( 'task_next_run' => $newdate ), "task_id=".$this_task['task_id'] );
			
		$task->saveNextRunStamp();
		
		//-----------------------------------------
		// Sit back and watch the show
		//-----------------------------------------
		
		$this->registry->output->global_message = $this->lang->words['b_initiated'];
		
		$this->_mailStart();
	}

	/**
	 * Start the sending of the bulk mail
	 *
	 * @return	@e void
	 */
	protected function _mailSendStart()
	{	
		//-----------------------------------------
		// Init
		//-----------------------------------------
	
		$id = intval( $this->request['id'] );		
		$mail = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'bulk_mail', 'where' => 'mail_id=' . $id ) );
		
		if ( ! $mail['mail_subject'] and ! $mail['mail_content'] )
		{
			$this->registry->output->global_message = $this->lang->words['b_nosend'];
			$this->_mailStart();
			return;
		}
		
		$opts = unserialize( stripslashes( $mail['mail_opts'] ) );
		$mail['mail_html_on'] = $opts['mail_html_on'];
		
		//-----------------------------------------
 		// Get Members
 		//-----------------------------------------
 		
 		/* Start with a basic query */
		$queryData = array(
			'select'	=> 'count(*) as count',
			'from'		=> array( 'members' => 'm' ),
			'order'		=> 'm.members_display_name',
			);
			
		/* Add in filters */
 		$_queryData = $this->_buildMembersQuery( $opts['filters'] );
 		$queryData['add_join'] = $_queryData['add_join'];
 		$queryData['where'] = implode( ' AND ' , $_queryData['where'] );
 		
 		/* Get Count */
 		$countmembers = $this->DB->buildAndFetch( $queryData );
 		
 		/* Get Em */
 		$queryData['select'] = 'm.member_id, m.members_display_name, m.email';
 		$queryData['limit'] = array( 0, 10000 );
		$this->DB->build( $queryData );
		$this->DB->execute();
		while ( $row = $this->DB->fetch() )
		{
			$members[] = $row;
		}
		
		if ( empty( $members ) )
		{
			$this->registry->output->showError( $this->lang->words['b_nonefound'] );
		}
		
		//-----------------------------------------
		// Print 'continue' screen
		//-----------------------------------------
		
		$this->registry->output->html .= $this->html->mailSendStart( $mail, $members, $countmembers['count'] );
	}
		
	/**
	 * Process the sending of the bulk mail
	 *
	 * @return	@e void
	 */
	protected function _mailPreviewComplete()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
	
		$id			= intval( $this->request['id'] );
		$mail		= $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'bulk_mail', 'where' => 'mail_id=' . $id ) );
				
		$mailopts	= unserialize( $mail['mail_opts'] );
		
		//-----------------------------------------
		// Build It
		//-----------------------------------------
		
		$contents = $this->_convertQuicktags( $mail['mail_content'], $this->memberData );
		
		/* Clear out previous data */
		IPSText::getTextClass('email')->clearContent();
		
		IPSText::getTextClass('email')->unsubscribe = true;

		/* Specifically a HTML email */
		if ( $mailopts['mail_html_on'] )
		{
			IPSText::getTextClass('email')->setHtmlEmail( true );
			IPSText::getTextClass('email')->setHtmlTemplate( str_replace( "\n", "", $contents ) );
			IPSText::getTextClass('email')->setHtmlWrapper( '<#content#>' );
			IPSText::getTextClass('email')->buildMessage( array(), false, true, $this->memberData );
		}
		else if ( $this->settings['email_use_html'] )
		{
			IPSText::getTextClass('email')->setHtmlEmail( true );
			IPSText::getTextClass('email')->setHtmlTemplate( $contents );
			IPSText::getTextClass('email')->buildMessage( array(), false, true, $this->memberData );
		}
		else
		{
			IPSText::getTextClass('email')->setPlainTextTemplate( $contents );
			IPSText::getTextClass('email')->buildMessage( array(), false, false, $this->memberData );
		}
			
		IPSText::getTextClass('email')->from		= $this->settings['email_out'];
		IPSText::getTextClass('email')->to			= $this->memberData['email'];
		IPSText::getTextClass('email')->subject		= $mail['mail_subject'];
		IPSText::getTextClass('email')->setHeader( 'Precedence', 'bulk' );
		
		//-----------------------------------------
		// Display
		//-----------------------------------------
		
		if ( $mailopts['mail_html_on'] or $this->settings['email_use_html'] )
		{
			echo IPSText::getTextClass('email')->getHtmlContent();
		}
		else
		{
			echo '<pre>';
			echo IPSText::getTextClass('email')->getPlainTextContent();
			echo '</pre>';
		}
			
		IPSText::getTextClass('email')->clearContent();

		exit();
	}
	
	/**
	 * Preview the email (javascript popup)
	 *
	 * @return	@e void
	 */
	protected function _mailPreviewStart()
	{
		$this->registry->output->html .= $this->html->mailPopupContent();
		$this->registry->output->printPopupWindow();
		exit();
	}
	
	/**
	 * Save the new or edited bulk mail
	 *
	 * @return	@e void
	 */
	protected function _mailSave()
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$id		= intval( $this->request['id'] );
		$type	= $this->request['type'];
		
		//-----------------------------------------
		// Validate
		//-----------------------------------------
		
		$errors = array();

		/* Basic */
		if ( ! $this->request['mail_subject'] or ! $this->request['mail_content'] )
		{
			$errors[] = $this->lang->words['b_entercont'];
		}
		
		/* Filters */
		$opts = array( 'mail_html_on' => ( $this->settings['email_use_html'] and $_POST['mail_html_on'] ), 'filters' => array(), 'mandrill_tags' => array_map( 'trim', explode( ',', $this->request['mandrill_tags'] ) ) );
		foreach ( IPSLib::getEnabledApplications() as $app )
		{
			$extensionFile = IPSLib::getAppDir( $app['app_directory'] ) . '/extensions/bulkMailFilters.php';
			if ( file_exists( $extensionFile ) )
			{
				$classToLoad = IPSLib::loadLibrary( $extensionFile, 'bulkMailFilters_' . $app['app_directory'] );
				$class       = new $classToLoad( $this->registry );
								
				foreach ( $class->filters as $f )
				{
					$classToLoad = IPSLib::loadLibrary( $extensionFile, "bulkMailFilter_{$app['app_directory']}_{$f}" );
					$_class = new $classToLoad( $this->registry );
					
					try
					{
						$value = $_class->save( $_POST );
						
						if ( $value !== FALSE )
						{
							$opts['filters'][ $app['app_directory'] ][ $f ] = $value;
						}
					}
					catch ( Exception $e )
					{
						$errors[] = $e->getMessage();
					}
				}
			}
		}
						
		/* Anything? */
		if ( !empty( $errors ) )
		{
			return $this->_mailForm( $type, $errors );
		}

 		//-----------------------------------------
 		// Count how many matches
 		//-----------------------------------------
 		
 		/* Start with a basic query */
		$queryData = array(
			'select'	=> 'count(*) as count',
			'from'		=> array( 'members' => 'm' ),
			);
			
		/* Add in filters */
 		$_queryData = $this->_buildMembersQuery( $opts['filters'] );
 		$queryData['add_join'] = $_queryData['add_join'];
 		$queryData['where'] = implode( ' AND ' , $_queryData['where'] );
 		
 		/* Count */
		$count = $this->DB->buildAndFetch( $queryData );
		
		/* If there aren't any, error */
		if ( !$count['count'] )
		{
			return $this->_mailForm( $type, array( $this->lang->words['b_nonefound'] ) );
		}
		
		//-----------------------------------------
		// Save
		//-----------------------------------------
				
		$save_array = array(
							'mail_subject'	=> IPSText::stripslashes( $_POST['mail_subject'] ),
							'mail_content'	=> $this->request['mail_html_on'] ? $_POST['mail_content_plain'] : IPSText::stripslashes( $_POST['mail_content'] ),
							'mail_start'	=> time(),
							'mail_updated'	=> time(),
							'mail_sentto'	=> 0,
							'mail_groups'   => '',
							'mail_opts'		=> serialize( $opts )
						 );
						 						 
		if ( $type == 'add' )
		{
			//-----------------------------------------
			// Save to DB
			//-----------------------------------------
			
			$this->DB->insert( 'bulk_mail', $save_array );
			
			$id = $this->DB->getInsertId();

			ipsRegistry::getClass('adminFunctions')->saveAdminLog( sprintf( $this->lang->words['b_maillogadd'], $this->request['mail_subject'] ) );
			
			$this->registry->output->silentRedirect( "{$this->settings['base_url']}app=members&module=bulkmail&section=bulkmail&do=mail_send_start&id={$id}" );
			return;
		}
		else
		{
			if ( ! $id )
			{
				$this->registry->output->global_message = $this->lang->words['b_norecord'];
				$this->_mailForm( $type );
				return;
			}
			
			$this->DB->update( 'bulk_mail', $save_array, 'mail_id=' . $id );
			
			ipsRegistry::getClass('adminFunctions')->saveAdminLog( sprintf( $this->lang->words['b_maillogedit'], $this->request['mail_subject'] ) );
			
			$this->registry->output->redirect( "{$this->settings['base_url']}app=members&module=bulkmail&section=bulkmail&do=mail_send_start&id={$id}", $this->lang->words['b_edited'] );
			return;
		}
	}
	
	/**
	 * Show the edit bulk mail form
	 *
	 * @param	string		[add|edit]
	 * @param	array		Error messages
	 * @return	@e void
	 */
	protected function _mailForm( $type='add', $errors=array() )
	{
		//-----------------------------------------
		// Init some values
		//-----------------------------------------
		
		$id			= intval($this->request['id']);
		
		if ( $type == 'add' )
		{
			$mail			= array();
		}
		else
		{
			$mail 			= $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'bulk_mail', 'where' => 'mail_id='.$this->request['id'] ) );
		}
		
		if ( $this->request['mail_groups'] )
		{
			$mail['mail_groups'] = $this->request['mail_groups'];
		}

		//-----------------------------------------
		// Format mail content
		//-----------------------------------------
		
		$mail_content	= $_POST['mail_content'] ? IPSText::stripslashes($_POST['mail_content']) : $mail['mail_content'];
		$mail_content	= preg_replace( "[^\r]\n", "\r\n", $mail_content );
		
		if ( !$mail_content and $type == 'add' )
		{
			$mail_content = $this->_getDefaultMailContents();
		}
		
		/* Bug report #39173 */
		$mail_content	= IPSText::htmlspecialchars( $mail_content );
		
		//-----------------------------------------
		// Get Filters
		//-----------------------------------------
		
		$opts = unserialize( $mail['mail_opts'] );

		$id = 1;
		$filters = array();
		
		foreach ( IPSLib::getEnabledApplications() as $app )
		{
			$extensionFile = IPSLib::getAppDir( $app['app_directory'] ) . '/extensions/bulkMailFilters.php';
			if ( file_exists( $extensionFile ) )
			{
				$classToLoad = IPSLib::loadLibrary( $extensionFile, 'bulkMailFilters_' . $app['app_directory'] );
				$class       = new $classToLoad( $this->registry );
				
				$filters[ $id ] = array( 'appName' => $app['app_title'], 'appKey' => $app['app_directory'], 'filters' => array() );
				
				foreach ( $class->filters as $f )
				{
					$classToLoad = IPSLib::loadLibrary( $extensionFile, "bulkMailFilter_{$app['app_directory']}_{$f}" );
					$_class = new $classToLoad( $this->registry );
										
					$filters[ $id ]['filters'][] = array( 'title' => $this->lang->words[ "bulkMailFilter_{$app['app_directory']}_{$f}" ], 'field' => $_class->getSettingField( $opts['filters'][ $app['app_directory'] ][ $f ] ) );
				}
				
				$id++;
			}
		}
		
		/* We want the members tab to be first so it shows groups, which will be the most common filter */
		uasort( $filters, create_function( '$a, $b', 'if ( $a[\'appKey\'] == "members" ) { return -1; } else { return 0; }' ) );
		
		//-----------------------------------------
		// Output
		//-----------------------------------------

		$this->registry->output->html .= $this->html->mailForm( $type, $mail, $mail_content, $filters, $errors );
	}
	
	/**
	 * Show the main bulk mail overview screen
	 *
	 * @return	@e void
	 */
	protected function _mailStart()
	{
		$content	= '';
		$st			= intval( $this->request['st'] );
		$perpage	= 50;
		
		/* Get count */
		$items = $this->DB->buildAndFetch( array( 'select' => 'COUNT(*) as cnt',
												  'from'   => 'bulk_mail' ) );
												  		
		//-----------------------------------------
		// Get mail from DB
		// WHERE clause helps query use index properly
		//-----------------------------------------
		
		$this->DB->build( array( 'select' => '*', 'from' => 'bulk_mail', 'where' => 'mail_start > 0', 'order' => 'mail_start DESC', 'limit' => array( $st, $perpage ) ) );
		$this->DB->execute();
		
		while( $r = $this->DB->fetch() )
		{
			$r['_mail_start']	= ipsRegistry::getClass( 'class_localization')->getDate( $r['mail_start'], 'SHORT' );
			$r['_mail_sentto']	= ipsRegistry::getClass('class_localization')->formatNumber( $r['mail_sentto'] ) . ' members';

			$content .= $this->html->mailOverviewRow( $r );
		}
		
		/* Pages */
		$pages = $this->registry->output->generatePagination( array( 'totalItems'			=> $items['cnt'],
																	 'itemsPerPage'			=> $perpage,
																	 'currentStartValue'	=> $st,
																	 'baseUrl'				=> $this->settings['base_url'] . 'app=members&amp;module=bulkmail&do=bulk_mail' ) );

		$this->registry->output->html .= $this->html->mailOverviewWrapper( $content, $pages );
	}
	
	/**
	 * Build the query to retrieve the members
	 *
	 * @param	array 		Filter values
	 * @return	array		Query Data
	 */
	public function _buildMembersQuery( $args = array() )
	{
		$queryData = array( 'add_join' => array(), 'where' => array( "m.allow_admin_mails=1" ), 'group' => 'm.member_id' );
		
		foreach ( $args as $app => $filters )
		{
			$extensionFile = IPSLib::getAppDir( $app ) . '/extensions/bulkMailFilters.php';
			if ( IPSLib::appIsInstalled( $app ) and file_exists( $extensionFile ) )
			{
				$classToLoad = IPSLib::loadLibrary( $extensionFile, 'bulkMailFilters_' . $app );
				$class       = new $classToLoad( $this->registry );
								
				foreach ( $filters as $key => $data )
				{
					$classToLoad = IPSLib::loadLibrary( $extensionFile, "bulkMailFilter_{$app}_{$key}" );
					$_class = new $classToLoad( $this->registry );
					
					$r = $_class->getMembers( $data );
										
					if ( isset( $r['joins'] ) )
					{
						$queryData['add_join'] = array_merge( $queryData['add_join'], $r['joins'] );
					}
					
					if( isset( $r['where'] ) )
					{
						$haveCriteria = TRUE;						
						$queryData['where'] = array_merge( $queryData['where'], $r['where'] );
					}
				}
			}
		}
				
		return $queryData;
	}
	
	/**
	 * Conver the 'quick tags' in the email
	 *
	 * @param 	string		The email contents
	 * @param	array 		Member information
	 * @return	string		The email contents, replaced
	 */
	protected function _convertQuickTags( $contents="", $member=array() )
	{
		foreach ( $this->_getVariableInformation( $member ) as $k => $v )
		{
			$contents = str_replace( '{'.$k.'}', $v, $contents );
		}
	
		return $contents;
	}
	
	/**
	 * Get variable values
	 *
	 * @param	array	Member information
	 * @param	int		0 	All
	 					1	Member Specific only
	 					2	Global only
	 * @retrun	array	key/value pairs
	 */
	protected function _getVariableInformation( $member, $type=0, $html=TRUE )
	{
		$variables = array();
	
		if ( $type == 0 or $type == 2 )
		{
			$variables['board_name']	= str_replace( "&#39;", "'", $this->settings['board_name'] ) ;
			$variables['board_url']		= $this->settings['board_url'] . "/index." . $this->settings['php_ext'] ;
			$variables['reg_total']		= $this->caches['stats']['mem_count'] ;
			$variables['total_posts']	= $this->caches['stats']['total_topics'] + $this->caches['stats']['total_replies'] ;
			$variables['busy_count']	= $this->caches['stats']['most_count'] ;
			$variables['busy_time']		= ipsRegistry::getClass( 'class_localization')->getDate( $this->caches['stats']['most_date'], 'SHORT' );
		}
		
		if ( $type == 0 or $type == 1 )
		{
			$variables['member_id']		= $member['member_id'];
			$variables['member_name']	= $member['members_display_name'];
			$variables['member_joined']	= ipsRegistry::getClass( 'class_localization')->getDate( $member['joined'], 'JOINED', TRUE );
			$variables['member_posts']	= $member['posts'];
			$variables['member_last_visit']	= ipsRegistry::getClass( 'class_localization')->getDate( $member['last_visit'], 'JOINED', TRUE );
			
			$this->registry->class_localization->loadLanguageFile( array( 'public_global' ), 'core', $member['language'] ? $member['language'] : IPSLib::getDefaultLanguage(), TRUE );
			$key = md5( $member['email'] . ':' . $member['members_pass_hash'] );
			$link = $this->registry->output->buildUrl( "app=core&amp;module=global&amp;section=unsubscribe&amp;member={$member['member_id']}&amp;key={$key}", 'publicNoSession' );
			$variables['unsubscribe']	= $html ? "<a href='{$link}'>" . $this->registry->class_localization->words['email_unsubscribe'] . '</a>' : "{$this->registry->class_localization->words['email_unsubscribe']}: {$link}";
		}
		
		return $variables;
	}
	
	/**
	 * Retrieve the 'default' email contents
	 *
	 * @return	string		Default email contents
	 */
	protected function _getDefaultMailContents()
	{
		if ( $this->settings['email_use_html'] )
		{
			return <<<CONTENT
<p>{member_name},</p><p>&nbsp;</p><p>&nbsp;</p>
CONTENT;
		}
		else
		{
			return "{member_name},\n\n";	
		}
	}	
}