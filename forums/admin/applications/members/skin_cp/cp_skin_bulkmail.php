<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * ACP bulk mail skin file
 * Last Updated: $Date: 2013-02-07 11:03:55 -0500 (Thu, 07 Feb 2013) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 11954 $
 *
 */
 
class cp_skin_bulkmail
{
	/**
	 * Registry Object Shortcuts
	 *
	 * @var		$registry
	 * @var		$DB
	 * @var		$settings
	 * @var		$request
	 * @var		$lang
	 * @var		$member
	 * @var		$memberData
	 * @var		$cache
	 * @var		$caches
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $memberData;
	protected $cache;
	protected $caches;
	
	/**
	 * Constructor
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function __construct( ipsRegistry $registry )
	{
		$this->registry 	= $registry;
		$this->DB	    	= $this->registry->DB();
		$this->settings		=& $this->registry->fetchSettings();
		$this->request		=& $this->registry->fetchRequest();
		$this->member   	= $this->registry->member();
		$this->memberData	=& $this->registry->member()->fetchMemberData();
		$this->cache		= $this->registry->cache();
		$this->caches		=& $this->registry->cache()->fetchCaches();
		$this->lang 		= $this->registry->class_localization;
	}

/**
 * Bulk mail start
 *
 * @param	array 		Mail data
 * @param	int			Members to send to
 * @return	string		HTML
 */
public function mailSendStart( $mail, $members, $countmembers ) {

$action = $this->settings['mandrill_api_key'] ? 'mail_send_mandrill' : 'mail_send_complete';

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['b_title']}</h2>
</div>
<form name='theAdminForm' id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do={$action}' method='post'>
	<input type='hidden' name='id' value='{$mail['mail_id']}' />
	<input type='hidden' name='_admin_auth_key' value='{$this->registry->getClass('adminFunctions')->_admin_auth_key}' />
	<input type='hidden' name='countmembers' value='{$countmembers}' />
	<div class='acp-box'>
		<h3>{$this->lang->words['b_title']}</h3>
		<div class='ipsTabBar' id='tabstrip_mytabs'>
			<ul>
				<li id='tab_1'>{$this->lang->words['bulkmail_preview']}</li>
				<li id='tab_2'>{$this->lang->words['bulkmail_list']} ({$countmembers})</li>
			</ul>
		</div>
		<div class='ipsTabBar_content' id='tabstrip_mytabs_content'>
			<div id='tab_1_content'>
				<table class='ipsTable double_pad' cellspacing='0' cellpadding='0'>
					<tr>
						<th colspan='2'>{$this->lang->words['b_maildetails']}</th>
					</tr>
					<tr>
				 		<td style='width: 40%;'>
							<label>{$this->lang->words['b_subject']}</label>
						</td>
				 		<td style='width: 60%'>
				 			{$mail['mail_subject']}
				 		</td>
				 	</tr>
					<tr>
				 		<td colspan='2'>
							<label>{$this->lang->words['b_content']}</label>
							<br />
							<div style='margin-top: 10px; margin-left: 15px;'>
								<iframe width='100%' height='400px' scrollbars='auto' src='{$this->settings['base_url']}{$this->form_code}&amp;do=mail_preview_do&amp;id={$mail['mail_id']}'></iframe>
							</div>
							<br />
							<span class='desctext'>{$this->lang->words['b_preview_desc']}</span>
						</td>
				 	</tr>
				</table>
			</div>
			<div id='tab_2_content'>
				<table class='ipsTable' id='recipientsTable'>
HTML;

			$IPBHTML .= $this->mss_recipients( 1, $countmembers, $members );
			

			$IPBHTML .= <<<HTML
				</table>
			</div>
		</div>
HTML;
	if ( !$this->settings['mandrill_api_key'] )
	{
		$IPBHTML .= <<<HTML
		<table class='ipsTable double_pad' cellspacing='0' cellpadding='0'>
			<tr>
				<th colspan='2'>{$this->lang->words['b_sending']}</th>
			</tr>
			<tr>
		 		<td colspan='2'>
					<div class='information-box'>
						{$this->lang->words['b_sending_info']}
					</div>
				</td>
		 	</tr>
		</table>
HTML;
	}
	
