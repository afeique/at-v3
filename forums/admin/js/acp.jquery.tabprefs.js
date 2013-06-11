// ************************************ //
// jQuery ACP Functions
// Matt Mecham	  						//
// ************************************ //

/**
 * Tab Prefs JS
 */
!function($, window, document, _undefined)
{
	ips.tabPrefs = {};
	
	/**
	 * Handle CKEditor
	 */
	ips.tabPrefs = function() { this.__construct(); };
	ips.tabPrefs.prototype =
	{
		/**
		 * Construct object
		 */
		__construct: function()
		{
			$('#tabPrefMsg').hide();
			
			$('#tabPrefSave').children('input:button[data-role=save]').on('click', function(e) { ipb.tabPrefs.save(e); } );
			$('#tabPrefSave').children('input:button[data-role=reset]').on('click', function(e) { ipb.tabPrefs.reset(e); } );
			
			$("#tabBar, #otherMenu").sortable({
				connectWith: ".connectedSortable",
				cancel: ".ui-state-disabled",
				update: function( e, ui ) { ipb.tabPrefs.updateEvent(e, ui); }
			}).disableSelection();
			
			this.resetStripHeights();
		},
		
		/**
		 * Reset
		 */
		reset: function(e)
		{
			/* BYE! */
			window.location = ips.acpUrl + '&app=core&module=mycp&section=tabs&do=save&reset=1';
		},

		/**
		 * Save
		 */
		save: function(e)
		{
			var tabBar     = {};
			var otherMenu  = {};
			var _tabBar    = $('#tabBar').sortable('toArray');
			var _otherMenu = $('#otherMenu').sortable('toArray');
			
			$.each( _tabBar, function(i)
			{
				tabBar[ 'tabBar_' + i ] = _tabBar[i].replace( /^(mainTab_|otherMenu_)/, '' );
			} );
			
			$.each( _otherMenu, function(i)
			{
				otherMenu[ 'otherMenu_' + i ] = _otherMenu[i].replace( /^(mainTab_|otherMenu_)/, '' );
			} );
			
			/* BYE! */
			window.location = ips.acpUrl + '&app=core&module=mycp&section=tabs&do=save&' + $.param( tabBar ) + '&' + $.param( otherMenu );
		},
		
		/**
		 * Change event triggered
		 */
		updateEvent: function(e, ui)
		{
			if ( $('#tabBar li').length > 8 )
			{
				$(ui.sender).sortable('cancel');
				
				$('#tabPrefMsg').show().children('h4').html('You cannot add another item into the main tab bar');
			}
			else if ( $('#tabBar li').length < 2 )
			{
				$(ui.sender).sortable('cancel');
				
				$('#tabPrefMsg').show().children('h4').html('You cannot remove another item from the main tab bar');
			}
			else
			{
				$('#tabPrefMsg').hide();
			}
			
			ipb.tabPrefs.resetStripHeights();
		},
		
		/**
		 * Updates tab strip heights
		 */
		resetStripHeights: function()
		{
			/* Tab menu */
			var num = Math.ceil( $('#tabBar li').length / 7 );
			console.log( num );
			$('#tabBar').css( { height: ( num * 52 ) + 'px' } );
			
			/* Other menu */
			var num = Math.ceil( $('#otherMenu li').length / 6 );
			
			$('#otherMenu').css( { height: ( num * 52 ) + 'px' } );
		}
	}
	
	$(document).ready( function() { ipb.tabPrefs = new ips.tabPrefs(); } );
	
}(jQuery, this, document);
