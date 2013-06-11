/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/*
 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
 * Digest Algorithm, as defined in RFC 1321.
 * Copyright (C) Paul Johnston 1999 - 2000.
 * Updated by Greg Holt 2000 - 2001.
 * See http://pajhome.org.uk/site/legal.html for details.
 */
 
/*
 * Convert a 32-bit number to a hex string with ls-byte first
 */
var hex_chr = "0123456789abcdef";
function rhex(num)
{
  str = "";
  for(j = 0; j <= 3; j++)
    str += hex_chr.charAt((num >> (j * 8 + 4)) & 0x0F) +
           hex_chr.charAt((num >> (j * 8)) & 0x0F);
  return str;
}
 
/*
 * Convert a string to a sequence of 16-word blocks, stored as an array.
 * Append padding bits and the length, as described in the MD5 standard.
 */
function str2blks_MD5(str)
{
  nblk = ((str.length + 8) >> 6) + 1;
  blks = new Array(nblk * 16);
  for(i = 0; i < nblk * 16; i++) blks[i] = 0;
  for(i = 0; i < str.length; i++)
    blks[i >> 2] |= str.charCodeAt(i) << ((i % 4) * 8);
  blks[i >> 2] |= 0x80 << ((i % 4) * 8);
  blks[nblk * 16 - 2] = str.length * 8;
  return blks;
}
 
/*
 * Add integers, wrapping at 2^32. This uses 16-bit operations internally 
 * to work around bugs in some JS interpreters.
 */
function add(x, y)
{
  var lsw = (x & 0xFFFF) + (y & 0xFFFF);
  var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
  return (msw << 16) | (lsw & 0xFFFF);
}
 
/*
 * Bitwise rotate a 32-bit number to the left
 */
function rol(num, cnt)
{
  return (num << cnt) | (num >>> (32 - cnt));
}

/*
 * These functions implement the basic operation for each round of the
 * algorithm.
 */
function cmn(q, a, b, x, s, t)
{
  return add(rol(add(add(a, q), add(x, t)), s), b);
}
function ff(a, b, c, d, x, s, t)
{
  return cmn((b & c) | ((~b) & d), a, b, x, s, t);
}
function gg(a, b, c, d, x, s, t)
{
  return cmn((b & d) | (c & (~d)), a, b, x, s, t);
}
function hh(a, b, c, d, x, s, t)
{
  return cmn(b ^ c ^ d, a, b, x, s, t);
}
function ii(a, b, c, d, x, s, t)
{
  return cmn(c ^ (b | (~d)), a, b, x, s, t);
}

var checkForUploader;
//var IPS_UPLOADER_INIT_DONE = false;