	$IPBHTML .= <<<HTML
		<div class='acp-actionbar'>
HTML;
		if ( $this->settings['mandrill_api_key'] )
		{
			$IPBHTML .= <<<HTML
			<input type='submit' value='{$this->lang->words['mandrill_send']}' class='realbutton' />
HTML;
		}
		else
		{
			$IPBHTML .= <<<HTML
			{$this->lang->words['b_percycle']} <input type='text' class='input_text' size='5' name='pergo' value='20' /> &nbsp; <input type='submit' value='{$this->lang->words['b_mailbutton']}' class='realbutton' />
HTML;
		}
		$IPBHTML .= <<<HTML
		 {$this->lang->words['or']} <a href="{$this->settings['base_url']}{$this->form_code}&amp;do=mail_edit&id={$mail['mail_id']}" class='button redbutton primary'>{$this->lang->words['b_edit']}</a>
		</div>
	</div>
</form>

<script type='text/javascript'>
     jQ("#tabstrip_mytabs").ipsTabBar({ tabWrap: "#tabstrip_mytabs_content" });
     
     function pagLink( page )
     {
     	console.log( "{$this->settings['base_url']}".replace(/&amp;/g, '&') + "app=members&module=ajax&section=bulkmail&do=recipients&id={$mail['mail_id']}&page="+page+"&countmembers={$countmembers}&secure_key=" + ipb.vars['md5_hash'] );
     	new Ajax.Request( "{$this->settings['base_url']}".replace(/&amp;/g, '&') + "app=members&module=ajax&section=bulkmail&do=recipients&id={$mail['mail_id']}&page="+page+"&countmembers={$countmembers}&secure_key=" + ipb.vars['md5_hash'],
		{
			evalJSON: 'force',
			onSuccess: function( t )
			{
				$('recipientsTable').innerHTML = t.responseText;
			}
		});
     }
</script>
HTML;

//--endhtml--//
return $IPBHTML;
}

function mss_recipients( $currentPage, $countmembers, $members )
{
$IPBHTML = "";
//--starthtml--//

if ( $countmembers > 10000 )
{
	$pages = ceil( $countmembers / 10000 );
	$nextPage = $currentPage + 1;
	$nextPageLink = ( $currentPage == $pages ) ? '' : "<a href='#' onclick='pagLink({$nextPage})' class='mini_button'>Next &rarr;</a>";
	$prevPage = $currentPage - 1;
	$prevPageLink = ( $currentPage == 1 ) ? '' : "<a href='#' onclick='pagLink({$prevPage})' class='mini_button'>&larr; Previous</a>";
	
	$IPBHTML .= "<tr><td style='text-align:center' colspan='4'>{$prevPageLink} <em>Page {$currentPage} of {$pages}</em> {$nextPageLink}</td></tr>";
}

$count = 0;
$sofar = 0;
	
foreach ( $members as $member )
{
	$sofar++;
	
	if ( $count == 0 )
	{
		$IPBHTML .= "<tr>";
	}
				
	$IPBHTML .= <<<HTML
		<td>
			<a href='{$this->settings['base_url']}app=members&module=members&section=members&do=viewmember&member_id={$member['member_id']}' target='_blank'>{$member['members_display_name']}</a> <span class='desctext'>({$member['email']})</span>
		</td>
HTML;
	if ( $count == 3 )
	{
		$IPBHTML .= "</tr>";
		$count = 0;
	}
	else
	{
		$count++;
	}
	
	if ( $sofar >= 10000 )
	{
		break;
	}
}

while ( $count != 0 and $count < 4 )
{
	$IPBHTML .= "<td></td>";
	$count++;
}

//--endhtml--//
return $IPBHTML;
}

/**
 * Bulk mail form
 *
 * @param	string		Type (add|edit)
 * @param	array 		Mail data
 * @param	string		Mail content
 * @param	array		Filter classes
 * @param	array		Error messages
 * @return	string		HTML
 */
