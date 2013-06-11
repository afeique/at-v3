<?php
/**
 * <pre>
 * Invision Power Services
 * IP.SEO ACP Skin - SEO
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2010-2011 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.SEO
 * @link		http://www.invisionpower.com
 * @version		$Revision: 10721 $
 */
 
class cp_skin_seo
{

	/**
	 * Constructor
	 *
	 * @param	object		Registry object
	 * @return	void
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

//===========================================================================
// Show Dashboard
//===========================================================================
function dashboard( $messages, $ignores ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['attention_title']}</h2>
HTML;
	if ( !empty( $ignores ) )
	{
		$IPBHTML .= <<<HTML
	<div class='ipsActionBar clearfix'>
		<ul>
			<li class='ipsActionButton'><a href='{$this->settings['base_url']}app=core&module=seo&section=advice&do=clear_warnings'><img src='{$this->settings['skin_acp_url']}/images/icons/arrow_refresh.png' /> {$this->lang->words['attention_clear_warnings']}</a></li>
		</ul>
	</div>
HTML;
	}
	$IPBHTML .= <<<HTML
</div>

<div class='acp-box'>
	<h3>{$this->lang->words['attention_title']}</h3>
	<table class='ipsTable'>
HTML;
		if ( !empty( $messages ) )
		{
			foreach ( $messages as $message )
			{
				$IPBHTML .= <<<HTML
			<tr class='ipsControlRow'>
				<td style='width: 18px'><img src='{$this->settings['skin_app_url']}images/{$message['level']}.png' /></td>
				<td><span class='larger_text'>{$this->lang->words[ 'atn_' . $message['key'] . '_title' ]}</span><br /><span class='desctext'>{$this->lang->words[ 'atn_' . $message['key'] . '_desc' ]}</span></td>
				<td class='col_buttons'>
					<ul class='ipsControlStrip'>
						<li class='i_cog'><a href='{$this->settings['base_url']}{$message['fix']}' title='{$this->lang->words['attention_fix']}'>{$this->lang->words['attention_fix']}</a></li>
						<li class='i_delete'><a href='{$this->settings['base_url']}app=core&module=seo&section=advice&do=ignore&key={$message['key']}' title='{$this->lang->words['attention_ignore']}'>{$this->lang->words['attention_ignore']}</a></li>
					</ul>
				</td>
			</tr>
HTML;
			}
		}
		else
		{
			$IPBHTML .= <<<HTML
		<tr>
			<td class='no_messages'>{$this->lang->words['attention_none']}</td>
		</tr>
HTML;
		}
		
		$IPBHTML .= <<<HTML
	</table>
</div>

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Meta Tags
//===========================================================================
function metaTags( $metaTags ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['meta_title']}</h2>
	<div class='ipsActionBar clearfix'>
		<ul>
			<li class='ipsActionButton'><a href='{$this->settings['base_url']}app=core&module=templates&section=meta&do=add'><img src='{$this->settings['skin_acp_url']}/images/icons/add.png' /> {$this->lang->words['meta_add']}</a></li>
			<li class='ipsActionButton'><a href='{$this->settings['public_url']}app=core&module=global&section=meta&do=init' target='_blank'><img src='{$this->settings['skin_app_url']}/images/wand.png' /> {$this->lang->words['meta_magic']}</a></li>
		</ul>
	</div>
</div>

<div class='acp-box'>
	<h3>{$this->lang->words['meta_title']}</h3>
	<table class='ipsTable'>
HTML;
HTML;
		if ( !empty( $metaTags ) )
		{
			foreach ( $metaTags as $page => $tags )
			{
				$encodedPage = IPSText::base64_encode_urlSafe( $page );
				
				$IPBHTML .= <<<HTML
		<tr class='ipsControlRow'>
			<th class='subhead' colspan='2'>{$page}</th>
			<th class='subhead col_buttons'>
				<ul class='ipsControlStrip'>
					<li class='i_edit'><a href='{$this->settings['base_url']}app=core&module=templates&section=meta&do=edit&page={$encodedPage}'>&nbsp;</a></li>
					<li class='i_delete'><a href='{$this->settings['base_url']}app=core&module=templates&section=meta&do=delete&page={$encodedPage}'>&nbsp;</a></li>
				</ul>
			</th>
		</tr>
HTML;

				foreach ( $tags as $title => $content )
				{
					$IPBHTML .= <<<HTML
		<tr>
			<td>{$title}</td>
			<td>{$content}</td>
			<td class='col_buttons'>&nbsp;</th>
		</tr>
HTML;
				}

			}
		}
		else
		{
			$IPBHTML .= <<<HTML
		<tr>
			<td class='no_messages'>{$this->lang->words['meta_none']} <a href='{$this->settings['base_url']}app=core&module=templates&section=meta&do=add' class='mini_button'>{$this->lang->words['meta_none_add']}</a></td>
		</tr>
HTML;
		}
		
		$IPBHTML .= <<<HTML
	</table>
</div>

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Meta Tag Form
//===========================================================================
function metaTagForm( $page, $tags ) {

$form['page'] = $this->registry->output->formInput( 'page', $page );

$startID = 0;

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<form id='tags-form' action='{$this->settings['base_url']}&amp;module=templates&amp;section=meta&amp;do=save' method='post'>
	<input type='hidden' name='old-page' value='{$page}' />

	<div class='section_title'>
		<h2>{$this->lang->words['meta_add']}</h2>
		<div class='ipsActionBar clearfix'>
			<ul>
				<li class='ipsActionButton'><a href='#' onclick="$('tags-form').submit();"><img src='{$this->settings['skin_acp_url']}/images/icons/tick.png' alt='' /> {$this->lang->words['meta_save']}</a></li>
			</ul>
		</div>
	</div>
	
	<div class='acp-box'>
		<h3>{$this->lang->words['meta_page']}</h3>
		<table class='ipsTable double_pad'>
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['meta_page']}</strong></td>
				<td class='field_field'>
					{$form['page']}<br />
					<span class='desctext'>{$this->lang->words['meta_page_desc']}</td>
				</td>
			</tr>
		</table>
	</div>
	<br />
	
	<div class='acp-box'>
		<h3>{$this->lang->words['meta_tags']}</h3>
		<table class='ipsTable double_pad' id='tags'>
			<tr>
				<th style='width='2%'>&nbsp;</th>
				<th>{$this->lang->words['meta_tag_title']}<br /><span class='desctext'>{$this->lang->words['meta_tag_title_desc']}</span></th>
				<th>{$this->lang->words['meta_tag_content']}</th>
				<th class='col_buttons'>&nbsp;</th>
			</tr>
HTML;

	foreach ( $tags as $title => $content )
	{
		$IPBHTML .= <<<HTML
			<tr class='ipsControlRow' id='tag_{$startID}'>
				<td>&nbsp;</td>
				<td id='display-title-{$startID}'><input name='title-{$startID}' id='title-{$startID}' value='{$title}' /></td>
				<td id='display-content-{$startID}'><textarea name='content-{$startID}' id='content-{$startID}'  rows='10' cols='40'>{$content}</textarea></td>
				<td>
					<ul class='ipsControlStrip'>
						<li class='i_delete'><a href='#' onclick='removeTag({$startID})'>&nbsp;</a></li>
					</ul>
				</td>
			</tr>
HTML;
		$startID++;
	}

$IPBHTML .= <<<HTML
		</table>
		<div class='acp-actionbar'>
			<a href='#' class='button' onclick='addTag()'>{$this->lang->words['meta_tag_add']}</a>
		</div>
	</div>
	
</form>

<script type='text/javascript'>

	var next = {$startID};
	
	function addTag()
	{		
		var row = $('tags').insertRow( $('tags').rows.length );
		row.id = 'tag_' + next;
		row.className = 'ipsControlRow';
		row.style.display = 'none';
								
		var cell_blank = row.insertCell(0);
		
		var cell_title = row.insertCell(1);
		cell_title.id = 'display-title-' + _popup;
		cell_title.innerHTML = "<input name='title-"+next+"' id='title-"+next+"' />";
		
		var cell_content = row.insertCell(2);
		cell_content.id = 'display-content-' + _popup;
		cell_content.innerHTML = "<textarea name='content-"+next+"' id='content-"+next+"' rows='10' cols='40'></textarea>";
		
		var cell_delete = row.insertCell(3);
		cell_delete.innerHTML = "<ul class='ipsControlStrip'><li class='i_delete'><a onclick='removeTag("+next+")' style='cursor:pointer'>&nbsp;</a></li></ul>";
		
		new Effect.Appear( row, {duration:0.5} );
		
		next++;
	}
	
	function removeTag( id )
	{
		$( 'title-' + id ).value = '';
		$( 'content-' + id ).value = '';
		
		new Effect.Fade( $('tag_'+id), {duration:0.5} );
	}

</script>

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Add Meta Tag
//===========================================================================
function addTag( $popup, $title='', $content='' ) {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='acp-box'>
	<h3>
HTML;
	if ( $title )
	{
		$IPBHTML .= <<<HTML
		{$this->lang->words['meta_tag_edit']}
HTML;
	}
	else
	{
		$IPBHTML .= <<<HTML
		{$this->lang->words['meta_tag_add']}
HTML;
	}
	$IPBHTML .= <<<HTML
	</h3>
	<table class="ipsTable double_pad" style='width:100%'>
		<tr>
			<td class='field_title'><strong class='title'>{$this->lang->words['meta_tag_title']}</strong></td>
			<td class='field_field'>
				<input id='input-title-{$popup}' value='{$title}' /><br />-{$popup}-
				<span class='desctext'>{$this->lang->words['meta_tag_title_desc']}</span>
			</td>
		</tr>
		<tr>
			<td class='field_title'><strong class='title'>{$this->lang->words['meta_tag_content']}</strong></td>
			<td class='field_field'><textarea id='input-content-{$popup}' rows='10' cols='40'>{$content}</textarea></td>
		</tr>
	</table>
	<div class='acp-actionbar'>
HTML;
	if ( $title )
	{
		$IPBHTML .= <<<HTML
		<input type='button' id='popup-save' onclick='doEditTag()' value='{$this->lang->words['meta_save']}' class='realbutton' />
HTML;
	}
	else
	{
		$IPBHTML .= <<<HTML
		<input type='button' id='popup-save' onclick='saveTag()' value='{$this->lang->words['meta_save']}' class='realbutton' />
HTML;
	}
	$IPBHTML .= <<<HTML
	</div>
</div>

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Acronyms
//===========================================================================
function acronyms( $acronyms ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['acronyms']}</h2>
	<div class='ipsActionBar clearfix'>
		<ul>
			<li class='ipsActionButton'><a href='{$this->settings['base_url']}app=core&module=posts&section=acronyms&do=add'><img src='{$this->settings['skin_acp_url']}/images/icons/add.png' /> {$this->lang->words['acronyms_add']}</a></li>
		</ul>
	</div>
</div>

<div class='acp-box'>
	<h3>{$this->lang->words['acronyms']}</h3>
	<table class='ipsTable'>
		<tr>
			<th width='2%'>&nbsp;</th>
			<th>{$this->lang->words['acronyms_short']}</th>
			<th>{$this->lang->words['acronyms_long']}</th>
			<th>{$this->lang->words['acronyms_semantic']}</th>
			<th>{$this->lang->words['acronyms_casesensitive']}</th>
			<th class='col_buttons'>&nbsp;</th>
		</tr>
HTML;
		if ( !empty( $acronyms ) )
		{
			foreach ( $acronyms as $id => $data )
			{
				$semantic = $data['a_semantic'] ? 'tick' : 'cross';
				$case     = $data['a_casesensitive'] ? 'tick' : 'cross';
				
				$IPBHTML .= <<<HTML
		<tr class='ipsControlRow'>
			<td>&nbsp;</td>
			<td>{$data['a_short']}</td>
			<td>{$data['a_long']}</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$semantic}.png' /></td>
			<td><img src='{$this->settings['skin_acp_url']}/images/icons/{$case}.png' /></td>
			<td>
				<ul class='ipsControlStrip'>
					<li class='i_edit'>
						<a href='{$this->settings['base_url']}app=core&module=posts&section=acronyms&do=edit&amp;id={$data['a_id']}'>{$this->registry->getClass('class_localization')->words['edit']}</a>
					</li>
					<li class='i_delete' id='menu{$data['queue_id']}'>
						<a onclick="if ( !confirm('{$this->registry->getClass('class_localization')->words['acronym_delete_confirm']}' ) ) { return false; }" href='{$this->settings['base_url']}app=core&module=posts&section=acronyms&do=delete&amp;id={$data['a_id']}'>{$this->registry->getClass('class_localization')->words['delete']}...</a>
					</li>
				</ul>
			</td>
		</tr>
HTML;
			}
		}
		else
		{
			$IPBHTML .= <<<HTML
		<tr>
			<td colspan='5' class='no_messages'>{$this->lang->words['acronyms_none']} <a href='{$this->settings['base_url']}app=core&module=posts&section=acronyms&do=add' class='mini_button'>{$this->lang->words['acronyms_none_add']}</a></td>
		</tr>
HTML;
		}
		
		$IPBHTML .= <<<HTML
	</table>
</div>

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Acronym Form
//===========================================================================
function acronymForm( $current ) {

if ( empty( $current ) )
{
	$title = $this->lang->words['acronyms_add'];
	$id = 0;
}
else
{
	$title = $this->lang->words['acronyms_edit'];
	$id = $current['a_id'];
}

$form['short'] = $this->registry->output->formInput( 'short', ( empty( $current ) ? '' : $current['a_short'] ), '', '30', 'text', '', '', '255' );
$form['long'] = $this->registry->output->formInput( 'long', ( empty( $current ) ? '' : $current['a_long'] ), '', '30', 'text', '', '', '255' );
$form['semantic'] = $this->registry->output->formYesNo( 'semantic', ( empty( $current ) ? 1 : $current['a_semantic'] ) );
$form['casesensitive'] = $this->registry->output->formYesNo( 'casesensitive', ( empty( $current ) ? 0 : $current['a_casesensitive'] ) );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$title}</h2>
</div>

<form id='tags-form' action='{$this->settings['base_url']}&amp;module=posts&amp;section=acronyms&amp;do=save' method='post'>
	<input type='hidden' name='id' value='{$id}' />
	
	<div class='acp-box'>
		<h3>{$title}</h3>
		<table class='ipsTable double_pad'>
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['acronyms_short']}</strong></td>
				<td class='field_field'>
					{$form['short']}<br />
					<span class='desctext'>{$this->lang->words['acronyms_short_desc']}</td>
				</td>
			</tr>
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['acronyms_long']}</strong></td>
				<td class='field_field'>
					{$form['long']}
				</td>
			</tr>
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['acronyms_semantic']}</strong></td>
				<td class='field_field'>
					{$form['semantic']}<br />
					<span class='desctext'>{$this->lang->words['acronyms_semantic_desc']}</td>
				</td>
			</tr>
			<tr>
				<td class='field_title'><strong class='title'>{$this->lang->words['acronyms_casesensitive']}</strong></td>
				<td class='field_field'>
					{$form['casesensitive']}<br />
					<span class='desctext'>{$this->lang->words['acronyms_casesensitive_desc']}</td>
				</td>
			</tr>
		</table>
		<div class='acp-actionbar'>
			<input type='submit' class='button' value='{$title}' />
		</div>
	</div>

</form>

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Show Keywords
//===========================================================================
function keywords( $pagination, $keywords ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['keywords_title']}</h2>
</div>

<div class='information-box'>
	{$this->lang->words['keywords_blurb']}
</div>
<br />

<div class='acp-box'>
	<h3>{$this->lang->words['keywords_title']}</h3>
	<table class='ipsTable'>
		<tr>
			<th>{$this->lang->words['keywords_keyword']}</th>
			<th>{$this->lang->words['keywords_count']}</th>
		</tr>
HTML;

		if ( !empty( $keywords ) )
		{
		
			foreach ( $keywords as $keyword )
			{
				$IPBHTML .= <<<HTML
		<tr>
			<td><a href="{$this->settings['base_url']}module=seo&section=activity&do=visitors&keyword={$keyword['keyword']}">{$keyword['keyword']}</a></td>
			<td>{$keyword['count']}</td>
		</tr>		
HTML;
			}
			
		}
		else
		{
				$IPBHTML .= <<<HTML
		<tr>
			<td colspan='2' class='no_messages'>{$this->lang->words['keywords_none']}</td>
		</tr>		
HTML;
		}
		
$IPBHTML .= <<<HTML
	</table>
</div>
<br />
{$pagination}

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Show Keywords
//===========================================================================
function visitors( $pagination, $visitors ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['visitors_title']}</h2>
</div>

<div class='information-box'>
	{$this->lang->words['visitors_blurb']}
</div>
<br />

<div class='acp-box'>
	<h3>{$this->lang->words['visitors_title']}</h3>
	<table class='ipsTable'>
		<tr>
			<th>{$this->lang->words['visitors_member']}</th>
			<th>{$this->lang->words['visitors_date']}</th>
			<th>{$this->lang->words['visitors_keywords']}</th>
			<th>{$this->lang->words['visitors_engine']}</th>
			<th>{$this->lang->words['visitors_page']}</th>
		</tr>
HTML;

		if ( !empty( $visitors ) )
		{
		
			foreach ( $visitors as $visitor )
			{
				$IPBHTML .= <<<HTML
		<tr>
			<td>
HTML;
			if ( $visitor['member_id'] )
			{
				$IPBHTML .= <<<HTML
				<a href="{$this->settings['base_url']}app=members&module=members&section=members&do=viewmember&member_id={$visitor['member_id']}">{$visitor['members_display_name']}</a>
HTML;
			}
			else
			{
				$IPBHTML .= <<<HTML
				{$this->lang->words['global_guestname']}
				
HTML;
			}
			
			
			$IPBHTML .= <<<HTML
			</td>
			<td>{$visitor['date']}</td>
			<td><a href="{$this->settings['base_url']}module=seo&amp;section=activity&amp;do=visitors&amp;keyword={$visitor['keywords']}">{$visitor['keywords']}</a></td>
			<td><a href="{$this->settings['base_url']}module=seo&amp;section=activity&amp;do=visitors&amp;engine={$visitor['engine']}">{$visitor['engine']}</a></td>
			<td><a target="_blank" href="{$this->settings['public_url']}{$visitor['url']}">{$visitor['page']}</a></td>
		</tr>		
HTML;
			}
			
		}
		else
		{
				$IPBHTML .= <<<HTML
		<tr>
			<td colspan='5' class='no_messages'>{$this->lang->words['visitors_none']}</td>
		</tr>		
HTML;
		}
		
$IPBHTML .= <<<HTML
	</table>
</div>
<br />
{$pagination}

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Activity Dashboard
//===========================================================================
function activity( $keywords, $spiders, $visitors ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['dashboard_title']}</h2>
	<div class='ipsActionBar clearfix'>
		<ul>
			<li class='ipsActionButton'><a href='#' class='ipbmenu' id='date'><img src='{$this->settings['skin_app_url']}images/calendar.png' /> {$this->lang->words['dashboard_graphs_date']}<img src='{$this->settings['skin_acp_url']}/images/dropdown.png' /></a></li>
		</ul>
	</div>
</div>

<ul class='ipbmenu_content' id='date_menucontent' style='display: none'>
	<li><img src='{$this->settings['skin_app_url']}images/calendar-d.png' /> <a href='{$this->settings['base_url']}module=seo&section=activity&do=dashboard&days=1' style='text-decoration: none' >{$this->lang->words['dashboard_graphs_day']}</a></li>
	<li><img src='{$this->settings['skin_app_url']}images/calendar-w.png' /> <a href='{$this->settings['base_url']}module=seo&section=activity&do=dashboard&days=7' style='text-decoration: none' >{$this->lang->words['dashboard_graphs_week']}</a></li>
	<li><img src='{$this->settings['skin_app_url']}images/calendar-m.png' /> <a href='{$this->settings['base_url']}module=seo&section=activity&do=dashboard&days=28' style='text-decoration: none' >{$this->lang->words['dashboard_graphs_month']}</a></li>
</ul>

<div class='acp-box'>
	<h3>{$this->lang->words['dashboard_visitors']}</h3>
	<div style='padding: 5px; text-align: center'>
		<img src="{$this->settings['base_url']}module=seo&section=activity&do=search_chart&days={$this->request['days']}" />
	</div>
</div>
<br />

<div class='acp-box'>
	<h3>{$this->lang->words['dashboard_spiders']}</h3>
	<div style='padding: 5px; text-align: center'>
		<img src="{$this->settings['base_url']}module=seo&section=activity&do=spider_chart&days={$this->request['days']}" />
	</div>
</div>
<br />

<div style='width: 49%; float: left'>
	
	<div class='acp-box'>
		<h3>{$this->lang->words['dashboard_keywords']}</h3>
		<table class='ipsTable'>
			<tr>
				<th>{$this->lang->words['keywords_keyword']}</th>
				<th>{$this->lang->words['keywords_count']}</th>
			</tr>
HTML;
	
			if ( !empty( $keywords ) )
			{
			
				foreach ( $keywords as $keyword )
				{
					$IPBHTML .= <<<HTML
			<tr>
				<td><a href="{$this->settings['base_url']}module=seo&section=activity&do=visitors&keyword={$keyword['keyword']}">{$keyword['keyword']}</a></td>
				<td>{$keyword['count']}</td>
			</tr>		
HTML;
				}
				
			}
			else
			{
					$IPBHTML .= <<<HTML
			<tr>
				<td colspan='2' class='no_messages'>{$this->lang->words['keywords_none']}</td>
			</tr>		
HTML;
			}
			
	$IPBHTML .= <<<HTML
		</table>
HTML;
		if ( !empty( $keywords ) )
		{
			$IPBHTML .= <<<HTML
			<div class='acp-actionbar'>
				<a href='{$this->settings['base_url']}module=seo&section=activity&do=keywords' class='button'>{$this->lang->words['dashboard_all']}</a>
			</div>
HTML;
		}
		$IPBHTML .= <<<HTML
	</div>
	<br />
	
</div>

<div style='width: 49%; float: right'>
	
	<div class='acp-box'>
		<h3>{$this->lang->words['dashboard_spiders']}</h3>
		<table class='ipsTable'>
			<tr>
				<th>{$this->lang->words['spiders_spider']}</th>
				<th>{$this->lang->words['spiders_date']}</th>
				<th>{$this->lang->words['spiders_page']}</th>
			</tr>
HTML;
	
			if ( !empty( $spiders ) )
			{
			
				foreach ( $spiders as $spider )
				{
					$IPBHTML .= <<<HTML
			<tr>
				<td><a href="{$this->settings['base_url']}module=seo&section=activity&engine={$spider['bot']}">{$spider['bot']}</a></td>
				<td>{$spider['entry_date']}</td>
				<td><a target="_blank" href="{$this->settings['public_url']}{$spider['url']}">{$spider['page']}</a></td>
			</tr>		
HTML;
				}
				
			}
			else
			{
					$IPBHTML .= <<<HTML
			<tr>
				<td colspan='3' class='no_messages'>{$this->lang->words['spiders_none']}</td>
			</tr>		
HTML;
			}
			
	$IPBHTML .= <<<HTML
		</table>
HTML;
		if ( !empty( $spiders ) )
		{
			$IPBHTML .= <<<HTML
			<div class='acp-actionbar'>
				<a href='{$this->settings['base_url']}app=core&module=logs&section=spiderlogs' class='button'>{$this->lang->words['dashboard_all']}</a>
			</div>
HTML;
		}
		$IPBHTML .= <<<HTML
	</div>
	<br />

</div>

<br style='clear: both' />

<div class='acp-box'>
	<h3>{$this->lang->words['dashboard_visitors']}</h3>
	<table class='ipsTable'>
		<tr>
			<th>{$this->lang->words['visitors_member']}</th>
			<th>{$this->lang->words['visitors_date']}</th>
			<th>{$this->lang->words['visitors_keywords']}</th>
			<th>{$this->lang->words['visitors_engine']}</th>
			<th>{$this->lang->words['visitors_page']}</th>
		</tr>
HTML;

		if ( !empty( $visitors ) )
		{
		
			foreach ( $visitors as $visitor )
			{
				$IPBHTML .= <<<HTML
		<tr>
			<td>
HTML;
			if ( $visitor['member_id'] )
			{
				$IPBHTML .= <<<HTML
				<a href="{$this->settings['base_url']}app=members&module=members&section=members&do=viewmember&member_id={$visitor['member_id']}">{$visitor['members_display_name']}</a>
HTML;
			}
			else
			{
				$IPBHTML .= <<<HTML
				{$this->lang->words['global_guestname']}
				
HTML;
			}
			
			
			$IPBHTML .= <<<HTML
			</td>
			<td>{$visitor['date']}</td>
			<td><a href="{$this->settings['base_url']}module=seo&section=activity&do=visitors&keyword={$visitor['keywords']}">{$visitor['keywords']}</a></td>
			<td><a href="{$this->settings['base_url']}module=seo&section=activity&do=visitors&engine={$visitor['engine']}">{$visitor['engine']}</a></td>
			<td><a target="_blank" href="{$this->settings['public_url']}{$visitor['url']}">{$visitor['page']}</a></td>
		</tr>		
HTML;
			}
			
		}
		else
		{
				$IPBHTML .= <<<HTML
		<tr>
			<td colspan='5' class='no_messages'>{$this->lang->words['visitors_none']}</td>
		</tr>		
HTML;
		}
		
$IPBHTML .= <<<HTML
	</table>
HTML;
	if ( !empty( $keywords ) )
	{
		$IPBHTML .= <<<HTML
	<div class='acp-actionbar'>
		<a href='{$this->settings['base_url']}module=seo&section=activity&do=visitors' class='button'>{$this->lang->words['dashboard_all']}</a>
	</div>
HTML;
	}
	$IPBHTML .= <<<HTML
</div>

HTML;

//--endhtml--//
return $IPBHTML;
}

//===========================================================================
// Upgrade Splash
//===========================================================================
function upgradeSplash() {

$oldPath = ( ( array_key_exists( '_', $_SERVER ) ? $_SERVER['_'] : '/usr/bin/php' ) . ' ' . IPS_ROOT_PATH . 'applications_addon/ips/ipseo/sources/cron.php' );

$task = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'task_manager', 'where' => "task_application='core' AND task_key='ipseo_sitemap_generator'" ) );
$newPath = DOC_IPS_ROOT_PATH . 'interface/task.php ' . $task['task_cronkey'];

$application = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'core_applications', 'where' => "app_directory='ipseo'" ) );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML

<div class='section_title'>
	<h2>{$this->lang->words['ipseo_upgrade_title']}</h2>
</div>

<div class='acp-box'>
	<h3>{$this->lang->words['ipseo_upgrade_title']}</h3>
	<table class='ipsTable'>
		<tr>
			<td>
				{$this->lang->words['ipseo_upgrade_intro']}
HTML;
			if ( $this->settings['task_use_cron'] )
			{
				$IPBHTML .= <<<HTML
				{$this->lang->words['ipseo_upgrade_cronmode']}
				<input value="{$oldPath}" style="width:100%" /><br /><br />
HTML;
			}
			else
			{
				$IPBHTML .= <<<HTML
				{$this->lang->words['ipseo_upgrade_1']}
				<input value="{$oldPath}" style="width:100%" /><br /><br />
				{$this->lang->words['ipseo_upgrade_2']}
				<input value="{$newPath}" style="width:100%" /><br /><br />
HTML;
			}
$IPBHTML .= <<<HTML
				{$this->lang->words['ipseo_upgrade_finish']}
			</td>
		</tr>
	</table>
	<div class='acp-actionbar'>
		<a href='{$this->settings['base_url']}app=core&module=applications&section=applications&do=application_remove&app_id={$application['app_id']}' class='realbutton'>{$this->lang->words['ipseo_upgrade_continue']}</a>
	</div>
</div>
HTML;


//--endhtml--//
return $IPBHTML;
}



}