(function()
{
	var imageDialog = function( editor, dialogType )
	{
		var loadUploadDialog = function()
		{
			try
			{
				if ( typeof( ipb.attach.template ) == 'undefined' )
				{
					$$('head').first().insert( new Element( 'script', { type: 'text/javascript', src: ipb.vars['swfupload_swf'].replace( /3rd_party.*$/, '' ) + 'ips.attach.js' } ) );
				}
			}
			catch(err)
			{
				$$('head').first().insert( new Element( 'script', { type: 'text/javascript', src: ipb.vars['swfupload_swf'].replace( /3rd_party.*$/, '' ) + 'ips.attach.js' } ) );
			}
			
			checkForUploader = setTimeout( function() { initUploader(); }, 500 );
		};
		
		/* Uploader ready? */
		var initUploader = function()
		{
			if ( typeof( IPS_UPLOADER_INIT_DONE ) == 'undefined' || IPS_UPLOADER_INIT_DONE !== true )
			{
				checkForUploader = setTimeout( function() { initUploader(); }, 500 );
				return;
			}
			
			/* All loaded */
			clearTimeout( checkForUploader );
			
			Debug.write( "Uploader initiating..." );
			
			var postKey     = getPostKey();
			var attachIds   = getAttachIds();
			var weCanUpload = canWeUpload( attachIds );
			
			var url  = ipb.vars['base_url'] + "app=core&section=attach&module=ajax&do=getForumsAppUploader&md5check=" + ipb.vars['secure_hash'];
			var data = { 'attach_post_key':   postKey,
						 'attach_rel_module': attachIds.section,
						 'attach_rel_id':     ( typeof( attachIds.pid ) == 'undefined' ) ? 0 : parseInt( attachIds.pid ),
						 'forum_id':		  parseInt( attachIds.f )
			};
			
			Debug.write( url );
			
			var oldFlashSetting = ipb.vars['swfupload_enabled'];
			
			ipb.vars['swfupload_enabled'] = false;
			
			new Ajax.Request( url,
			{
				method: 'post',
				parameters: data,
				onSuccess: function(t)
				{
					/* Hacky, but needed because other style rules overwrite this from CK */
					var styleText = "<style type='text/css'> \
								 .ipsButton_secondary { \
									height: 22px; \
									line-height: 22px; \
									font-size: 12px; \
									padding: 0 10px; \
									background: #f6f6f6; !important\
									background: -moz-linear-gradient(top, #f6f6f6 0%, #e5e5e5 100%); /* firefox */ \
									background: -webkit-gadient(linear, left top, left bottom, color-stop(0%,#f6f6f6), color-stop(100%,#e5e5e5)); /* webkit */ \
									border: 1px solid #dbdbdb; \
									-moz-box-shadow: 0px 1px 0px rgba(255,255,255,1) inset, 0px 1px 0px rgba(0,0,0,0.3); \
									-webkit-box-shadow: 0px 1px 0px rgba(255,255,255,1) inset, 0px 1px 0px rgba(0,0,0,0.3); \
									box-shadow: 0px 1px 0px rgba(255,255,255,1) inset, 0px 1px 0px rgba(0,0,0,0.3); \
									-moz-border-radius: 3px; \
									-webkit-border-radius: 3px; \
									border-radius: 3px; \
									color: #616161; \
									display: inline-block; \
									white-space: nowrap; \
									-webkit-transition: all 0.2s ease-in-out; \
									-moz-transition: all 0.2s ease-in-out; \
								} \
									.ipsButton_secondary a { color: #616161; }\
									.ipsButton_secondary:hover { \
										color: #4c4c4c; \
										border-color: #9a9a9a; \
									} \
									#pu_attachments { } \
									#pu_attachments li { \
										background-color: #e4ebf2; \
										border: 1px solid #d5dde5; \
										padding: 6px 20px 6px 42px; \
										margin-bottom: 10px; \
										position: relative; \
									} \
										#pu_attachments li p.info { \
											color: #69727b; \
											font-size: 0.8em; \
											width: 300px; \
										} \
										#pu_attachments li .links, #pu_attachments li.error .links, #pu_attachments.traditional .progress_bar { \
											display: none;\
										}  \
											#pu_attachments li.complete .links { \
												font-size: 0.9em; \
												margin-right: 15px; \
												right: 0px; \
												top: 12px; \
												display: block; \
												position: absolute; \
											} \
										#pu_attachments li .progress_bar { \
											margin-right: 15px; \
											width: 200px; \
											right: 0px; \
											top: 15px; \
											position: absolute; \
										} \
										#pu_attachments li.complete, #pu_attachments li.in_progress, #pu_attachments li.error { \
											background-repeat: no-repeat; \
											background-position: 12px 12px; \
										} \
										#pu_attachments li.error { \
											background-color: #e8caca; \
											border: 1px solid #ddafaf; \
										} \
											#pu_attachments li.error .info { \
												color: #8f2d2d; \
											} \
										#pu_attachments li .thumb_img { \
											left: 6px; \
											top: 6px; \
											width: 30px; \
											height: 30px; \
											overflow: hidden; \
											position: absolute; \
										} \
									</style>";
					
					$('img_upload_box_inner').update( styleText + "\n" + t.responseText );
					
					setUpUploaderHtml( data, postKey );
					
					setTimeout( function() { ipb.vars['swfupload_enabled'] = oldFlashSetting; }, 1000 );
				}
			});
		};
		
		/* Re-set up uploader */
		var setUpUploaderHtml = function( obj, postKey )
		{
			editor._attachObj = obj;
			
			// Show the button and info
			$('add_files_attach_pu_' + obj.attach_rel_id).show();
			$('space_info_attach_pu_' + obj.attach_rel_id).show();
			$('pu_help_msg').hide();
			
			var useType   = 'default';
			var uploadURL = ipb.vars['base_url'] + "app=core&module=attach&section=attach&do=attachiFrame&attach_rel_module=" + obj.attach_rel_module +
						    "&attach_rel_id=" + obj.attach_rel_id + "&attach_post_key=" + postKey + "&forum_id=" + obj.forum_id + "&attach_id=attach_pu_" + obj.attach_rel_id + "&fetch_all=1";
		
			ipb.lang['attach_button'] = "Add to post";
			ipb.lang['attach_delete'] = "Delete";
			
			ipb.attach.template = "<li id='ali_[id]' class='attach_row' style='display: none'><div><h4 class='attach_name'>[name]</h4><p class='info'>[info]</p><span class='img_holder'></span><p class='progress_bar'><span style='width: 0%'>0%</span></p><p class='links'><a href='#' class='add_to_post' tabindex='-1'>" + ipb.lang['attach_button']
								+ "</a> | <a href='#' class='cancel delete' tabindex='-1'>" + ipb.lang['attach_delete'] + "</a></p></div></li>"; 
		
			ipb.attach.registerUploader( 'attach_pu_' + obj.attach_rel_id, useType, 'pu_attachments', {
					'upload_url': uploadURL,
					'attach_rel_module': obj.attach_rel_module,
					'attach_rel_id': obj.attach_rel_id,
					'attach_post_key': postKey,
					'forum_id': obj.forum_id,
					'prefix': 'pu_',
					'file_size_limit': 100000
			} );
			
			//$('add_files_attach_' + obj.attach_rel_id).addClassName('cke_dialog_ui_button');
		};
		
		/* Can we upload? */
		var weCanUpload = function( attachIds )
		{
			try
			{
				if ( attachIds.app == 'forums' && attachIds.section == 'post' )
				{
					return true;
				}
			}
			catch( err )
			{
				return false;
			}
			
			return false;
		};
		
		/* Can we upload? */
		var canWeUpload = function( attachIds )
		{
			return true;
		};
		
		/* Get attach details based on form content */
		var getAttachIds = function()
		{
			return $( editor.name ).up('form').serialize( true );
		};
		
		/* Attempt to get a post key or create one */
		var getPostKey = function()
		{
			try
			{
				if ( $$('input[name=attach_post_key]').first().value )
				{
					return $$('input[name=attach_post_key]').first().value;
				}
			} catch(err){}
			
			/* Create one */
			var postKey = calcMD5( new Date().getTime().toString() );
			
			$( editor.name ).up('form').insert( new Element( 'input', { type: 'hidden', name: 'attach_post_key', value: postKey } ) );
			
			/* Makes sure fast reply sends this key */
			$$('input[name=attach_post_key]').first().setAttribute('rel', 'include');
			
			return postKey;
		};
		
		/*
		 * Take a string and return the hex representation of its MD5.
		 */
		var calcMD5 = function(str)
		{
		  x = str2blks_MD5(str);
		  a =  1732584193;
		  b = -271733879;
		  c = -1732584194;
		  d =  271733878;
		 
		  for(i = 0; i < x.length; i += 16)
		  {
		    olda = a;
		    oldb = b;
		    oldc = c;
		    oldd = d;
		 
		    a = ff(a, b, c, d, x[i+ 0], 7 , -680876936);
		    d = ff(d, a, b, c, x[i+ 1], 12, -389564586);
		    c = ff(c, d, a, b, x[i+ 2], 17,  606105819);
		    b = ff(b, c, d, a, x[i+ 3], 22, -1044525330);
		    a = ff(a, b, c, d, x[i+ 4], 7 , -176418897);
		    d = ff(d, a, b, c, x[i+ 5], 12,  1200080426);
		    c = ff(c, d, a, b, x[i+ 6], 17, -1473231341);
		    b = ff(b, c, d, a, x[i+ 7], 22, -45705983);
		    a = ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
		    d = ff(d, a, b, c, x[i+ 9], 12, -1958414417);
		    c = ff(c, d, a, b, x[i+10], 17, -42063);
		    b = ff(b, c, d, a, x[i+11], 22, -1990404162);
		    a = ff(a, b, c, d, x[i+12], 7 ,  1804603682);
		    d = ff(d, a, b, c, x[i+13], 12, -40341101);
		    c = ff(c, d, a, b, x[i+14], 17, -1502002290);
		    b = ff(b, c, d, a, x[i+15], 22,  1236535329);    
		 
		    a = gg(a, b, c, d, x[i+ 1], 5 , -165796510);
		    d = gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
		    c = gg(c, d, a, b, x[i+11], 14,  643717713);
		    b = gg(b, c, d, a, x[i+ 0], 20, -373897302);
		    a = gg(a, b, c, d, x[i+ 5], 5 , -701558691);
		    d = gg(d, a, b, c, x[i+10], 9 ,  38016083);
		    c = gg(c, d, a, b, x[i+15], 14, -660478335);
		    b = gg(b, c, d, a, x[i+ 4], 20, -405537848);
		    a = gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
		    d = gg(d, a, b, c, x[i+14], 9 , -1019803690);
		    c = gg(c, d, a, b, x[i+ 3], 14, -187363961);
		    b = gg(b, c, d, a, x[i+ 8], 20,  1163531501);
		    a = gg(a, b, c, d, x[i+13], 5 , -1444681467);
		    d = gg(d, a, b, c, x[i+ 2], 9 , -51403784);
		    c = gg(c, d, a, b, x[i+ 7], 14,  1735328473);
		    b = gg(b, c, d, a, x[i+12], 20, -1926607734);
		     
		    a = hh(a, b, c, d, x[i+ 5], 4 , -378558);
		    d = hh(d, a, b, c, x[i+ 8], 11, -2022574463);
		    c = hh(c, d, a, b, x[i+11], 16,  1839030562);
		    b = hh(b, c, d, a, x[i+14], 23, -35309556);
		    a = hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
		    d = hh(d, a, b, c, x[i+ 4], 11,  1272893353);
		    c = hh(c, d, a, b, x[i+ 7], 16, -155497632);
		    b = hh(b, c, d, a, x[i+10], 23, -1094730640);
		    a = hh(a, b, c, d, x[i+13], 4 ,  681279174);
		    d = hh(d, a, b, c, x[i+ 0], 11, -358537222);
		    c = hh(c, d, a, b, x[i+ 3], 16, -722521979);
		    b = hh(b, c, d, a, x[i+ 6], 23,  76029189);
		    a = hh(a, b, c, d, x[i+ 9], 4 , -640364487);
		    d = hh(d, a, b, c, x[i+12], 11, -421815835);
		    c = hh(c, d, a, b, x[i+15], 16,  530742520);
		    b = hh(b, c, d, a, x[i+ 2], 23, -995338651);
		 
		    a = ii(a, b, c, d, x[i+ 0], 6 , -198630844);
		    d = ii(d, a, b, c, x[i+ 7], 10,  1126891415);
		    c = ii(c, d, a, b, x[i+14], 15, -1416354905);
		    b = ii(b, c, d, a, x[i+ 5], 21, -57434055);
		    a = ii(a, b, c, d, x[i+12], 6 ,  1700485571);
		    d = ii(d, a, b, c, x[i+ 3], 10, -1894986606);
		    c = ii(c, d, a, b, x[i+10], 15, -1051523);
		    b = ii(b, c, d, a, x[i+ 1], 21, -2054922799);
		    a = ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
		    d = ii(d, a, b, c, x[i+15], 10, -30611744);
		    c = ii(c, d, a, b, x[i+ 6], 15, -1560198380);
		    b = ii(b, c, d, a, x[i+13], 21,  1309151649);
		    a = ii(a, b, c, d, x[i+ 4], 6 , -145523070);
		    d = ii(d, a, b, c, x[i+11], 10, -1120210379);
		    c = ii(c, d, a, b, x[i+ 2], 15,  718787259);
		    b = ii(b, c, d, a, x[i+ 9], 21, -343485551);
		 
		    a = add(a, olda);
		    b = add(b, oldb);
		    c = add(c, oldc);
		    d = add(d, oldd);
		  }
		  return rhex(a) + rhex(b) + rhex(c) + rhex(d);
		};

		// Load image preview.
		var IMAGE = 1,
			LINK = 2,
			PREVIEW = 4,
			CLEANUP = 8,
			regexGetSize = /^\s*(\d+)((px)|\%)?\s*$/i,
			regexGetSizeOrEmpty = /(^\s*(\d+)((px)|\%)?\s*$)|^$/i,
			pxLengthRegex = /^\d+px$/;

		var onSizeChange = function()
		{
			var value = this.getValue(),	// This = input element.
				dialog = this.getDialog(),
				aMatch  =  value.match( regexGetSize );	// Check value
			if ( aMatch )
			{
				if ( aMatch[2] == '%' )			// % is allowed - > unlock ratio.
					switchLockRatio( dialog, false );	// Unlock.
				value = aMatch[1];
			}

			// Only if ratio is locked
			if ( dialog.lockRatio )
			{
				var oImageOriginal = dialog.originalElement;
				if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
				{
					if ( this.id == 'txtHeight' )
					{
						if ( value && value != '0' )
							value = Math.round( oImageOriginal.$.width * ( value  / oImageOriginal.$.height ) );
						if ( !isNaN( value ) )
							dialog.setValueOf( 'info', 'txtWidth', value );
					}
					else		//this.id = txtWidth.
					{
						if ( value && value != '0' )
							value = Math.round( oImageOriginal.$.height * ( value  / oImageOriginal.$.width ) );
						if ( !isNaN( value ) )
							dialog.setValueOf( 'info', 'txtHeight', value );
					}
				}
			}
			updatePreview( dialog );
		};

		var updatePreview = function( dialog )
		{
			//Don't load before onShow.
			if ( !dialog.originalElement || !dialog.preview )
				return 1;

			// Read attributes and update imagePreview;
			dialog.commitContent( PREVIEW, dialog.preview );
			return 0;
		};

		// Custom commit dialog logic, where we're intended to give inline style
		// field (txtdlgGenStyle) higher priority to avoid overwriting styles contribute
		// by other fields.
		function commitContent()
		{
			var args = arguments;
			var inlineStyleField = this.getContentElement( 'advanced', 'txtdlgGenStyle' );
			inlineStyleField && inlineStyleField.commit.apply( inlineStyleField, args );

			this.foreach( function( widget )
			{
				if ( widget.commit &&  widget.id != 'txtdlgGenStyle' )
					widget.commit.apply( widget, args );
			});
		}

		// Avoid recursions.
		var incommit;

		// Synchronous field values to other impacted fields is required, e.g. border
		// size change should alter inline-style text as well.
		function commitInternally( targetFields )
		{
			if ( incommit )
				return;

			incommit = 1;

			var dialog = this.getDialog(),
				element = dialog.imageElement;
			if ( element )
			{
				// Commit this field and broadcast to target fields.
				this.commit( IMAGE, element );

				targetFields = [].concat( targetFields );
				var length = targetFields.length,
					field;
				for ( var i = 0; i < length; i++ )
				{
					field = dialog.getContentElement.apply( dialog, targetFields[ i ].split( ':' ) );
					// May cause recursion.
					field && field.setup( IMAGE, element );
				}
			}

			incommit = 0;
		}

		var switchLockRatio = function( dialog, value )
		{
			if ( !dialog.getContentElement( 'info', 'ratioLock' ) )
				return null;

			var oImageOriginal = dialog.originalElement;

			// Dialog may already closed. (#5505)
			if( !oImageOriginal )
				return null;

			// Check image ratio and original image ratio, but respecting user's preference.
			if ( value == 'check' )
			{
				if ( !dialog.userlockRatio && oImageOriginal.getCustomData( 'isReady' ) == 'true'  )
				{
					var width = dialog.getValueOf( 'info', 'txtWidth' ),
						height = dialog.getValueOf( 'info', 'txtHeight' ),
						originalRatio = oImageOriginal.$.width * 1000 / oImageOriginal.$.height,
						thisRatio = width * 1000 / height;
					dialog.lockRatio  = false;		// Default: unlock ratio

					if ( !width && !height )
						dialog.lockRatio = true;
					else if ( !isNaN( originalRatio ) && !isNaN( thisRatio ) )
					{
						if ( Math.round( originalRatio ) == Math.round( thisRatio ) )
							dialog.lockRatio = true;
					}
				}
			}
			else if ( value != undefined )
				dialog.lockRatio = value;
			else
			{
				dialog.userlockRatio = 1;
				dialog.lockRatio = !dialog.lockRatio;
			}

			var ratioButton = CKEDITOR.document.getById( btnLockSizesId );
			if ( dialog.lockRatio )
				ratioButton.removeClass( 'cke_btn_unlocked' );
			else
				ratioButton.addClass( 'cke_btn_unlocked' );

			ratioButton.setAttribute( 'aria-checked', dialog.lockRatio );

			// Ratio button hc presentation - WHITE SQUARE / BLACK SQUARE
			if ( CKEDITOR.env.hc )
			{
				var icon = ratioButton.getChild( 0 );
				icon.setHtml(  dialog.lockRatio ? CKEDITOR.env.ie ? '\u25A0': '\u25A3' : CKEDITOR.env.ie ? '\u25A1' : '\u25A2' );
			}

			return dialog.lockRatio;
		};

		var resetSize = function( dialog )
		{
			var oImageOriginal = dialog.originalElement;
			if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
			{
				var widthField = dialog.getContentElement( 'info', 'txtWidth' ),
					heightField = dialog.getContentElement( 'info', 'txtHeight' );
				widthField && widthField.setValue( oImageOriginal.$.width );
				heightField && heightField.setValue( oImageOriginal.$.height );
			}
			updatePreview( dialog );
		};

		var setupDimension = function( type, element )
		{
			if ( type != IMAGE )
				return;

			function checkDimension( size, defaultValue )
			{
				var aMatch  =  size.match( regexGetSize );
				if ( aMatch )
				{
					if ( aMatch[2] == '%' )				// % is allowed.
					{
						aMatch[1] += '%';
						switchLockRatio( dialog, false );	// Unlock ratio
					}
					return aMatch[1];
				}
				return defaultValue;
			}

			var dialog = this.getDialog(),
				value = '',
				dimension = this.id == 'txtWidth' ? 'width' : 'height',
				size = element.getAttribute( dimension );

			if ( size )
				value = checkDimension( size, value );
			value = checkDimension( element.getStyle( dimension ), value );

			this.setValue( value );
		};

		var previewPreloader;

		var onImgLoadEvent = function()
		{
			// Image is ready.
			var original = this.originalElement;
			original.setCustomData( 'isReady', 'true' );
			original.removeListener( 'load', onImgLoadEvent );
			original.removeListener( 'error', onImgLoadErrorEvent );
			original.removeListener( 'abort', onImgLoadErrorEvent );

			// Hide loader
			CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );

			// New image -> new domensions
			if ( !this.dontResetSize )
				resetSize( this );

			if ( this.firstLoad )
				CKEDITOR.tools.setTimeout( function(){ switchLockRatio( this, 'check' ); }, 0, this );

			this.firstLoad = false;
			this.dontResetSize = false;
		};

		var onImgLoadErrorEvent = function()
		{
			// Error. Image is not loaded.
			var original = this.originalElement;
			original.removeListener( 'load', onImgLoadEvent );
			original.removeListener( 'error', onImgLoadErrorEvent );
			original.removeListener( 'abort', onImgLoadErrorEvent );

			// Set Error image.
			var noimage = CKEDITOR.getUrl( editor.skinPath + 'images/noimage.png' );

			if ( this.preview )
				this.preview.setAttribute( 'src', noimage );

			// Hide loader
			CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );
			switchLockRatio( this, false );	// Unlock.
		};

		var numbering = function( id )
			{
				return CKEDITOR.tools.getNextId() + '_' + id;
			},
			btnLockSizesId = numbering( 'btnLockSizes' ),
			btnResetSizeId = numbering( 'btnResetSize' ),
			imagePreviewLoaderId = numbering( 'ImagePreviewLoader' ),
			previewLinkId = numbering( 'previewLink' ),
			previewImageId = numbering( 'previewImage' );

		return {
			title : editor.lang.image[ dialogType == 'image' ? 'title' : 'titleButton' ],
			minWidth : 420,
			minHeight : 360,
			onShow : function()
			{
				this.imageElement = false;
				this.linkElement = false;

				// Default: create a new element.
				this.imageEditMode = false;
				this.linkEditMode = false;

				this.lockRatio = true;
				this.userlockRatio = 0;
				this.dontResetSize = false;
				this.firstLoad = true;
				this.addLink = false;

				var editor = this.getParentEditor(),
					sel = editor.getSelection(),
					element = sel && sel.getSelectedElement(),
					link = element && element.getAscendant( 'a' );

				//Hide loader.
				CKEDITOR.document.getById( imagePreviewLoaderId ).setStyle( 'display', 'none' );
				// Create the preview before setup the dialog contents.
				previewPreloader = new CKEDITOR.dom.element( 'img', editor.document );
				this.preview = CKEDITOR.document.getById( previewImageId );

				// Copy of the image
				this.originalElement = editor.document.createElement( 'img' );
				this.originalElement.setAttribute( 'alt', '' );
				this.originalElement.setCustomData( 'isReady', 'false' );

				if ( link )
				{
					this.linkElement = link;
					this.linkEditMode = true;

					// Look for Image element.
					var linkChildren = link.getChildren();
					if ( linkChildren.count() == 1 )			// 1 child.
					{
						var childTagName = linkChildren.getItem( 0 ).getName();
						if ( childTagName == 'img' || childTagName == 'input' )
						{
							this.imageElement = linkChildren.getItem( 0 );
							if ( this.imageElement.getName() == 'img' )
								this.imageEditMode = 'img';
							else if ( this.imageElement.getName() == 'input' )
								this.imageEditMode = 'input';
						}
					}
					// Fill out all fields.
					if ( dialogType == 'image' )
						this.setupContent( LINK, link );
				}

				if ( element && element.getName() == 'img' && !element.data( 'cke-realelement' )
					|| element && element.getName() == 'input' && element.getAttribute( 'type' ) == 'image' )
				{
					this.imageEditMode = element.getName();
					this.imageElement = element;
				}

				if ( this.imageEditMode )
				{
					// Use the original element as a buffer from  since we don't want
					// temporary changes to be committed, e.g. if the dialog is canceled.
					this.cleanImageElement = this.imageElement;
					this.imageElement = this.cleanImageElement.clone( true, true );

					// Fill out all fields.
					this.setupContent( IMAGE, this.imageElement );
				}
				else
					this.imageElement =  editor.document.createElement( 'img' );

				// Refresh LockRatio button
				switchLockRatio ( this, true );

				// Dont show preview if no URL given.
				if ( !CKEDITOR.tools.trim( this.getValueOf( 'info', 'txtUrl' ) ) )
				{
					this.preview.removeAttribute( 'src' );
					this.preview.setStyle( 'display', 'none' );
				}
			},
			onOk : function()
			{
				// Auto add attachments into post if need be
				try
				{
					var row         = ipb.attach.uploaders[ 'attach_pu_' + editor._attachObj.attach_rel_id ].boxes;
					var addedAttach = new Array();
					
					var _PossibleAttach = phpjs.preg_match_all( '\\[attachment=(\\d+?)(:|\\])', editor.getData() );
					
					if ( _PossibleAttach.length )
					{
						$(_PossibleAttach).each( function(t)
						{
							if ( addedAttach.indexOf( t[1] ) == -1 )
							{
								addedAttach.push( t[1] );
							}
						} );
					}
				
					$H(ipb.attach.uploaders[ 'attach_pu_' + editor._attachObj.attach_rel_id ].boxes).each( function( row )
					{
						var key = row.key;
						var val = row.value;
						
						if ( typeof( val ) == 'string' )
						{
							var attachid = $(val).readAttribute('attachid').replace( /cur_/, '' );
							var filename = $(val).down('.attach_name').innerHTML;
							
							if ( attachid )
							{
								if ( addedAttach.indexOf( attachid ) == -1 )
								{
									var _pee = editor.document.createElement( 'p' );
									_pee.setHtml( "[attachment=" + attachid + ":" + filename.replace( /\[/, '' ).replace( /\]/, '' ) + "]" );
									
									editor.insertElement( _pee );
								}
							}
						}
					} );
				}
				catch(err )
				{
					Debug.write( err );
				}
				
				dialogType = 'image';
				
				// Edit existing Image.
				if ( this.imageEditMode )
				{
					var imgTagName = this.imageEditMode;
					
					// Image dialog and Input element.
					if ( dialogType == 'image' && imgTagName == 'input' && confirm( editor.lang.image.button2Img ) )
					{
						// Replace INPUT-> IMG
						imgTagName = 'img';
						this.imageElement = editor.document.createElement( 'img' );
						this.imageElement.setAttribute( 'alt', '' );
						editor.insertElement( this.imageElement );
					}
					// ImageButton dialog and Image element.
					else if ( dialogType != 'image' && imgTagName == 'img' && confirm( editor.lang.image.img2Button ))
					{
						// Replace IMG -> INPUT
						imgTagName = 'input';
						this.imageElement = editor.document.createElement( 'input' );
						this.imageElement.setAttributes(
							{
								type : 'image',
								alt : ''
							}
						);
						editor.insertElement( this.imageElement );
					}
					else
					{
						// Restore the original element before all commits.
						this.imageElement = this.cleanImageElement;
						delete this.cleanImageElement;
					}
				}
				else	// Create a new image.
				{
					// Image dialog -> create IMG element.
					if ( dialogType == 'image' )
						this.imageElement = editor.document.createElement( 'img' );
					else
					{
						this.imageElement = editor.document.createElement( 'input' );
						this.imageElement.setAttribute ( 'type' ,'image' );
					}
					this.imageElement.setAttribute( 'alt', '' );
				}

				// Create a new link.
				if ( !this.linkEditMode )
					this.linkElement = editor.document.createElement( 'a' );

				// Set attributes.
				this.commitContent( IMAGE, this.imageElement );
				this.commitContent( LINK, this.linkElement );

				// Remove empty style attribute.
				if ( !this.imageElement.getAttribute( 'style' ) )
					this.imageElement.removeAttribute( 'style' );
				
				if ( ! this.imageElement.getAttribute('src') )
				{
					return;
				}
				
				// Insert a new Image.
				if ( !this.imageEditMode )
				{
					if ( this.addLink )
					{
						//Insert a new Link.
						if ( !this.linkEditMode )
						{
							editor.insertElement( this.linkElement );
							this.linkElement.append( this.imageElement, false );
						}
						else	 //Link already exists, image not.
							editor.insertElement( this.imageElement );
					}
					else
						editor.insertElement( this.imageElement );
				}
				else		// Image already exists.
				{
					//Add a new link element.
					if ( !this.linkEditMode && this.addLink )
					{
						editor.insertElement( this.linkElement );
						this.imageElement.appendTo( this.linkElement );
					}
					//Remove Link, Image exists.
					else if ( this.linkEditMode && !this.addLink )
					{
						editor.getSelection().selectElement( this.linkElement );
						editor.insertElement( this.imageElement );
					}
				}
			},
			onLoad : function()
			{
				if ( dialogType != 'image' )
					this.hidePage( 'Link' );		//Hide Link tab.
				var doc = this._.element.getDocument();

				if ( this.getContentElement( 'info', 'ratioLock' ) )
				{
					this.addFocusable( doc.getById( btnResetSizeId ), 5 );
					this.addFocusable( doc.getById( btnLockSizesId ), 5 );
				}

				this.commitContent = commitContent;
			},
			onHide : function()
			{
				if ( this.preview )
					this.commitContent( CLEANUP, this.preview );

				if ( this.originalElement )
				{
					this.originalElement.removeListener( 'load', onImgLoadEvent );
					this.originalElement.removeListener( 'error', onImgLoadErrorEvent );
					this.originalElement.removeListener( 'abort', onImgLoadErrorEvent );
					this.originalElement.remove();
					this.originalElement = false;		// Dialog is closed.
				}

				delete this.imageElement;
			},
			contents : [
				{
					id : 'info',
					label : editor.lang.image.infoTab,
					accessKey : 'I',
					elements :
					[
						{
							type : 'vbox',
							padding : 0,
							children :
							[
								{
									type : 'hbox',
									widths : [ '280px', '110px' ],
									align : 'right',
									children :
									[
										{
											id : 'txtUrl',
											type : 'text',
											label : editor.lang.common.url,
											required: true,
											onChange : function()
											{
												var dialog = this.getDialog(),
													newUrl = this.getValue();

												//Update original image
												if ( newUrl.length > 0 )	//Prevent from load before onShow
												{
													dialog = this.getDialog();
													var original = dialog.originalElement;

													dialog.preview.removeStyle( 'display' );

													original.setCustomData( 'isReady', 'false' );
													// Show loader
													var loader = CKEDITOR.document.getById( imagePreviewLoaderId );
													if ( loader )
														loader.setStyle( 'display', '' );

													original.on( 'load', onImgLoadEvent, dialog );
													original.on( 'error', onImgLoadErrorEvent, dialog );
													original.on( 'abort', onImgLoadErrorEvent, dialog );
													original.setAttribute( 'src', newUrl );

													// Query the preloader to figure out the url impacted by based href.
													previewPreloader.setAttribute( 'src', newUrl );
													dialog.preview.setAttribute( 'src', previewPreloader.$.src );
													updatePreview( dialog );
												}
												// Dont show preview if no URL given.
												else if ( dialog.preview )
												{
													dialog.preview.removeAttribute( 'src' );
													dialog.preview.setStyle( 'display', 'none' );
												}
											},
											setup : function( type, element )
											{
												if ( type == IMAGE )
												{
													var url = element.data( 'cke-saved-src' ) || element.getAttribute( 'src' );
													var field = this;

													this.getDialog().dontResetSize = true;

													field.setValue( url );		// And call this.onChange()
													// Manually set the initial value.(#4191)
													field.setInitValue();
												}
											},
											commit : function( type, element )
											{
												if ( type == IMAGE && ( this.getValue() || this.isChanged() ) )
												{
													element.data( 'cke-saved-src', this.getValue() );
													element.setAttribute( 'src', this.getValue() );
												}
												else if ( type == CLEANUP )
												{
													element.setAttribute( 'src', '' );	// If removeAttribute doesn't work.
													element.removeAttribute( 'src' );
												}
											},
											//validate : CKEDITOR.dialog.validate.notEmpty( editor.lang.image.urlMissing )
										},
										{
											type : 'button',
											id : 'browse',
											// v-align with the 'txtUrl' field.
											// TODO: We need something better than a fixed size here.
											style : 'display:inline-block;margin-top:10px;',
											align : 'center',
											label : editor.lang.common.browseServer,
											hidden : true,
											filebrowser : 'info:txtUrl'
										}
									]
								}
							]
						},
						{
							id : 'txtAlt',
							type : 'text',
							hidden: true,
							label : editor.lang.image.alt,
							accessKey : 'T',
							'default' : '',
							onChange : function()
							{
								updatePreview( this.getDialog() );
							},
							setup : function( type, element )
							{
								if ( type == IMAGE )
									this.setValue( element.getAttribute( 'alt' ) );
							},
							commit : function( type, element )
							{
								if ( type == IMAGE )
								{
									if ( this.getValue() || this.isChanged() )
										element.setAttribute( 'alt', this.getValue() );
								}
								else if ( type == PREVIEW )
								{
									element.setAttribute( 'alt', this.getValue() );
								}
								else if ( type == CLEANUP )
								{
									element.removeAttribute( 'alt' );
								}
							}
						},
						{
							type : 'hbox',
							hidden: true,
							children :
							[
								{
									id : 'basic',
									type : 'vbox',
									children :
									[
										{
											type : 'hbox',
											widths : [ '50%', '50%' ],
											children :
											[
												{
													type : 'vbox',
													padding : 1,
													children :
													[
														{
															type : 'text',
															width: '40px',
															id : 'txtWidth',
															label : editor.lang.common.width,
															onKeyUp : onSizeChange,
															onChange : function()
															{
																commitInternally.call( this, 'advanced:txtdlgGenStyle' );
															},
															validate : function()
															{
																var aMatch  =  this.getValue().match( regexGetSizeOrEmpty ),
																	isValid = !!( aMatch && parseInt( aMatch[1], 10 ) !== 0 );
																if ( !isValid )
																	alert( editor.lang.common.invalidWidth );
																return isValid;
															},
															setup : setupDimension,
															commit : function( type, element, internalCommit )
															{
																var value = this.getValue();
																if ( type == IMAGE )
																{
																	if ( value )
																		element.setStyle( 'width', CKEDITOR.tools.cssLength( value ) );
																	else
																		element.removeStyle( 'width' );

																	!internalCommit && element.removeAttribute( 'width' );
																}
																else if ( type == PREVIEW )
																{
																	var aMatch = value.match( regexGetSize );
																	if ( !aMatch )
																	{
																		var oImageOriginal = this.getDialog().originalElement;
																		if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
																			element.setStyle( 'width',  oImageOriginal.$.width + 'px');
																	}
																	else
																		element.setStyle( 'width', CKEDITOR.tools.cssLength( value ) );
																}
																else if ( type == CLEANUP )
																{
																	element.removeAttribute( 'width' );
																	element.removeStyle( 'width' );
																}
															}
														},
														{
															type : 'text',
															id : 'txtHeight',
															width: '40px',
															label : editor.lang.common.height,
															onKeyUp : onSizeChange,
															onChange : function()
															{
																commitInternally.call( this, 'advanced:txtdlgGenStyle' );
															},
															validate : function()
															{
																var aMatch = this.getValue().match( regexGetSizeOrEmpty ),
																	isValid = !!( aMatch && parseInt( aMatch[1], 10 ) !== 0 );
																if ( !isValid )
																	alert( editor.lang.common.invalidHeight );
																return isValid;
															},
															setup : setupDimension,
															commit : function( type, element, internalCommit )
															{
																var value = this.getValue();
																if ( type == IMAGE )
																{
																	if ( value )
																		element.setStyle( 'height', CKEDITOR.tools.cssLength( value ) );
																	else
																		element.removeStyle( 'height' );

																	!internalCommit && element.removeAttribute( 'height' );
																}
																else if ( type == PREVIEW )
																{
																	var aMatch = value.match( regexGetSize );
																	if ( !aMatch )
																	{
																		var oImageOriginal = this.getDialog().originalElement;
																		if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
																			element.setStyle( 'height', oImageOriginal.$.height + 'px' );
																	}
																	else
																		element.setStyle( 'height',  CKEDITOR.tools.cssLength( value ) );
																}
																else if ( type == CLEANUP )
																{
																	element.removeAttribute( 'height' );
																	element.removeStyle( 'height' );
																}
															}
														}
													]
												},
												{
													id : 'ratioLock',
													type : 'html',
													style : 'margin-top:30px;width:40px;height:40px;',
													onLoad : function()
													{
														// Activate Reset button
														var	resetButton = CKEDITOR.document.getById( btnResetSizeId ),
															ratioButton = CKEDITOR.document.getById( btnLockSizesId );
														if ( resetButton )
														{
															resetButton.on( 'click', function( evt )
																{
																	resetSize( this );
																	evt.data && evt.data.preventDefault();
																}, this.getDialog() );
															resetButton.on( 'mouseover', function()
																{
																	this.addClass( 'cke_btn_over' );
																}, resetButton );
															resetButton.on( 'mouseout', function()
																{
																	this.removeClass( 'cke_btn_over' );
																}, resetButton );
														}
														// Activate (Un)LockRatio button
														if ( ratioButton )
														{
															ratioButton.on( 'click', function(evt)
																{
																	var locked = switchLockRatio( this ),
																		oImageOriginal = this.originalElement,
																		width = this.getValueOf( 'info', 'txtWidth' );

																	if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' && width )
																	{
																		var height = oImageOriginal.$.height / oImageOriginal.$.width * width;
																		if ( !isNaN( height ) )
																		{
																			this.setValueOf( 'info', 'txtHeight', Math.round( height ) );
																			updatePreview( this );
																		}
																	}
																	evt.data && evt.data.preventDefault();
																}, this.getDialog() );
															ratioButton.on( 'mouseover', function()
																{
																	this.addClass( 'cke_btn_over' );
																}, ratioButton );
															ratioButton.on( 'mouseout', function()
																{
																	this.removeClass( 'cke_btn_over' );
																}, ratioButton );
														}
													},
													html : '<div>'+
														'<a href="javascript:void(0)" tabindex="-1" title="' + editor.lang.image.lockRatio +
														'" class="cke_btn_locked" id="' + btnLockSizesId + '" role="checkbox"><span class="cke_icon"></span><span class="cke_label">' + editor.lang.image.lockRatio + '</span></a>' +
														'<a href="javascript:void(0)" tabindex="-1" title="' + editor.lang.image.resetSize +
														'" class="cke_btn_reset" id="' + btnResetSizeId + '" role="button"><span class="cke_label">' + editor.lang.image.resetSize + '</span></a>'+
														'</div>'
												}
											]
										},
										{
											type : 'vbox',
											padding : 1,
											children :
											[
												{
													type : 'text',
													id : 'txtBorder',
													width: '60px',
													label : editor.lang.image.border,
													'default' : '',
													onKeyUp : function()
													{
														updatePreview( this.getDialog() );
													},
													onChange : function()
													{
														commitInternally.call( this, 'advanced:txtdlgGenStyle' );
													},
													validate : CKEDITOR.dialog.validate.integer( editor.lang.image.validateBorder ),
													setup : function( type, element )
													{
														if ( type == IMAGE )
														{
															var value,
																borderStyle = element.getStyle( 'border-width' );
															borderStyle = borderStyle && borderStyle.match( /^(\d+px)(?: \1 \1 \1)?$/ );
															value = borderStyle && parseInt( borderStyle[ 1 ], 10 );
															isNaN ( parseInt( value, 10 ) ) && ( value = element.getAttribute( 'border' ) );
															this.setValue( value );
														}
													},
													commit : function( type, element, internalCommit )
													{
														var value = parseInt( this.getValue(), 10 );
														if ( type == IMAGE || type == PREVIEW )
														{
															if ( !isNaN( value ) )
															{
																element.setStyle( 'border-width', CKEDITOR.tools.cssLength( value ) );
																element.setStyle( 'border-style', 'solid' );
															}
															else if ( !value && this.isChanged() )
															{
																element.removeStyle( 'border-width' );
																element.removeStyle( 'border-style' );
																element.removeStyle( 'border-color' );
															}

															if ( !internalCommit && type == IMAGE )
																element.removeAttribute( 'border' );
														}
														else if ( type == CLEANUP )
														{
															element.removeAttribute( 'border' );
															element.removeStyle( 'border-width' );
															element.removeStyle( 'border-style' );
															element.removeStyle( 'border-color' );
														}
													}
												},
												{
													type : 'text',
													id : 'txtHSpace',
													width: '60px',
													label : editor.lang.image.hSpace,
													'default' : '',
													onKeyUp : function()
													{
														updatePreview( this.getDialog() );
													},
													onChange : function()
													{
														commitInternally.call( this, 'advanced:txtdlgGenStyle' );
													},
													validate : CKEDITOR.dialog.validate.integer( editor.lang.image.validateHSpace ),
													setup : function( type, element )
													{
														if ( type == IMAGE )
														{
															var value,
																marginLeftPx,
																marginRightPx,
																marginLeftStyle = element.getStyle( 'margin-left' ),
																marginRightStyle = element.getStyle( 'margin-right' );

															marginLeftStyle = marginLeftStyle && marginLeftStyle.match( pxLengthRegex );
															marginRightStyle = marginRightStyle && marginRightStyle.match( pxLengthRegex );
															marginLeftPx = parseInt( marginLeftStyle, 10 );
															marginRightPx = parseInt( marginRightStyle, 10 );

															value = ( marginLeftPx == marginRightPx ) && marginLeftPx;
															isNaN( parseInt( value, 10 ) ) && ( value = element.getAttribute( 'hspace' ) );

															this.setValue( value );
														}
													},
													commit : function( type, element, internalCommit )
													{
														var value = parseInt( this.getValue(), 10 );
														if ( type == IMAGE || type == PREVIEW )
														{
															if ( !isNaN( value ) )
															{
																element.setStyle( 'margin-left', CKEDITOR.tools.cssLength( value ) );
																element.setStyle( 'margin-right', CKEDITOR.tools.cssLength( value ) );
															}
															else if ( !value && this.isChanged( ) )
															{
																element.removeStyle( 'margin-left' );
																element.removeStyle( 'margin-right' );
															}

															if ( !internalCommit && type == IMAGE )
																element.removeAttribute( 'hspace' );
														}
														else if ( type == CLEANUP )
														{
															element.removeAttribute( 'hspace' );
															element.removeStyle( 'margin-left' );
															element.removeStyle( 'margin-right' );
														}
													}
												},
												{
													type : 'text',
													id : 'txtVSpace',
													width : '60px',
													label : editor.lang.image.vSpace,
													'default' : '',
													onKeyUp : function()
													{
														updatePreview( this.getDialog() );
													},
													onChange : function()
													{
														commitInternally.call( this, 'advanced:txtdlgGenStyle' );
													},
													validate : CKEDITOR.dialog.validate.integer( editor.lang.image.validateVSpace ),
													setup : function( type, element )
													{
														if ( type == IMAGE )
														{
															var value,
																marginTopPx,
																marginBottomPx,
																marginTopStyle = element.getStyle( 'margin-top' ),
																marginBottomStyle = element.getStyle( 'margin-bottom' );

															marginTopStyle = marginTopStyle && marginTopStyle.match( pxLengthRegex );
															marginBottomStyle = marginBottomStyle && marginBottomStyle.match( pxLengthRegex );
															marginTopPx = parseInt( marginTopStyle, 10 );
															marginBottomPx = parseInt( marginBottomStyle, 10 );

															value = ( marginTopPx == marginBottomPx ) && marginTopPx;
															isNaN ( parseInt( value, 10 ) ) && ( value = element.getAttribute( 'vspace' ) );
															this.setValue( value );
														}
													},
													commit : function( type, element, internalCommit )
													{
														var value = parseInt( this.getValue(), 10 );
														if ( type == IMAGE || type == PREVIEW )
														{
															if ( !isNaN( value ) )
															{
																element.setStyle( 'margin-top', CKEDITOR.tools.cssLength( value ) );
																element.setStyle( 'margin-bottom', CKEDITOR.tools.cssLength( value ) );
															}
															else if ( !value && this.isChanged( ) )
															{
																element.removeStyle( 'margin-top' );
																element.removeStyle( 'margin-bottom' );
															}

															if ( !internalCommit && type == IMAGE )
																element.removeAttribute( 'vspace' );
														}
														else if ( type == CLEANUP )
														{
															element.removeAttribute( 'vspace' );
															element.removeStyle( 'margin-top' );
															element.removeStyle( 'margin-bottom' );
														}
													}
												},
												{
													id : 'cmbAlign',
													type : 'select',
													widths : [ '35%','65%' ],
													style : 'width:90px',
													label : editor.lang.common.align,
													'default' : '',
													items :
													[
														[ editor.lang.common.notSet , ''],
														[ editor.lang.common.alignLeft , 'left'],
														[ editor.lang.common.alignRight , 'right']
														// Backward compatible with v2 on setup when specified as attribute value,
														// while these values are no more available as select options.
														//	[ editor.lang.image.alignAbsBottom , 'absBottom'],
														//	[ editor.lang.image.alignAbsMiddle , 'absMiddle'],
														//  [ editor.lang.image.alignBaseline , 'baseline'],
														//  [ editor.lang.image.alignTextTop , 'text-top'],
														//  [ editor.lang.image.alignBottom , 'bottom'],
														//  [ editor.lang.image.alignMiddle , 'middle'],
														//  [ editor.lang.image.alignTop , 'top']
													],
													onChange : function()
													{
														updatePreview( this.getDialog() );
														commitInternally.call( this, 'advanced:txtdlgGenStyle' );
													},
													setup : function( type, element )
													{
														if ( type == IMAGE )
														{
															var value = element.getStyle( 'float' );
															switch( value )
															{
																// Ignore those unrelated values.
																case 'inherit':
																case 'none':
																	value = '';
															}

															!value && ( value = ( element.getAttribute( 'align' ) || '' ).toLowerCase() );
															this.setValue( value );
														}
													},
													commit : function( type, element, internalCommit )
													{
														var value = this.getValue();
														if ( type == IMAGE || type == PREVIEW )
														{
															if ( value )
																element.setStyle( 'float', value );
															else
																element.removeStyle( 'float' );

															if ( !internalCommit && type == IMAGE )
															{
																value = ( element.getAttribute( 'align' ) || '' ).toLowerCase();
																switch( value )
																{
																	// we should remove it only if it matches "left" or "right",
																	// otherwise leave it intact.
																	case 'left':
																	case 'right':
																		element.removeAttribute( 'align' );
																}
															}
														}
														else if ( type == CLEANUP )
															element.removeStyle( 'float' );

													}
												}
											]
										}
									]
								},
								{
									type : 'vbox',
									height : '250px',
									children :
									[
										{
											type : 'html',
											id : 'htmlPreview',
											style : 'width:95%;',
											html : '<div>' + CKEDITOR.tools.htmlEncode( editor.lang.common.preview ) +'<br>'+
											'<div id="' + imagePreviewLoaderId + '" class="ImagePreviewLoader" style="display:none"><div class="loading">&nbsp;</div></div>'+
											'<div class="ImagePreviewBox"><table><tr><td>'+
											'<a href="javascript:void(0)" target="_blank" onclick="return false;" id="' + previewLinkId + '">'+
											'<img id="' + previewImageId + '" alt="" /></a>' +
											( editor.config.image_previewText ||
											'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. '+
											'Maecenas feugiat consequat diam. Maecenas metus. Vivamus diam purus, cursus a, commodo non, facilisis vitae, '+
											'nulla. Aenean dictum lacinia tortor. Nunc iaculis, nibh non iaculis aliquam, orci felis euismod neque, sed ornare massa mauris sed velit. Nulla pretium mi et risus. Fusce mi pede, tempor id, cursus ac, ullamcorper nec, enim. Sed tortor. Curabitur molestie. Duis velit augue, condimentum at, ultrices a, luctus ut, orci. Donec pellentesque egestas eros. Integer cursus, augue in cursus faucibus, eros pede bibendum sem, in tempus tellus justo quis ligula. Etiam eget tortor. Vestibulum rutrum, est ut placerat elementum, lectus nisl aliquam velit, tempor aliquam eros nunc nonummy metus. In eros metus, gravida a, gravida sed, lobortis id, turpis. Ut ultrices, ipsum at venenatis fringilla, sem nulla lacinia tellus, eget aliquet turpis mauris non enim. Nam turpis. Suspendisse lacinia. Curabitur ac tortor ut ipsum egestas elementum. Nunc imperdiet gravida mauris.' ) +
											'</td></tr></table></div></div>'
										}
									]
								}
							]
						}
					]
				},
				{
					id : 'Link',
					label : editor.lang.link.title,
					padding : 0,
					hidden: true,
					elements :
					[
						{
							id : 'txtUrl',
							type : 'text',
							label : editor.lang.common.url,
							style : 'width: 100%',
							'default' : '',
							setup : function( type, element )
							{
								if ( type == LINK )
								{
									var href = element.data( 'cke-saved-href' );
									if ( !href )
										href = element.getAttribute( 'href' );
									this.setValue( href );
								}
							},
							commit : function( type, element )
							{
								if ( type == LINK )
								{
									if ( this.getValue() || this.isChanged() )
									{
										var url = decodeURI( this.getValue() );
										element.data( 'cke-saved-href', url );
										element.setAttribute( 'href', url );

										if ( this.getValue() || !editor.config.image_removeLinkByEmptyURL )
											this.getDialog().addLink = true;
									}
								}
							}
						},
						{
							type : 'button',
							id : 'browse',
							filebrowser :
							{
								action : 'Browse',
								target: 'Link:txtUrl',
								url: editor.config.filebrowserImageBrowseLinkUrl
							},
							style : 'float:right',
							hidden : true,
							label : editor.lang.common.browseServer
						},
						{
							id : 'cmbTarget',
							type : 'select',
							label : editor.lang.common.target,
							'default' : '',
							items :
							[
								[ editor.lang.common.notSet , ''],
								[ editor.lang.common.targetNew , '_blank'],
								[ editor.lang.common.targetTop , '_top'],
								[ editor.lang.common.targetSelf , '_self'],
								[ editor.lang.common.targetParent , '_parent']
							],
							setup : function( type, element )
							{
								if ( type == LINK )
									this.setValue( element.getAttribute( 'target' ) || '' );
							},
							commit : function( type, element )
							{
								if ( type == LINK )
								{
									if ( this.getValue() || this.isChanged() )
										element.setAttribute( 'target', this.getValue() );
								}
							}
						}
					]
				},
				{
					/*! UPLOAD --------- */
					id : 'Upload',
					hidden : false,
					filebrowser : 'uploadButton',
					label : editor.lang.image.upload,
					elements :
					[
						{
							onLoad: loadUploadDialog,
							type : 'html',
							id : 'img_upload_box',
							label : editor.lang.image.btnUpload,
							html: "<div id='img_upload_box_inner'>Loading...</div>"
						},
						
					]
				},
				{
					id : 'advanced',
					hidden: true,
					label : editor.lang.common.advancedTab,
					elements :
					[
						{
							type : 'hbox',
							widths : [ '50%', '25%', '25%' ],
							children :
							[
								{
									type : 'text',
									id : 'linkId',
									label : editor.lang.common.id,
									setup : function( type, element )
									{
										if ( type == IMAGE )
											this.setValue( element.getAttribute( 'id' ) );
									},
									commit : function( type, element )
									{
										if ( type == IMAGE )
										{
											if ( this.getValue() || this.isChanged() )
												element.setAttribute( 'id', this.getValue() );
										}
									}
								},
								{
									id : 'cmbLangDir',
									type : 'select',
									style : 'width : 100px;',
									label : editor.lang.common.langDir,
									'default' : '',
									items :
									[
										[ editor.lang.common.notSet, '' ],
										[ editor.lang.common.langDirLtr, 'ltr' ],
										[ editor.lang.common.langDirRtl, 'rtl' ]
									],
									setup : function( type, element )
									{
										if ( type == IMAGE )
											this.setValue( element.getAttribute( 'dir' ) );
									},
									commit : function( type, element )
									{
										if ( type == IMAGE )
										{
											if ( this.getValue() || this.isChanged() )
												element.setAttribute( 'dir', this.getValue() );
										}
									}
								},
								{
									type : 'text',
									id : 'txtLangCode',
									label : editor.lang.common.langCode,
									'default' : '',
									setup : function( type, element )
									{
										if ( type == IMAGE )
											this.setValue( element.getAttribute( 'lang' ) );
									},
									commit : function( type, element )
									{
										if ( type == IMAGE )
										{
											if ( this.getValue() || this.isChanged() )
												element.setAttribute( 'lang', this.getValue() );
										}
									}
								}
							]
						},
						{
							type : 'text',
							id : 'txtGenLongDescr',
							label : editor.lang.common.longDescr,
							setup : function( type, element )
							{
								if ( type == IMAGE )
									this.setValue( element.getAttribute( 'longDesc' ) );
							},
							commit : function( type, element )
							{
								if ( type == IMAGE )
								{
									if ( this.getValue() || this.isChanged() )
										element.setAttribute( 'longDesc', this.getValue() );
								}
							}
						},
						{
							type : 'hbox',
							widths : [ '50%', '50%' ],
							children :
							[
								{
									type : 'text',
									id : 'txtGenClass',
									label : editor.lang.common.cssClass,
									'default' : '',
									setup : function( type, element )
									{
										if ( type == IMAGE )
											this.setValue( element.getAttribute( 'class' ) );
									},
									commit : function( type, element )
									{
										if ( type == IMAGE )
										{
											if ( this.getValue() || this.isChanged() )
												element.setAttribute( 'class', this.getValue() );
										}
									}
								},
								{
									type : 'text',
									id : 'txtGenTitle',
									label : editor.lang.common.advisoryTitle,
									'default' : '',
									onChange : function()
									{
										updatePreview( this.getDialog() );
									},
									setup : function( type, element )
									{
										if ( type == IMAGE )
											this.setValue( element.getAttribute( 'title' ) );
									},
									commit : function( type, element )
									{
										if ( type == IMAGE )
										{
											if ( this.getValue() || this.isChanged() )
												element.setAttribute( 'title', this.getValue() );
										}
										else if ( type == PREVIEW )
										{
											element.setAttribute( 'title', this.getValue() );
										}
										else if ( type == CLEANUP )
										{
											element.removeAttribute( 'title' );
										}
									}
								}
							]
						},
						{
							type : 'text',
							id : 'txtdlgGenStyle',
							label : editor.lang.common.cssStyle,
							validate : CKEDITOR.dialog.validate.inlineStyle( editor.lang.common.invalidInlineStyle ),
							'default' : '',
							setup : function( type, element )
							{
								if ( type == IMAGE )
								{
									var genStyle = element.getAttribute( 'style' );
									if ( !genStyle && element.$.style.cssText )
										genStyle = element.$.style.cssText;
									this.setValue( genStyle );

									var height = element.$.style.height,
										width = element.$.style.width,
										aMatchH  = ( height ? height : '' ).match( regexGetSize ),
										aMatchW  = ( width ? width : '').match( regexGetSize );

									this.attributesInStyle =
									{
										height : !!aMatchH,
										width : !!aMatchW
									};
								}
							},
							onChange : function ()
							{
								commitInternally.call( this,
									[ 'info:cmbFloat', 'info:cmbAlign',
									  'info:txtVSpace', 'info:txtHSpace',
									  'info:txtBorder',
									  'info:txtWidth', 'info:txtHeight' ] );
								updatePreview( this );
							},
							commit : function( type, element )
							{
								if ( type == IMAGE && ( this.getValue() || this.isChanged() ) )
								{
									element.setAttribute( 'style', this.getValue() );
								}
							}
						}
					]
				}
			]
		};
	};

	CKEDITOR.dialog.add( 'ipsimage', function( editor )
		{
			return imageDialog( editor, 'ipsimage' );
		});

	CKEDITOR.dialog.add( 'imagebutton', function( editor )
		{
			return imageDialog( editor, 'imagebutton' );
		});
})();