public function mailForm( $type, $mail, $mail_content, $filters, $errors ) {

$dd_ltmt	= array(
				  0 => array( 'lt' , $this->lang->words['b_lessthan'] ),
				  1 => array( 'mt' , $this->lang->words['b_morethan'] )
				);
						
if ( $type == 'add' )
{
	$title			= $this->lang->words['b_create'];
	$button			= $this->lang->words['b_proceed'];
	$html_checked	= 0;
}
else
{
	$title			= $this->lang->words['b_editstored'];
	$button			= $this->lang->words['b_edit'];
	
	//-----------------------------------------
	// Unpack more..
	//-----------------------------------------
	
	$tmp = unserialize( stripslashes( $mail['mail_opts'] ) );
	
	if ( is_array( $tmp ) and count ( $tmp ) )
	{
		foreach( $tmp as $k => $v )
		{
			if ( ! $mail[ $k ] )
			{
				$mail[ $k ] = $v;
			}
		}
	}
	
	$html_checked	= $mail['mail_html_on'];
	
}


$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/editor/composite.php', 'classes_editor_composite' );
$editor = new $classToLoad();
$editor->setContent( $mail_content );


$form						= array();
$form['groups']				= '';
$form['mail_subject']		= $this->registry->output->formInput( 'mail_subject', htmlspecialchars( IPSText::stripslashes( $_POST['mail_subject'] ? $_POST['mail_subject'] : $mail['mail_subject'] ), ENT_QUOTES ) );
$form['mail_content']		= $editor->show('mail_content');
$form['mail_content_plain']	= $this->registry->output->formTextarea( 'mail_content_plain', $mail_content, 60, 14, '', 'mail_content_plain', "' style='width: 100%'" ); // Hacky CSS thing, but eh
$form['mail_post_ltmt']		= $this->registry->output->formDropdown( 'mail_post_ltmt', $dd_ltmt, $_POST['mail_post_ltml'] ? $_POST['mail_post_ltml'] : $mail['mail_post_ltmt'] );
$form['mail_filter_post']	= $this->registry->output->formSimpleInput( "mail_filter_post", $_POST['mail_filter_post'] ? $_POST['mail_filter_post'] : $mail['mail_filter_post'], 7 );
$form['mail_visit_ltmt']	= $this->registry->output->formDropdown( 'mail_visit_ltmt', $dd_ltmt, $_POST['mail_visit_ltml'] ? $_POST['mail_visit_ltml'] : $mail['mail_visit_ltmt'] );
$form['mail_filter_visit']	= $this->registry->output->formSimpleInput( "mail_filter_visit", $_POST['mail_filter_visit'] ? $_POST['mail_filter_visit'] : $mail['mail_filter_visit'], 7 );
$form['mail_joined_ltmt']	= $this->registry->output->formDropdown( 'mail_joined_ltmt', $dd_ltmt, $_POST['mail_joined_ltml'] ? $_POST['mail_joined_ltml'] : $mail['mail_joined_ltmt'] );
$form['mail_filter_joined']	= $this->registry->output->formSimpleInput( "mail_filter_joined", $_POST['mail_filter_joined'] ? $_POST['mail_filter_joined'] : $mail['mail_filter_joined'], 7 );
$form['tags']				= $this->registry->output->formInput( 'mandrill_tags', $_POST['mandrill_tags'] ? $_POST['mandrill_tags'] : is_array( $mail['mandrill_tags'] ) ? implode( ',', $mail['mandrill_tags'] ) : '' );

foreach( $this->cache->getCache('group_cache') as $g )
{
	if ( $g['g_id'] == $this->settings['guest_group'] )
	{
		continue;
	}
	
	$checked = 0;
	
	if ( $mail['mail_groups'] )
	{
		if ( strstr( ',' . $mail['mail_groups'] . ',', ',' . $g['g_id'] . ',' ) )
		{
			$checked = 1;
		}
	}
	
	$form['groups'] .=  $this->registry->output->formCheckbox( 'sg_' . $g['g_id'], $checked ) . "&nbsp;&nbsp;<b>{$g['g_title']}</b><br />";
}

$standardStyle = '';
$plainStyle = 'display:none';
$htmlOn = 0;
if ( $mail['mail_html_on'] or $this->request['mail_html_on'] or !$this->settings['email_use_html'] )
{
	$standardStyle = 'display:none';
	$plainStyle = '';	
	$htmlOn = 1;
}
			
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$title}</h2>
</div>

HTML;

if ( !empty( $errors ) )
{
	$errors = implode( '<br />', $errors );
	$IPBHTML .= <<<HTML
	<div class='warning'>
		{$errors}
	</div>
	<br />
HTML;
}

