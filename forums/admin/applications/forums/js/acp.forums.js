/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* acp.forums.js - Forum javascript 			*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Brandon Farber						*/
/************************************************/

ACPForums = {
	showModForm: 0,
	
	/*------------------------------*/
	/* Constructor 					*/
	init: function()
	{
		Debug.write("Initializing acp.forums.js");
		
		document.observe("dom:loaded", function(){
			if( $('modUserName') )
			{
				ACPForums.autoComplete = new ipb.Autocomplete( $('modUserName'), { multibox: false, url: acp.autocompleteUrl, templates: { wrap: acp.autocompleteWrap, item: acp.autocompleteItem } } );
			}
			
			if ( jQuery('[data-form-type=forum]').length )
			{
				jQuery('[data-form-type=forum]').on( 'submit', ACPForums.formSubmit );
			}
			
			ipb.delegate.register(".toggle", ACPForums.toggleCat);
			
		});
	},
	
	formSubmit: function()
	{
		var parent    = jQuery('#parent_id').val();
		var forumType = jQuery('input[name=forum_type]:checked').val();
		
		if ( parent == '-1' && forumType == 'standard' )
		{
			jQuery('#parent_id').parents('tr').addClass('_red');
			
			alert( ipb.lang['forum_not_root_and_standard'] );
			
			return false;
		}
		
		return true;
	},
	
	toggleModOptions: function()
	{
		$$('.moddiv').each( function(div){
			if( ACPForums.showModForm )
			{
				div.hide();
			}
			else
			{
				div.show();
			}
		});
		
		$( 'togglemod' ).innerHTML = ACPForums.showModForm ? ipb.lang['forums_showmod'] : ipb.lang['forums_hidemod'];
		ACPForums.showModForm = ACPForums.showModForm == 1 ? 0 : 1;
		return false;
	},
	
	submitModForm: function()
	{
		var submitValue	= '';
		
		$$('input').each( function(cb){
			if( cb.type == 'checkbox' && cb.checked == true )
			{
				var mainname = cb.id.replace( /^(.+?)_.+?$/  , "$1" );
				var idname   = cb.id.replace( /^(.+?)_(.+?)$/, "$2" );
				
				if ( mainname == 'id' )
				{
					submitValue += ',' + idname;
				}
			}
		});
		
		$('modforumids').value	= submitValue;
		return true;
	},
	
	/**
	 * @todo: not used anymore it seems, we need to remove it at some point
	 */
	convert: function()
	{
		$('convert').value = 1;
		$('adminform').submit();
	},
	
	/**
	 * Show/hide a category
	 * 
	 * @var		{event}		e	The event
	*/
	toggleCat: function(e, elem)
	{
		if( ACPForums.animating ){ return false; }
		
		var remove = $A();
		var catname = $( elem ).up('.parentCat');
		var wrapper = $( catname ).down('.item_wrap');
		$( wrapper ).identify(); // IE8 fix
		
		ACPForums.animating = true;

		Effect.toggle( wrapper, 'blind', {duration: 0.4, afterFinish: function(){ ACPForums.animating = false; } } );
		Effect.toggle( wrapper, 'appear', {duration: 0.3});
		
		if( catname.hasClassName('collapsed') )
		{
			catname.removeClassName('collapsed');
		}
		else
		{
			new Effect.Morph( $(catname), {style: 'collapsed', duration: 0.4, afterFinish: function(){
				$( catname ).addClassName('collapsed');
				ACPForums.animating = false;
			} });
		}

		Event.stop( e );
	}
};

ACPForums.init();