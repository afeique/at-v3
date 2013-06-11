function checkform() {

	/* Unlimited stuff */
	$$('._unlimitedNumber').each( function(elem)
	{
		field = elem.readAttribute('unlimited-field');
		input = elem.down('input');
		
		if ( $(field + '_cb').checked == true )
		{
			$(field).value = -1;
			Debug.write( $(field).name + "is -1");
		}
	} );
	
	/* Admin check */
	isAdmin = $('g_access_cp_yes');
	isMod   = $('g_is_supmod_yes');
	msg		= '';
	
	if ( isAdmin && isAdmin.checked == true )
	{
		msg += ipb.lang['can_access_cp'] + '\n\n';
	}
	
	if ( isMod && isMod.checked == true )
	{
		msg += ipb.lang['is_supmod'] + '\n\n';
	}
	
	if ( msg != '' )
	{
		if( confirm( ipb.lang['security_check'] + "\n--------------\n" + ipb.lang['group_title'] + $F('g_title') + "\n--------------\n\n" + msg ) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

function setUpForm()
{
	$$('._unlimitedNumber').each( function(elem)
	{
		field = elem.readAttribute('unlimited-field');
		input = elem.down('input');
		
		var cbox = new Element('input', { type: 'checkbox', name: field + '_cb', id: field + '_cb' } );
		var span = new Element('label', { 'for': field + '_cb', 'class': 'desctext clickable' } ).update( '&nbsp;' + ipb.lang['unlimited_text'] );
		var div  = new Element('div').setStyle( 'padding-bottom: 3px' ).insert( cbox ).insert( span );
		
		input.insert( { before: div } );
		
		$(field + '_cb').observe( 'click', setUpFormCbToggle );
		
		if ( input.value == -1 )
		{
			$(field + '_cb').checked = true;
			
			input.hide();
		}
	} );
}

function setUpFormCbToggle(e)
{
	elem = Event.findElement(e);
	field = elem.id.replace( /_cb$/, '' );
	
	if ( elem.checked == true )
	{
		new Effect.Fade( $(field), { duration: 0.4 } );
	}
	else
	{
		new Effect.Appear( $(field), { duration: 0.4 } );
		
		if ( $(field).value == -1 )
		{
			$(field).value = 0;
		}
	}
}

function stripGuestLegend()
{
	$$('.guest_legend').each( 
							function( elem )
							{
								elem.hide();
							} 
						);
}