$IPBHTML .= <<<HTML
<form name='theAdminForm' id='adminform' action='{$this->settings['base_url']}{$this->form_code}&amp;do=mail_save' method='post'>
	<input type='hidden' name='id' value='{$mail['mail_id']}' />
	<input type='hidden' name='type' value='{$type}' />
	<input type='hidden' name='_admin_auth_key' value='{$this->registry->getClass('adminFunctions')->_admin_auth_key}' />
	<input type='hidden' name='mail_html_on' id='mail_html_on' value='{$htmlOn}' />
	
	<div class='acp-box'>
		<h3>{$title}</h3>
		
		<table class='ipsTable double_pad' cellspacing='0' cellpadding='0'>
		 	<tr>
		 		<th colspan='2'>{$this->lang->words['b_step1_title']}</th>
		 	</tr>
			<tr>
		 		<td class='field_title'>
					<strong class='title'>{$this->lang->words['b_subject']}</strong>
				</td>
		 		<td class='field_field'>
		 			{$form['mail_subject']}
		 		</td>
		 	</tr>
			<tr>
		 		<td class='field_title'>
					<strong class='title'>{$this->lang->words['b_content']}</strong>
				</td>
				<td class='field_field'>
					<div id='editor_standard' style='{$standardStyle}'>
						{$form['mail_content']}<br />
						<p style='margin-top: 5px' class='desctext'>
							<span class='clickable mini_button' onclick='variablesPopup()'>{$this->lang->words['b_var_link']}</span> <span class='clickable mini_button' onclick="editorModeToggle('plain')">{$this->lang->words['b_html']}</span>
						</p>
					</div>
					<div id='editor_plain' style='{$plainStyle}'>
						{$form['mail_content_plain']}<br />
						<p style='margin-top: 5px' class='desctext'>
							<span class='clickable mini_button' onclick='variablesPopup()'>{$this->lang->words['b_var_link']}</span> 
HTML;
							if ( $this->settings['email_use_html'] )
							{
								$IPBHTML .= <<<HTML
								<span class='clickable mini_button' onclick="editorModeToggle('standard')">{$this->lang->words['b_standard']}</span>
HTML;
							}
							
							$IPBHTML .= <<<HTML
						</p>
					</div>
				</td>
		 	</tr>
HTML;
	if ( $this->settings['mandrill_api_key'] )
	{
		$IPBHTML .= <<<HTML
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['mandrill_tags']}</strong>
				</td>
		 		<td class='field_field'>
		 			{$form['tags']}<br />
		 			<span class='desctext'>{$this->lang->words['mandrill_tags_desc']}</span>
		 		</td>
		 	</tr>
HTML;
	}
	$IPBHTML .= <<<HTML
		 	<tr>
		 		<th colspan='2'>{$this->lang->words['b_step2']}</th>
		 	</tr>
		</table>
		<div class='information-box'>{$this->lang->words['bulkmail_notes_override']}</div>
		<div class='ipsTabBar with_left with_right' id='tabstrip_mytabs'>
			<span class='tab_left'>&laquo;</span>
			<span class='tab_right'>&laquo;</span>
			<ul>
HTML;
			foreach ( $filters as $id => $data )
			{
				$appName = ipsRegistry::$applications[ $app ]['app_title'];
				$IPBHTML .= <<<HTML
				<li id='tab_{$id}'>{$data['appName']}</li>
HTML;
			}
				$IPBHTML .= <<<HTML
			</ul>
		</div>
		<div class='ipsTabBar_content' id='tabstrip_mytabs_content'>
HTML;
			foreach ( $filters as $id => $data )
			{
				$IPBHTML .= <<<HTML
			<div id='tab_{$id}_content'>
				<table class='ipsTable double_pad'>
HTML;
				foreach ( $data['filters'] as $f )
				{	
					$IPBHTML .= <<<HTML
					<tr>
						<td class='field_title'><strong class='title'>{$f['title']}</strong></td>
						<td class='field_field'>
							{$f['field']}
						</td>
					</tr>
HTML;
				}
				
				$IPBHTML .= <<<HTML
				</table>
			</div>
HTML;
			}
				$IPBHTML .= <<<HTML
		</div>
		<div class='acp-actionbar'>
			<input class='realbutton' type='submit' value='{$button}' />
		</div>
	</div>
</form>

<div id='quicktags-popup' style='display:none'>
	<div class='acp-box'>
		<h3>{$this->lang->words['b_qtag']}</h3>
		<table class='ipsTable double_pad'>
			<tr>
				<td colspan='4'>{$this->lang->words['b_qtag_info']}</td>
			</tr>
			<tr>
				<td><strong>{member_id}</strong></td>
				<td>{$this->lang->words['b_qid']}</td>
				<td><strong>{member_name}</strong></td>
				<td>{$this->lang->words['b_qmname']}</td>
			</tr>
			<tr>
				<td><strong>{member_joined}</strong></td>
				<td>{$this->lang->words['b_qjoin']}</td>
				<td><strong>{member_last_visit}</strong></td>
				<td>{$this->lang->words['b_lastactive']}</td>
			</tr>
			<tr>
				<td><strong>{member_posts}</strong></td>
				<td>{$this->lang->words['b_qposts']}</td>
				<td><strong>{reg_total}</strong></td>
				<td>{$this->lang->words['b_qmtotal']}</td>
			</tr>
			<tr>
				<td><strong>{board_name}</strong></td>
				<td>{$this->lang->words['b_qbname']}</td>
				<td><strong>{board_url}</strong></td>
				<td>{$this->lang->words['b_qboardurl']}</td>
			</tr>

			<tr>
				<td><strong>{busy_count}</strong></td>
				<td>{$this->lang->words['b_qonline']}</td>
				<td><strong>{busy_time}</strong></td>
				<td>{$this->lang->words['b_qonlinetime']}</td>
			</tr>
			<tr>
				<td><strong>{total_posts}</strong></td>
				<td>{$this->lang->words['b_qptotal']}</td>
			</tr>																		
		 </table>
	</div>
</div>

<script type='text/javascript'>
	function variablesPopup()
	{
		new ipb.Popup( 'variablespopup', { type: 'pane', stem: true, hideAtStart: false, w: '900px', h: '800px', initial: $('quicktags-popup').innerHTML, modal: false } );
	}
	
	function editorModeToggle( mode )
	{
		if ( mode == 'standard' )
		{
			$('editor_standard').style.display = '';
			$('editor_plain').style.display = 'none';
			
			ipb.textEditor.getEditor().insert( $('mail_content_plain').value, false, true );
			$('mail_html_on').value = 0;
		}
		else
		{
			$('editor_standard').style.display = 'none';
			$('editor_plain').style.display = '';
			
			$('mail_content_plain').value = ipb.textEditor.getEditor().getText();
			$('mail_html_on').value = 1;
		}
	}
	
     jQ("#tabstrip_mytabs").ipsTabBar({ tabWrap: "#tabstrip_mytabs_content" });
	
</script>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * HTML to show in popup
 *
 * @return	string		HTML
 */
public function mailPopupContent() {

$IPBHTML = "";
//--starthtml--//

$IPS_DOC_CHAR_SET = IPS_DOC_CHAR_SET;

$IPBHTML .= <<<HTML
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset={$IPS_DOC_CHAR_SET}" />
	</head>
	<body onload='doitdude()'>
		<script type='text/javascript'>
			posty = opener.thisval;
			pisty = opener.thatval;
			   
			function doitdude()
			{
				$('theForm').action		= '{$this->settings['base_url']}{$this->form_code_js}&do=mail_preview_do'.replace( /&amp;/g, '&' );
				$('theForm_text').value	= posty;
				$('theForm_html').value	= pisty;
				$('theForm').submit();
			}
		</script>
		<form name='peekaboo' id='theForm'  method='post'>
		<input type='hidden' id='theForm_text' name='text' />
		<input type='hidden' id='theForm_html' name='html' />
		</form>
	</body>
</html>
HTML;

//--endhtml--//
return $IPBHTML;
}


/**
 * Saved bulk mails overview
 *
 * @param	string		Content
 * @return	string		HTML
 */
public function mailOverviewWrapper( $content, $pages ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['b_title']}</h2>
	<ul class='context_menu'>
		<li><a href='{$this->settings['base_url']}{$this->form_code}&amp;do=mail_new'><img src='{$this->settings['skin_acp_url']}/images/icons/email.png' alt='' /> {$this->lang->words['b_create']}</a></li>
HTML;
	if ( $this->settings['mandrill_api_key'] )
	{
		$IPBHTML .= <<<HTML
		<li><a href='http://external.ipslink.com/ipboard30/landing/?p=mandrill_dashboard' target='_blank'><img src='{$this->settings['skin_acp_url']}/images/icons/cog.png' alt='' /> {$this->lang->words['mandrill_dashboard']}</a></li>
HTML;
	}
	$IPBHTML .= <<<HTML
	</ul>
</div>

<div class='acp-box'>
	<h3>{$this->lang->words['b_stored']}</h3>
	<table class='ipsTable'>
		<tr>
			<th width='1%'>&nbsp;</th>
			<th width='30%'>{$this->lang->words['b_lsubject']}</th>
			<th width='15%'>{$this->lang->words['b_lsenton']}</th>
			<th width='15%'>{$this->lang->words['b_lsentto']}</th>
			<th width='15%'>{$this->lang->words['b_ltime']}</th>
			<th class='col_buttons'>&nbsp;</th>
		</tr>
HTML;
		if( $content )
		{
			$IPBHTML .= <<<HTML
 				{$content}
HTML;
		}
		else
		{
			$IPBHTML .= <<<HTML
			<tr>
				<td colspan='6' class='no_messages'>{$this->lang->words['b_nobulk']}</td>
			</tr>
HTML;
		}
		
		$IPBHTML .= <<<HTML
	
 	</table>
</div>
<br />
{$pages}
HTML;

//--endhtml--//
return $IPBHTML;
}


/**
 * Bulk mail row
 *
 * @param	array 		Mail data
 * @return	string		HTML
 */
public function mailOverviewRow( $r ) {
$IPBHTML = "";
//--starthtml--//

$inprogress	= "";
$time_taken	= "";

if ( $r['mail_updated'] == $r['mail_start'] )
{
	$time_taken = $this->lang->words['b_notyet'];
}
else
{
	$time_taken = intval($r['mail_updated'] - $r['mail_start']);
	
	if ( $time_taken < 0 )
	{
		$time_taken = 0;
	}
	
	if ( $time_taken )
	{
		$time_taken = ceil( $time_taken / 60 );
	}
	
	$time_taken .= $this->lang->words['b_minutes'];
}

if ( $r['mail_active'] )
{
	$inprogress = " ( {$this->lang->words['b_inprogress']} - <a href='#' class='ipsBadge badge_red' onclick=\"acp.confirmDelete('{$this->settings['base_url']}{$this->form_code_js}&do=mail_send_cancel', '{$this->lang->words['b_cancelconfirm']}'); return false;\">{$this->lang->words['b_cancel']}</a> )";
}


$IPBHTML .= <<<HTML
<tr class='ipsControlRow'>
  <td>&nbsp;</td>
  <td><span class='larger_text'><a href='{$this->settings['base_url']}{$this->form_code}&do=mail_edit&id={$r['mail_id']}' title='{$this->lang->words['b_editdot']}'>{$r['mail_subject']}</a></span> {$inprogress}</td>
  <td>{$r['_mail_start']}</td>
  <td>{$r['_mail_sentto']}</td>
  <td>{$time_taken}</td>
  <td class='col_buttons'>
	<ul class='ipsControlStrip'>
		<li class='i_refresh'>
			<a href='{$this->settings['base_url']}{$this->form_code}&do=mail_send_start&id={$r['mail_id']}' title='{$this->lang->words['b_resenddot']}'>{$this->lang->words['b_resenddot']}</a>
		</li>
		<li class='i_edit'>
			<a href='{$this->settings['base_url']}{$this->form_code}&do=mail_edit&id={$r['mail_id']}' title='{$this->lang->words['b_editdot']}'>{$this->lang->words['b_editdot']}</a>
		</li>
		<li class='i_delete'>
			<a href='#' onclick='return acp.confirmDelete("{$this->settings['base_url']}{$this->form_code}&do=mail_delete&id={$r['mail_id']}");' title='{$this->lang->words['b_deletedot']}'>{$this->lang->words['b_deletedot']}</a>
		</li>
	</ul>
  </td>
</tr>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Mandrill Signup
 *
 * @return	string		HTML
 */
public function mandrillSignup( $error ) {

$form['username'] = $this->registry->output->formInput( 'username' );
$form['api_key'] = $this->registry->output->formInput( 'api_key' );
$form['smtp'] = $this->registry->output->formYesNo( 'smtp', 1 );

$apikeydesc = sprintf( $this->lang->words['mandrill_api_key_desc'], "<a href='http://external.ipslink.com/ipboard30/landing/?p=mandrill_dashboard' target='_blank'>{$this->lang->words['mandrill_dashboard']}</a>" );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['mandrill']}</h2>
</div>

<div class='information-box'>
	{$this->lang->words['mandrill_blurb']}
	<br /><br />
	<a href='http://external.ipslink.com/ipboard30/landing/?p=mandrill_signup' target='_blank'>{$this->lang->words['mandrill_signup']}</a>
</div>
<br />

HTML;

if ( $error )
{
	$IPBHTML .= <<<HTML
	<div class='warning'>
		{$this->lang->words[ $error ]}
	</div>
	<br />
HTML;
}

$IPBHTML .= <<<HTML
<form action='{$this->settings['base_url']}app=core&amp;module=applications&amp;section=enhancements&amp;do=save&amp;service=enhancements_members_mandrill' method='POST'>
	<div class='acp-box'>
		<h3>{$this->lang->words['mandrill_settings']}</h3>
		<table class='ipsTable'>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['mandrill_username']}</strong>
				</td>
				<td>
					{$form['username']}
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['mandrill_api_key']}</strong>
				</td>
				<td>
					{$form['api_key']}<br />
					<span class='desctext'>{$apikeydesc}</span>
				</td>
			</tr>
			<tr>
				<td class='field_title'>
					<strong class='title'>{$this->lang->words['mandrill_smtp']}</strong>
				</td>
				<td>
					{$form['smtp']}<br />
					<span class='desctext'>{$this->lang->words['mandrill_smtp_desc']}</span>
				</td>
			</tr>
		</table>
		<div class='acp-actionbar'>
			<input type='submit' value='{$this->lang->words['enhancements_save']}' class='button primary' />
		</div>
	</div>
</form>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Mandrill Manage
 *
 * @return	string		HTML
 */
public function mandrillManage() {

$mandrillAll = ( $this->settings['mail_method'] == 'smtp' and $this->settings['smtp_host'] == 'smtp.mandrillapp.com' );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['mandrill']}</h2>
	<ul class='context_menu'>
		<li><a href='{$this->settings['base_url']}app=members&amp;module=bulkmail&amp;section=bulkmail&amp;do=mail_new'><img src='{$this->settings['skin_acp_url']}/images/icons/email.png' alt='' /> {$this->lang->words['b_create']}</a></li>
	</ul>
</div>

<div class='acp-box'>
	<h3>{$this->lang->words['mandrill_settings']}</h3>
	<table class='ipsTable'>
		<tr>
			<td class='field_title'>
				<strong class='title'>{$this->lang->words['mandrill_api_key']}</strong>
			</td>
			<td>
				{$this->settings['mandrill_api_key']} (<a href='{$this->settings['base_url']}app=core&amp;module=applications&amp;section=enhancements&amp;do=save&amp;service=enhancements_members_mandrill&amp;off=1'>{$this->lang->words['mandrill_off']}</a>)
			</td>
		</tr>
		<tr>
			<td class='field_title'>
				<strong class='title'>{$this->lang->words['mandrill_smtp']}</strong>
			</td>
			<td>
HTML;
			if ( $mandrillAll )
			{ 
				$IPBHTML .= <<<HTML
				{$this->lang->words['mandrill_all_on']} (<a href='{$this->settings['base_url']}app=core&amp;module=settings&amp;section=settings&amp;do=findsetting&amp;key=email'>{$this->lang->words['mandrill_all_change']}</a>)
HTML;
			}
			else
			{
				$IPBHTML .= <<<HTML
				{$this->lang->words['mandrill_all_off']} (<a href='{$this->settings['base_url']}app=core&amp;module=applications&amp;section=enhancements&amp;do=save&amp;service=enhancements_members_mandrill&amp;smtp_on=1'>{$this->lang->words['mandrill_all_enable']}</a>)
HTML;
			}
	$IPBHTML .= <<<HTML
			</td>
		</tr>
	</table>
</div>
HTML;

//--endhtml--//
return $IPBHTML;
}

}