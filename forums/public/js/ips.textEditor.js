/**
 * Wrapper for CKEditor based goodness
 * Written by Matt Mecham for his sins.
 * (c) 2011 IPS, Inc.
 */

/**
 * From: http://phpjs.org
 * By:  Kevin van Zonneveld (http://kevin.vanzonneveld.net), Martijn Wieringa
 */
phpjs = {
	substr_count: function(haystack, needle, offset, length)
	{
	    var pos = 0,
	        cnt = 0;
	 
	    haystack += '';
	    needle += '';
	    if (isNaN(offset)) {
	        offset = 0;
	    }
	    if (isNaN(length)) {
	        length = 0;
	    }
	    offset--;
	 
	    while ((offset = haystack.indexOf(needle, offset + 1)) != -1) {
	        if (length > 0 && (offset + needle.length) > length) {
	            return false;
	        } else {
	            cnt++;
	        }
	    }
	 
	    return cnt;
	},
	
	stristr: function(haystack, needle, bool)
	{
	    var pos = 0;
	 
	    haystack += '';
	    pos = haystack.toLowerCase().indexOf((needle + '').toLowerCase());
	    if (pos == -1) {
	        return false;
	    } else {
	        if (bool) {
	            return haystack.substr(0, pos);
	        } else {
	            return haystack.slice(pos);
	        }
	    }
	},
	
	stripos: function (f_haystack, f_needle, f_offset) {
	    var haystack = (f_haystack + '').toLowerCase();
	    var needle = (f_needle + '').toLowerCase();
	    var index = 0;
	 
	    if ((index = haystack.indexOf(needle, f_offset)) !== -1) {
	        return index;
	    }
	    return false;
	},
	
	substr: function(str, start, len)
	{
	    str += '';
	    var end = str.length;
	 
	    
	    if (start < 0) {
	        start += end;
	    }
	    end = typeof len === 'undefined' ? end : (len < 0 ? len + end : len + start);
	     
	    return start >= str.length || start < 0 || start > end ? !1 : str.slice(start, end);
	},

	
	substr_replace: function(str, replace, start, length)
	{
	    if (start < 0) { // start position in str
	        start = start + str.length;
	    }
	    length = length !== undefined ? length : str.length;
	    if (length < 0) {
	        length = length + str.length - start;
	    }
	    return str.slice(0, start) + replace.substr(0, length) + replace.slice(length) + str.slice(start + length);
	},
	
	strlen: function( str )
	{
		 return str.length;
	},
	
	strrpos: function(haystack, needle, offset) {
	    var i = -1;
	    if (offset) {
	        i = (haystack + '').slice(offset).lastIndexOf(needle); // strrpos' offset indicates starting point of range till end,
	        // while lastIndexOf's optional 2nd argument indicates ending point of range from the beginning
	        if (i !== -1) {
	            i += offset;
	        }
	    } else {
	        i = (haystack + '').lastIndexOf(needle);
	    }
	    return i >= 0 ? i : false;
	},
	
	str_ireplace: function(search, replace, subject)
	{
	    var i, k = '';
	    var searchl = 0;
	    var reg;
	 
	    var escapeRegex = function (s) {
	        return s.replace(/([\\\^\$*+\[\]?{}.=!:(|)])/g, '\\$1');
	    };
	 
	    search += '';
	    searchl = search.length;
	    if (Object.prototype.toString.call(replace) !== '[object Array]') {
	        replace = [replace];
	        if (Object.prototype.toString.call(search) === '[object Array]') {
	            // If search is an array and replace is a string,
	            // then this replacement string is used for every value of search
	            while (searchl > replace.length) {
	                replace[replace.length] = replace[0];
	            }
	        }
	    }
	 
	    if (Object.prototype.toString.call(search) !== '[object Array]') {
	        search = [search];
	    }
	    while (search.length > replace.length) {
	        // If replace has fewer values than search,
	        // then an empty string is used for the rest of replacement values
	        replace[replace.length] = '';
	    }
	 
	    if (Object.prototype.toString.call(subject) === '[object Array]') {
	        // If subject is an array, then the search and replace is performed
	        // with every entry of subject , and the return value is an array as well.
	        for (k in subject) {
	            if (subject.hasOwnProperty(k)) {
	                subject[k] = str_ireplace(search, replace, subject[k]);
	            }
	        }
	        return subject;
	    }
	 
	    searchl = search.length;
	    for (i = 0; i < searchl; i++) {
	        reg = new RegExp(escapeRegex(search[i]), 'gi');
	        subject = subject.replace(reg, replace[i]);
	    }
	 
	    return subject;
	},
	
	/* Based on @link http://coding.pressbin.com/16/Javascript-equivalent-of-PHPs-pregmatchall */
	preg_match_all: function(regex, haystack)
	{
		var globalRegex = new RegExp(regex, 'gi');
		var globalMatch = haystack.match(globalRegex);
		matchArray = new Array();
		
		try
		{
			$(globalMatch).each( function( i )
			{
				nonGlobalRegex = new RegExp(regex, 'i');
				nonGlobalMatch = i.match(nonGlobalRegex);
				matchArray.push(nonGlobalMatch);
			} );
		} catch(err){}
		
		return matchArray;
	}
};

ipsTextArea = {
	/**
	 * Insert text at the cursor position
	 * Some code from
	 * @link http://bytes.com/topic/javascript/answers/149268-moving-cursor-end-textboxes-text
	 * @param editorId
	 * @param text
	 */
	insertAtCursor: function( editorId, text )
	{
		var te		  = $('cke_' + editorId).down('textarea');
		
		var scrollPos = te.scrollTop;
		
		if ( CKEDITOR.env.ie )
		{
			te.focus();
			sel = document.selection.createRange();
			sel.text = text;
			sel.select();
		}
		else if ( te.selectionStart || te.selectionStart == '0' )
		{
			var startPos = te.selectionStart;
			var endPos   = te.selectionEnd;
			
			te.value = te.value.substring(0, startPos) + text + te.value.substring(endPos, te.value.length);
			
			if ( startPos == endPos )
			{
				this.setSelectionRange( te, startPos + text.length, endPos + text.length );
			}
			else
			{
				this.setCaretToPos( te, startPos + text.length );
			}		
		}
		else
		{
			te.value += text;
		}
		
		te.scrollTop = scrollPos;
	},
	
	/**
	 * Set selection range
	 * Some code from
	 * @link http://bytes.com/topic/javascript/answers/149268-moving-cursor-end-textboxes-text
	 */
	setSelectionRange: function(input, selectionStart, selectionEnd)
	{
		if ( input.setSelectionRange )
		{
			input.focus();
			input.setSelectionRange(selectionStart, selectionEnd);
		}
		else if ( input.createTextRange )
		{
			var range = input.createTextRange();
			range.collapse(true);
			range.moveEnd('character', selectionEnd);
			range.moveStart('character', selectionStart);
	    	range.select();
		}
	},

	/**
	 * Set caret position
	 * Some code from
	 * @link http://bytes.com/topic/javascript/answers/149268-moving-cursor-end-textboxes-text
	 */
	setCaretToPos: function( input, pos )
	{
		this.setSelectionRange( input, pos, pos );
	}	
};

IPSCKTools = {
		
	getEmbeddedTagPositions: function( txt, tag, brackets )
	{
		if ( ! ( brackets instanceof Array ) )
		{
			brackets = [ '[', ']' ];
		}
		
		var close_tag = brackets[0] + '/' + tag + brackets[1];
		var open_tag  = brackets[0] + tag;
		var map       = { open: {}, close: {} };
		var iteration = 0;
		var curPos    = 0;

		/* Pick through bit of code */
		while( ( curPos = phpjs.stripos( txt, open_tag, curPos ) ) !== false )
		{
			if ( iteration > 1000 )
			{
				break;
			}
			
			/* Make sure the next character is either ] = or a space */
			var nextChar = phpjs.substr( txt, curPos + phpjs.strlen( tag ) + 1, 1 );
			
			if ( nextChar != brackets[1] && nextChar != '=' && nextChar != ' ' )
			{
				if( curPos > phpjs.strlen(txt) )
				{
					curPos	= 0;
					break;
				}

				curPos++;
			}
			else
			{
				map['open'][ iteration ] = curPos + phpjs.strlen( open_tag );
					
				var new_pos = phpjs.stripos( txt, brackets[0], curPos ) ? phpjs.stripos( txt, brackets[0], curPos ) : curPos + 1;
					
				/* Got an option, grab that */
				var _option = phpjs.substr( txt, curPos + phpjs.strlen(open_tag), (phpjs.stripos( txt, brackets[1], curPos ) - (curPos + phpjs.strlen(open_tag) )) );
				
				map['open'][ iteration ] += parseInt( phpjs.strlen( _option ) ) + 1;
				
				/* Got a closing tag? */
				var closingTagPos = phpjs.stripos( txt, close_tag, new_pos );

				if ( closingTagPos !== false )
				{
					map['close'][ iteration ] = closingTagPos;
		
					var _content  = phpjs.substr( txt, (curPos + phpjs.strlen( open_tag )  + phpjs.strlen(_option) + 1), (closingTagPos - (curPos + phpjs.strlen(open_tag) + phpjs.strlen(_option) + 1)) );
					
					/* Did we have an opening tag in that mess? */
					if ( _content && phpjs.stristr( _content, open_tag ) )
					{
						var count = phpjs.substr_count( _content.toLowerCase(), open_tag.toLowerCase() );
		
						/* Found N opening tags in portion of text */
						if ( count > 0 )
						{
							/* So now find Nth closing tag */
							var _nPos = closingTagPos + phpjs.strlen( close_tag );
		
							while( count > 0 )
							{
								_closePos = phpjs.stripos( txt, close_tag, _nPos );
								
								if ( _closePos !== false )
								{
									map['close'][ iteration ] = _closePos;

									_content	= phpjs.substr( txt, (curPos + phpjs.strlen( open_tag )  + phpjs.strlen(_option) + 1), (_closePos - (curPos + phpjs.strlen(open_tag) + phpjs.strlen(_option) + 1)) );
									count		= phpjs.substr_count( _content.toLowerCase(), open_tag.toLowerCase() );
									
									_nPos = _closePos + phpjs.strlen( close_tag );
									
									if ( ! isNaN( _nPos ) && _nPos > phpjs.strlen( txt ) )
									{ 
										count = 0;
									}
								}
								else
								{
									count = 0;
								}
							}
						}
					}
				}
					
				iteration++;
					
				curPos = closingTagPos ? closingTagPos : curPos + 1;
		
				if ( curPos > phpjs.strlen(txt) )
				{
					curPos	= 0;
					break;
				}
			}
		}
	
		return map;
	},
	
	/**
	 * Get the selected HTML from the editor
	 * @param	object
	 */
	getSelectionHtml: function( editor )
	{
		var selection = editor.getSelection();
		
		if ( CKEDITOR.env.ie )
		{
			try {
				if ( ! Prototype.Browser.IE8 )
				{
					selection.unlock(true);
				}
			} catch(e){}
			
			try
			{
				var text = selection.getNative().createRange().htmlText;
			
				if ( text.toLowerCase().strip() == '<p>&nbsp;</p>' )
				{
					return '';
				}
			
				return text;
			} catch( e ){ return false; }
		}
		else if ( CKEDITOR.env.opera )
		{
			var selection = selection.getNative();
		
			var range = selection ? selection.getRangeAt(0) : selection.createRange();
			var div   = document.createElement('div');
			div.appendChild( range.cloneContents() );
			
			return div.innerHTML.replace( /<p><\/p>/g, '<p><br /></p>' );
		}
		else
		{
			var range = selection.getNative().getRangeAt( selection.rangeCount -1 ).cloneRange();
			var div   = document.createElement('div');
			div.appendChild( range.cloneContents() );
			
			return div.innerHTML;
		}
	},
	
	/**
	 * Clean HTML for inserting between tags
	 * @param	object
	 */
	cleanHtmlForTagWrap: function( html, convert )
	{
		var text = ( typeof( html ) != 'undefined' ) ? html.replace( /<br( \/)?>$/, '' ) : '';
		
		/* The text may have become double encoded */
		if ( convert )
		{
			text = text.replace( /&lt;/g  , '<' );
			text = text.replace( /&gt;/g  , '>' );
			text = text.replace( /&amp;/g , '&' );
			text = text.replace( /&#39;/g , "'" );
			text = text.replace( /&quot;/g, '"' );
		}
	
		return text;
	},
	
	/**
	 * Remove HTML formatting
	 * @param string
	 */
	stripHtmlTags: function( html )
	{
		return html.stripTags();
	}
};

ipb.lang['ck_bbcode_error_title'] = "BBCode Error";
ipb.lang['ck_bbcode_desc']		  = "Errors have been found with your post, please correct these errors. Errors are usually caused by mismatched opening and closing tags or missing closing tags.<br />You may use your post anyway but it may not display as intended.<br />Tags with potential errors: #{tags}";
ipb.lang['ck__bbcode_use_anyway'] = "Use Anyway";
ipb.lang['ck__bbcode_close']      = "Close &amp; Continue Editing";
/*
 * This is just a wrapper class around the objects to give it a global interface
 */
IPBoard.prototype.textEditor = {
		
	mainStore: $H(),
	lastSetUp: null,
	htmlCheckbox: $H(),
	_tmpContent: '',
	IPS_TEXTEDITOR_POLLING:	10000,			/* 10 seconds */
	IPS_NEW_POST_POLLING:	(2 * 60000),	/* 2 mins */
	IPS_SAVED_MSG:			"<a href='javascript:void()' class='_as_explain desc'>" + ipb.lang['ck_auto_saved'] + "</a>",
	IPS_AUTOSAVETEMPLATE:	"<a href='javascript:void()' class='_as_launch desc'>" + ipb.lang['ck_view_saved'] + "</a>",
	IPS_AUTOSAVEVIEW:		"<div><h3>" + ipb.lang['ck_saved'] + "</h3><div class='row2' style='padding:4px'><div class='as_content'>#{content}</div><div class='as_buttons'><input type='button' class='input_submit _as_restore' value='" + ipb.lang['ck_restore'] + "' /></div>",
	IPS_AUTOSAVEEXPLAIN:	"<div><h3>" + ipb.lang['ck_saved_title'] + "</h3><div class='row2' style='padding:8px'>" + ipb.lang['ck_saved_desc'] + "</div>",
	IPS_BBCODE_ERROR:	    "<div><h3>" + ipb.lang['ck_bbcode_error_title'] + "</h3><div class='row2' style='padding:4px'><p class='ipsPad_half'>" + ipb.lang['ck_bbcode_desc'] + "</p><div class='as_content'>#{content}</div><div class='as_buttons'><input type='button' class='input_submit _bbcode_use_anyway' value='" + ipb.lang['ck__bbcode_use_anyway'] + "' />&nbsp;<input type='button' class='input_submit important _bbcode_close' value='" + ipb.lang['ck__bbcode_close'] + "' /></div>",

	ajaxUrl:				'',
	
	initialize: function( editorId, options )
	{	
		if ( inACP )
		{
			ipb.textEditor.ajaxUrl     = ipb.vars['base_url']; 
			ipb.vars['secure_hash']    = ipb.vars['md5_hash'];
		}
		else
		{
			ipb.textEditor.ajaxUrl  = ipb.vars['base_url'];
		}
		
		/* Insert into main store */
		if ( ! ipb.textEditor.mainStore.get( editorId ) )
		{
			newEditorObject = new ipb.textEditorObjects( editorId, options );
			
			ipb.textEditor.mainStore.set( editorId, newEditorObject );
		}
		
		/* Set up */
		ipb.textEditor.lastSetUp = editorId;
	},
	
	/**
	 * Bind a button to the ajax preview functionality
	 */
	bindPreviewButton: function( buttonElem, previewElem )
	{
		if ( $(buttonElem) && $(previewElem) )
		{
			var _bbcode = 0;
			var _html   = 0;
			var _emos   = 0;
			var _area   = 'topics';
			
			try
			{
				_bbcode = ( $(buttonElem).readAttribute('bbcode') )    ? parseInt( $(buttonElem).readAttribute('bbcode') )    : _bbcode;
				_html   = ( $(buttonElem).readAttribute('html') )      ? parseInt( $(buttonElem).readAttribute('html') )      : _html;
				_emos   = ( $(buttonElem).readAttribute('emoticons') ) ? parseInt( $(buttonElem).readAttribute('emoticons') ) : _emos;
				_area   = ( $(buttonElem).readAttribute('area') )      ? $(buttonElem).readAttribute('area')                  : _area;
				
			} catch( ouch ) { }
			
			
		}
	},
	
	/**
	 * Bind a HTML checkbox to the toggle stuffs
	 */
	bindHtmlCheckbox: function( elem, editorId )
	{
		editorId = ( editorId ) ? editorId : ipb.textEditor.getEditor().editorId;
		
		if ( $( elem ) )
		{
			ipb.textEditor.htmlCheckbox[ editorId ] = $( elem );
			
			$( elem ).writeAttribute( 'data-editorId', editorId );
			
			$( elem ).observe('change', ipb.textEditor.htmlModeToggled.bindAsEventListener( this, $( elem ), true ) );
		}
	},
	
	/**
	 * HTML mode has been toggled so we force STD mode
	 */
	htmlModeToggled: function( e, elem )
	{
		var textObj  = ipb.textEditor.getEditor( $( elem ).readAttribute( 'data-editorId' ) );
		var isRte    = textObj.isRte();
		var button   = $( 'cke_' + textObj.editorId ).down('.cke_button_ipssource');
		
		/* @todo remove this in 3.4.3 - used only for the patch */
		ipb.lang['ckeditor__toggle_html_warning'] = "Toggling HTML to off will convert the editor's contents to BBCode resulting in the loss of any complex HTML you may have added";
		
		if ( elem.checked == true )
		{
			$('editor_html_message_' + textObj.editorId).show();
			
			textObj.CKEditor.ipsOptions.isHtml = 1;
			
			if ( isRte )
			{
				var replaceWith = textObj.getText();
	
				$( elem ).writeAttribute( 'data-was', 'rte' );
				ipb.textEditor.switchEditor( textObj.editorId );
				
				textObj.CKEditor.setData( replaceWith.unEscapeHtml() );
			}
			
			button.hide();
		}
		else
		{
			if ( isRte == 0 )
			{
				var _ok         = true;
				var replaceWith = textObj.getText();
				
				if ( replaceWith.length )
				{
					if ( ! confirm( ipb.lang['ckeditor__toggle_html_warning'] ) )
					{
						_ok = false;
					}
				}
				
				if ( _ok === true )
				{
					$('editor_html_message_' + textObj.editorId).hide();
					
					textObj.CKEditor.ipsOptions.isHtml = 0;
					
					if ( ! button.visible() )
					{
						button.show();
					}
					
					/* Convert HTML to BBCode in the source editor */
					_replaceWith = ( isRte ) ? _replaceWith : ipb.textEditor.ckPre( myParser.toBBCode( replaceWith ) );
					
					textObj.CKEditor.setData( _replaceWith );	
				}
			}
		}
	},
	
	/**
	 * Switches an editor from rte to std or vice-versa
	 */
	switchEditor: function( editorId  )
	{
		var textObj = ipb.textEditor.getEditor( editorId );
		var isRte   = textObj.isRte();
		
		textObj.setIsRte( ( isRte ) ? false : true, true );
		textObj.EditorObj.editor.setMode( ( isRte ) ? 'ipssource' : 'wysiwyg' );
	},
	
	/*
	 * Fetches an editor by ID
	 */
	getEditor: function( editorId )
	{
		editorId = ( ! editorId ) ? ipb.textEditor.getCurrentEditorId() : editorId;

		return ipb.textEditor.mainStore.get( editorId );
	},
	
	/**
	 * Fetches the current focused editor or the last one set up
	 * @returns string	editor id
	 */
	getCurrentEditorId: function()
	{
		if ( typeof(CKEDITOR) == 'undefined' || Object.isUndefined( CKEDITOR ) )
		{
			return ipb.textEditor.lastSetUp;
		}
		else
		{
			if ( CKEDITOR.currentInstance && ipb.textEditor.mainStore.get( CKEDITOR.currentInstance.name ).CKEditor )
			{
				return CKEDITOR.currentInstance.name;
			}
		    else
		    {
		    	return ipb.textEditor.lastSetUp;
		    }
		}
	},
	
	/*! Smilies to code */
	smiliesToCode: function( text )
	{
		if ( $(IPS_smiles.emoticons) )
		{
			$(IPS_smiles.emoticons).each( function( grin )
			{
				if ( grin.text !== null )
				{
					grin.text = grin.text.replace( /&lt;/g, '<' );
					grin.text = grin.text.replace( /&gt;/g, '>' );
			
					text = text.replace( new RegExp( '\\\[img=' + IPS_smiley_path  + grin.src.regExpEscape() + '\\\]', 'g' ), grin.text );
				}
			} );
		}
		
		return text;
	},
	
	/*! Code to smilies */
	codeToSmilies: function( text )
	{
		var invalidWrappers = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'\"/";
		var position        = 0;
		var emoPosition     = 0;
		
		var codeBlocks = [];
		var _c         = 0;
		
		/* Change... */
		while( _matches = text.match( /(<pre((\n|.)+?(?=<\/pre>))<\/pre>)/ ) )
		{
			var find    = _matches[0].regExpEscape();
			var replace = '<!--CODE_' + _c + '-->';
			
			/* Dollar signs in the code confuse javascript into thinking it's a replacement placeholder. */
			_matches[0] = _matches[0].replace( /\$/g, '&#36;' );
			
			codeBlocks[ _c ] = _matches[0];
			
			text = text.replace( new RegExp( find, 'g' ), replace );
			
			_c++;
		}
		
		while( _matches = text.match( /(\[code((\n|.)+?(?=\[\/code\]))\[\/code\])/ ) )
		{
			var find    = _matches[0].regExpEscape();
			var replace = '<!--CODE_' + _c + '-->';
			
			/* Dollar signs in the code confuse javascript into thinking it's a replacement placeholder. */
			_matches[0] = _matches[0].replace( /\$/g, '&#36;' );
			
			codeBlocks[ _c ] = _matches[0];
			
			text = text.replace( new RegExp( find, 'g' ), replace );
			
			_c++;
		}
		
		if ( $(IPS_smiles.emoticons) )
		{
			$(IPS_smiles.emoticons).each( function( grin )
			{
				if ( grin.text !== null )
				{
					emoPosition = 0;
					
					var testEmo = grin.text;
					testEmo     = testEmo.replace( /</g, '&lt;' );
					testEmo     = testEmo.replace( />/g, '&gt;' );
					testEmo     = testEmo.replace( /\[/g, '&#91;' );
					testEmo     = testEmo.replace( /\]/g, '&#93;' );
				
					while ( ( position = phpjs.stripos( text, testEmo, emoPosition ) ) !== false )
					{
						lastOpenTagPosition  = phpjs.strrpos( phpjs.substr( text, 0, position ), '[' );
						lastCloseTagPosition = phpjs.strrpos( phpjs.substr( text, 0, position ), ']' );
				
						if ( ( position === 0 || phpjs.stripos( invalidWrappers, phpjs.substr( text, position - 1, 1 ) ) === false )
							&& ( ( lastOpenTagPosition === false || lastCloseTagPosition === false ) || ( lastCloseTagPosition !== false && lastCloseTagPosition > lastOpenTagPosition ) )
							&& ( phpjs.strlen( text ) == ( position + phpjs.strlen( testEmo ) ) || phpjs.stripos( invalidWrappers, phpjs.substr( text, ( position + phpjs.strlen( testEmo ) ), 1 ) ) === false ) )
						{
							
							var replace = '<img src="' + IPS_smiley_path + grin.src + '" class="bbc_emoticon" title="' + grin.text + '" />';
							text = phpjs.substr_replace( text, replace, position, phpjs.strlen( testEmo ) );
											
							position += phpjs.strlen( replace );					
						}
						
						emoPosition = position + 1;
						
						if ( emoPosition > phpjs.strlen( text ) )
						{
							break;
						}
					}
				}
			} );
		}
		
		/* Change back... */
		var _c = 0;
		$(codeBlocks).each( function( code )
		{		
			text = text.replace( new RegExp( '<!--CODE_' + _c + '-->', 'g' ), codeBlocks[ _c ] );
			
			_c++;
		} );
		
		return text;
	},
	/**
	 * Make safe for CKEditor
	 */
	ckPre: function( text )
	{
		text = text.replace( '<script', '&#60;script' );
		text = text.replace( '</script', '&#60;/script' );
		
		/* Convert textual emos to images */
		text = ipb.textEditor.codeToSmilies( text );
		
		/* Make sure we're not tying to paste built quotes here yo */
		text = text.replace( /<p\s+?class=['"]citation["']>(.+?)<\/p>([\s\n]+?)?<blockquote/g, '$2<blockquote' );
		text = text.replace( /<blockquote class=['"]ipsBlockquote built['"]>/g, '<blockquote class="ipsBlockquote">' );
		
		return text;
	},
	
	/**
	 * Make safe for std editor
	 */
	stdPre: function( text )
	{
		/* &lt; / &gt; is made safe by CKEditor */
		text = text.replace( /&lt;/g, '<' );
		text = text.replace( /&gt;/g, '>' );
		
		/* Properly encoded HTML &amp;#39; isn't parsed in the text area */
		text = text.replace( /&amp;/g, '&' );
		
		if ( ! $(ipb.textEditor.htmlCheckbox) || ! $(ipb.textEditor.htmlCheckbox).checked )
		{
			//text = text.replace( /<br([^>]+?)?>(\n)?/g, '\n' );
		}
		
		Debug.write( '**' + text );
		
		return text;
	},
	
	/**
	 * 0 pad times
	 * @param n
	 * @returns
	 */
	pad: function(n)
	{
		return ("0" + n).slice(-2);
	},
	
	/**
	 * Convert Quotes
	 */
	convertQuotes: function( text )
	{			
		/* Insert cites */
		text = text.replace( /(<blockquote\s+class=['"]ipsBlockquote['"]\s*(data-author=['"](.+?)['"])?\s*(?:data-cid=['"]\d+?['"])?\s*(data-time=['"](\d+?)[\'"])?\s*>)/g, function ( whole, blockquote, _author, author, _date, date, offset )
		{
			if ( text.substr( offset + whole.length, 5 ) == '<cite' )
			{
				return whole;
			}

			if ( date )
			{
				if ( date == parseInt( date ) && date.length == 10 )
				{
					/* First, determine if we are in DST now */
					var _tz   = new Date().getTimezoneOffset() * 60;

					/* Find out if timestamp was in DST */
					var _date = new Date().getDateAndTime( parseInt( date ) );

					/* If there isn't a match, figure out the DST offset and then add it back in */
					if( _date['dst'] * 60 != _tz )
					{
						_tz	= _tz - _date['dst'] * 60;
						_date = new Date().getDateAndTime( parseInt( date ) - parseInt( _tz ) );
					}
					var _ampm = '';
					
					if( ipb.vars['hour_format'] == "12" )
					{
						if( _date['hour'] > 12 )
						{
							_date['hour']	-= 12;
							_ampm			= ' ' + ipb.lang['date_pm'];
						}
						else if( _date['hour'] == 12 )
						{
							_ampm			= ' ' + ipb.lang['date_pm'];
						}
						else if( _date['hour'] == 0 )
						{
							_date['hour']	= 12;
							_ampm			= ' ' + ipb.lang['date_am'];
						}
						else
						{
							_ampm			= ' ' + ipb.lang['date_am'];
						}
					}

					date      = _date['date'] + ' ' + _date['monthName'] + ' ' + _date['year'] + ' - ' + _date['hour'] + ':' + _date['min'] + _ampm;
				}
			}
											
			if ( author && date )
			{
				_text = ipb.lang['bbc_full_cite'].replace( '{author}', author ).replace( '{date}', date );
			}
			else if ( author )
			{
				_text = ipb.lang['bbc_name_cite'].replace( '{author}', author );
			}
			else if ( date )
			{
				_text = ipb.lang['bbc_date_cite'].replace( '{date}', date );
			}
			else
			{
				_text = ipb.lang['quote_title'];
			}
						
			return blockquote + '<cite class="ipb" contenteditable="false">' + _text + '</cite>';
		});
				
		/* Return */
		return text;
	},
	
	/**
	 * Make Quote Event Handlers
	 */
	makeQuoteEventHandlers: function( editor )
	{	
		var qSel = new CKEDITOR.dom.selection( editor.document );
		if ( CKEDITOR.env.gecko || CKEDITOR.env.ie ) {
			citeTags = editor.document.getElementsByTag('cite');
			for ( var i = 0; i < citeTags.count() ; i++ )
			{
				tag = citeTags.getItem(i);
				if ( tag.hasClass('ipb') ) {
					tag.on( 'mouseup', function() {
						qSel.selectElement( this.getParent() );
						});
				}
			}
		}
	}
};

/*
 * Each CKEditor object is referenced via this class
 */
IPBoard.prototype.textEditorObjects = Class.create( {
	
	editorId: {},
	popups: [],
	cookie: 'rte',
	timers: {},
	options: {},
	CKEditor: null,
	EditorObj: null,
	
	/*------------------------------*/
	/* Constructor 					*/
	initialize: function( editorId, options )
	{
		this.editorId = editorId;
		this.cookie   = ipb.Cookie.get('rteStatus');
		
		this.options  = Object.extend( { type: 	              'full',
										 ips_AutoSaveKey:      false,
										 height:			   0,
										 minimize:             0,
										 minimizeNowOpen:      0,
										 isHtml: 			   0,
										 bypassCKEditor:	   0,
										 isTypingCallBack:     false,
										 delayInit:			   false,
										 noSmilies:			   false,
										 disabledTags:		   [],
										 ips_AutoSaveData:     {},
										 ips_AutoSaveTemplate: new Template( ipb.textEditor.IPS_AUTOSAVETEMPLATE ) }, arguments[1] );
		
		
		/* Do we have an override? */
		if ( this.options.isHtml )
		{
			this.setIsRte( 0, true );
		}
		else
		{
			this.setIsRte( this.cookie == 'rte' ? 1 : ( this.cookie == 'std' ? 0 : 1 ) );		
		}
		
		/* Create the CKEditor */
		if ( ! this.options.delayInit )
		{
			this.initEditor();
		}
		
		if ( this.isRte() )
		{
			this.setText( ipb.textEditor.convertQuotes( this.getText() ) );
		}
	},
	
	/**
	 * Set RTe status of current editor
	 */
	setIsRte: function( value, noSaveChange )
	{
		value      = ( value ) ? 1 : 0;
		saveCookie = ( noSaveChange ) ? false : true;
		
		this.options.isRte = value;			
		
		if ( $( 'isRte_' + this.editorId ) )
		{
			 $( 'isRte_' + this.editorId ).value = value;
		}
		
		if ( ipb.textEditor.htmlCheckbox[ this.editorId ] )
		{
			if ( ! value )
			{
				//ipb.textEditor.htmlCheckbox[ this.editorId ].setAttribute( 'disabled', 'disabled' );
			}
			else
			{
				//ipb.textEditor.htmlCheckbox[ this.editorId ].removeAttribute( 'disabled' );
			}
		}
		
		/* Set cookie */
		if ( saveCookie )
		{
			ipb.Cookie.set( 'rteStatus', ( value ) ? 'rte' : 'std', 1 );
		}
	},
	
	/**
	 * Returns whether current editor is RTE (ckeditor)
	 */
	isRte: function()
	{
		return this.options.isRte ? 1 : 0;
	},
	
	/**
	 * Fetch editor contents 
	 */
	getText: function()
	{
		var val = '';
		
		if ( ! this.options.bypassCKEditor && this.CKEditor )
		{
			val = this.CKEditor.getData();
		}
		else
		{
			/* If CKEditor isn't being used (iOS, etc) */
			if( $( this.editorId ) )
			{
				val = $( this.editorId ).value;
			}
		}
		
		return val;	
	},
	
	/**
	 * Set editor contents 
	 */
	setText: function( contents )
	{
		if ( ! this.options.bypassCKEditor && this.CKEditor )
		{
			this.CKEditor.setData( contents );
		}
		else
		{
			/* If CKEditor isn't being used (iOS, etc) */
			if( $( this.editorId ) )
			{
				$( this.editorId ).value = contents;
			}
		}
	},
	
	/**
	 * Create the CKEditor
	 */
	initEditor: function(initContent)
	{
		/* Bypassing the CKEditor completely? Why so mean? */
		if ( this.options.bypassCKEditor )
		{
			if ( this.options.minimize )
			{
				this.options.minimizeNowOpen = 1;
			}
			
			if ( typeof( initContent ) == 'string' )
			{
				$( this.editorId ).value = initContent;
			}
			
			$('isRte_' + this.editorId).value      = 0;
			$('noCKEditor_' + this.editorId).value = 1;
			
			return;
		}
		
		/* Switch around the BBCode non JS version yo */
		if ( $( this.editorId + '_js' ) )
		{
			var name = $( this.editorId ).getAttribute('name');
			
			$( this.editorId ).remove();
			$( this.editorId + '_js' ).setAttribute( 'name', name );
			$( this.editorId + '_js' ).setAttribute( 'id', this.editorId );
			
			/* Disable noCKE flag */
			$('noCKEditor_' + this.editorId).value = 0;
		}
		
		/* Start the process of initiating */
		if ( $( this.editorId ).value )
		{
			$( this.editorId ).value = ( ! this.options.isHtml ) ? ipb.textEditor.ckPre( $( this.editorId ).value ) : ipb.textEditor.stdPre( $( this.editorId ).value );
		}
		
		/* RTE init */
		try
		{
			var config	= {
							toolbar:			( this.options.type == 'ipsacp' ) ? 'ipsacp' : ( this.options.type == 'mini' ? 'ipsmini' : 'ipsfull' ),
							height:				( Object.isNumber( this.options.height ) && this.options.height > 0 ) ? this.options.height : ( this.options.type == 'mini' ? 150 : 300 ),
						    ips_AutoSaveKey:	this.options.ips_AutoSaveKey
			};
		
			/* Minimized - force ipsmini */
			if ( this.options.minimize )
			{
				config.toolbarStartupExpanded = false;
			}
			
			CKEDITOR.replace( this.editorId, config );
		}
		catch( err )
		{
			Debug.write( 'CKEditor error: ' + err );
		}
		
		this.CKEditor = CKEDITOR.instances[ this.editorId ];
		
		/* HTML mode? */
		this.CKEditor.ipsOptions = { isMinimized: parseInt(this.options.minimize), isHtml: parseInt( this.options.isHtml ), startIsRte: ( this.isRte() ) ? 'rte' : 'source' };
		
		/* Bug in ckeditor init which treats initContent as event object inside an .on() */
		ipb.textEditor._tmpContent = ( Object.isString(initContent) || Object.isNumber(initContent) ) ? initContent : '';
		
		var setUpEditors = [];
		
		/* Got any saved data to show? */
		CKEDITOR.on( 'instanceReady', function( ev )
		{
			if ( ev.editor.name == this.editorId )
			{
				/* Make sure we don't set up multiple editors */
				if ( setUpEditors.indexOf( this.editorId ) != -1 )
				{
					return false;
				}
				
				/* Used for when init in source HTML mode */
				if ( $( this.editorId ).value )
				{
					ev.editor._defaultContent = $( this.editorId ).value;
				}
				
				setUpEditors.push( this.editorId );
				
				/* This is dumb and only way to access editor object */
				this.EditorObj = ev;
				
				/* Quickly make some changes if minimized */
				if ( this.options.minimize )
				{
					try
					{
						$('cke_' + this.editorId ).down('.cke_toolbox').hide();
						$('cke_top_' + this.editorId ).down('.cke_toolbox_collapser').hide();
						$('cke_bottom_' + this.editorId ).hide();
						
						$('cke_' + this.editorId ).down('.cke_wrapper').addClassName('minimized');
						
						if ( ! this.isRte() )
						{
							$('cke_' + this.editorId ).down('.cke_wrapper').addClassName('std');
						}
						else
						{
							/* IE bug when clicking the text editor to expand and it also registers a toolbar click*/
							if ( Prototype.Browser.IE9 )
							{
								$('cke_top_' + this.editorId ).down('.cke_toolbox').setStyle( { 'position': 'absolute', 'left': '-2000px' } );
							}
						}
					}catch(e){}
					
					ev.editor.on('focus', function()
					{
						try
						{
							if ( ! this.options.minimizeNowOpen )
							{ 
								if( typeof(this.showEditor()) != 'undefined' )
								{
									this.showEditor().bind(this);
								}
							}
						}catch(e){ Debug.error(e); }
					}.bind(this) );
				}
				
				/* Fix for some tags */
				new Array( 'p', 'ul', 'li', 'blockquote', 'div' ).each( function ( tag )
				{
					ev.editor.dataProcessor.writer.setRules( tag, {
																	indent : false,
																	breakBeforeOpen : true,
																	breakAfterOpen : false,
																	breakBeforeClose : false,
																	breakAfterClose : true
																  } );
				} );
				
				/* Insert */
				if ( ipb.textEditor._tmpContent.length )
				{
					ev.editor.setData( ipb.textEditor.ckPre( ipb.textEditor._tmpContent ) );
				}
				
				/* Clear tmp content */
				ipb.textEditor._tmpContent = '';
				
				this.displayAutoSaveData();
				
				if ( this.options.isTypingCallBack !== false )
				{
					this.timers['dirty'] = setInterval( this.checkForInput.bind(this), ipb.textEditor.IPS_TEXTEDITOR_POLLING );
				}
				
				/* Make sure our menus close */
				if ( $('cke_contents_' + this.editorId ).down('iframe') )
				{
					try
					{
						$('cke_contents_' + this.editorId ).down('iframe').contentWindow.document.onclick = parent.ipb.menus.docCloseAll;
					} catch( e ) { }
				}				
				
				/* Some ACP styles conflict */
				$$('.cke_top').each( function(elem) { elem.setStyle('background: transparent !important; padding: 0px !important'); } );
				$$('.cke_bottom').each( function(elem) { elem.setStyle('background: transparent !important; padding: 0px !important'); } );
				$$('.cke_contents').each( function(elem) { elem.setStyle('padding: 0px !important'); } );
				
				/* CKEditor tends to add a bunch of inline styles to cke_top_x which messes up custom styles */
				$('cke_top_' + this.editorId ).writeAttribute( 'style', '' );
				
				/* Update text-direction, since CKE forces LTR in 'source' mode */
				if( isRTL && !this.isRte() )
				{
					$$('.cke_contents > textarea').each( function(elem) {
						$(elem).setStyle( { textAlign: 'right' } );
					});
				}
				Debug.dir( this.options );
				
				/* PERMIT HTML IN ACP */
				/* Is HTML? */
				if ( this.options.isHtml == 1 || inACP )
				{
					if ( Object.isUndefined( ipb.textEditor.htmlCheckbox[ this.editorId ] ) || ipb.textEditor.htmlCheckbox[ this.editorId ] == null )
					{
						$('cke_' + this.editorId ).insert( { after: new Element( 'input', { type: 'checkbox', name: 'isHtml_' + this.editorId, id: 'cbx_' + this.editorId, checked: ( this.options.isHtml ) ? true : false } ).hide() } );
						$('cke_' + this.editorId ).insert( { after: new Element( 'br' ) } );
						
						ipb.textEditor.bindHtmlCheckbox( $('cbx_' + this.editorId ), this.editorId );
						
						/* Allow admin to toggle as normal */
						if ( inACP )
						{
							$('cbx_' + this.editorId ).show();
							$('cbx_' + this.editorId ).insert( { after: new Element( 'span', { 'class': '' } ).update( '&nbsp;' + ipb.lang['ckeditor__code_html'] ) } );
						}
					}
					
					if ( this.options.isHtml == 1 )
					{
						$( ipb.textEditor.htmlCheckbox[ this.editorId ] ).checked = true;
					}
				}
				
				/* HTML checkbox checked? */
				if ( $( ipb.textEditor.htmlCheckbox[ this.editorId ] ) )
				{
					/* Check status of HTML checkbox on load */
					if ( $( ipb.textEditor.htmlCheckbox[ this.editorId ] ).checked == true )
					{
						ipb.textEditor.htmlModeToggled( this, ipb.textEditor.htmlCheckbox[ this.editorId ] );
					}
				}
		
				if ( this.options.noSmilies )
				{
					$('cke_top_' + this.editorId ).down('.cke_button_ipsemoticon').up('.cke_button').hide();
				}
				
				if ( this.options.disabledTags.length )
				{
					this.options.disabledTags.each( function( tag )
					{
						switch( tag )
						{
							case 'font':
								$('cke_top_' + this.editorId ).down('.cke_font').up('.cke_rcombo').hide();
							break;
							case 'size':
								$('cke_top_' + this.editorId ).down('.cke_fontSize').up('.cke_rcombo').hide();
							break;
							case 'img':
								$('cke_top_' + this.editorId ).down('.cke_button_image').up('.cke_button').hide();
							break;
							case 'url':
								$('cke_top_' + this.editorId ).down('.cke_button_link').up('.cke_button').hide();
								$('cke_top_' + this.editorId ).down('.cke_button_unlink').up('.cke_button').hide();
							break;
							case 'code':
								$('cke_top_' + this.editorId ).down('.cke_button_ipscode').up('.cke_button').hide();
							break;
							case 'quote':
								$('cke_top_' + this.editorId ).down('.cke_button_ipsquote').up('.cke_button').hide();
							break;
							case 'color':
								$('cke_top_' + this.editorId ).down('.cke_button_textcolor').up('.cke_button').hide();
							break;							
						}
					}.bind(this) );
				}
				
				/* Add an onclick handler to cite tags which will remove focus so that FireFox users can't delete it */
				ipb.textEditor.makeQuoteEventHandlers( ev.editor );
				
				/* Add a click handler on the surrounding box so that IE10 for touchscreens can access it - see http://community.invisionpower.com/resources/bugs.html/_/ip-board/editor-doesnt-work-on-ie10-touch-screens-r40497 */
				if ( CKEDITOR.env.ie ) {
					$('cke_' + this.editorId ).on( 'click', function( e ) {
						ev.editor.focus();
					} );
				}
				
				/* Hidden text input for focus management for a Firefox bug
				 * @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/polish-national-characters-dont-appear-when-replying-with-quote-r40119 */
				if ( isRTL )
				{
					$$('body').first().insert( new Element( 'input', { type: 'text', id: 'hte_' + this.editorId, style: 'position:absolute;right:-1000px;display:none;' } ) );
				}
				else
				{
					$$('body').first().insert( new Element( 'input', { type: 'text', id: 'hte_' + this.editorId, style: 'position:absolute;left:-1000px;display:none;' } ) );
				}

				/* Stuff to do on key press */
				_this = this;
				ev.editor.on( 'key', function( e ) {
					/* Is our cursor at the front of a quote box? */
					/* If we capture "delete" key, you can't delete text in a quote box at the beginning */
					if ( ev.editor.document && ( e.data.keyCode == 8 || /*e.data.keyCode == 46 ||*/ e.data.keyCode == 37 || e.data.keyCode == 38 ) )
					{
						var selection = ev.editor.getSelection();
						blockquoteTags = ev.editor.document.getElementsByTag('blockquote');
						for ( var i = 0; i < blockquoteTags.count() ; i++ )
						{							
							tag = blockquoteTags.getItem(i);
							
							if ( selection.getType() == CKEDITOR.SELECTION_TEXT && selection.getSelectedText() == '' )
							{
								ranges = selection.getRanges();

								if ( ranges[0].checkBoundaryOfElement( tag.getFirst().getNext(), CKEDITOR.START ) )
								{
									/* We're only capturing backspace and only if there is nothing selected - otherwise the browser should just delete the selected text.
										Note that if we are before the quote, the previous node is null (beginning of editor) while it will not be when cursor is inside quote */
									if ( ranges[0].getPreviousNode() != null && e.data.keyCode == 8 && !IPSCKTools.getSelectionHtml( ev.editor ) )
									//if ( e.data.keyCode == 8 || e.data.keyCode == 46 )
									{
										next = tag.getNext();
										if ( next == null )
										{
											next = CKEDITOR.dom.element.createFromHtml( '<p>&nbsp;</p>' );
											next.insertAfter( tag );
										}
										Debug.write("Removing tag: " + tag.getFirst().getName());
										tag.getFirst().remove();

										if( tag.getChild(0).getName() == 'div' )
										{
											tag.getChild(0).moveChildren( next );
										}
										else
										{
											tag.moveChildren( next );
										}
										Debug.write("Removing tag: " + tag.getName());
										tag.remove();
									}
									/* We should only do this if nothing is selected */
									else if( !IPSCKTools.getSelectionHtml( ev.editor ) ) {	
									//else {
										// Wrapped in try/catch as arrow keys in <cite contenteditable="false"> throws "tag.getFirst().getNext().getHtml() is not a function"
										try
										{
											if ( tag.getFirst().getNext().getHtml().length <= 1 ) {
												tag.getFirst().getNext().appendHtml( '&nbsp;' );
											}
											if ( tag.getPrevious() == null ) {
												para = CKEDITOR.dom.element.createFromHtml( '<p>&nbsp;</p>' );
												tag.insertBeforeMe( para );
												selection.selectElement( para );
											}
										}
										catch( e ){}
									}
								}
							}
						}
					}

					/* IE does not remove the <cite> tag when it removes the blockquote, likely due to contentEditable being false */
					if( e.data.keyCode == 46 && CKEDITOR.env.ie )
					{
						var selection = ev.editor.getSelection();

						if ( selection.getType() == CKEDITOR.SELECTION_TEXT && selection.getSelectedText() == '' )
						{
							ranges = selection.getRanges();

							if( ranges[0].getCommonAncestor(true,false).getNext().getFirst().getName() == 'cite' )
							{
								ranges[0].getCommonAncestor(true,false).getNext().getFirst().remove();
							}
						}
					}

					/* @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/blank-line-before-quote-r41342
						If we hit "delete" before a quote, Chrome is inside a <p> tag but does not register as removing the p tag and instead
						deletes the next tag, which may be <cite> - this code is designed to capture this scenario and instead delete the <p>
						tag, removing the blank line before the quote box */
					var setTimeouts = true;

					if( e.data.keyCode == 46 && CKEDITOR.env.webkit )
					{
						var selection = ev.editor.getSelection();

						if ( selection.getType() == CKEDITOR.SELECTION_TEXT && selection.getSelectedText() == '' )
						{
							ranges = selection.getRanges();

							try
							{
								if( ranges[0].getCommonAncestor(true,false).getName() == 'p' && ranges[0].getCommonAncestor(true,false).getNext().getName() == 'blockquote' )
								{
									/* Ok this is really hacky, but I can't find a way around it.
										We are here because we want to delete the blank line before the quote box, so we have determined we are in a <p> and the next element is <blockquote>.
										The remove() call below works fine, however we cannot stop the event propogation (this method does not pass the original event object, but a custom ckeditor one).
										Subsequently, the delete key still fires and ends up deleting the first character inside the quote box.
										By adding a blank space, this is all "fixed" for the user...but surely there must be a better way.
										@link http://community.invisionpower.com/index.php?app=ccs&module=pages&section=comments&database=12&record=41342&do=findComment&comment_id=111617 */
									ranges[0].getCommonAncestor(true,false).getNext()
										.getFirst( function(_node){ return _node.type == CKEDITOR.NODE_ELEMENT && _node.is( 'p' ); } )
										.append( CKEDITOR.dom.element.createFromHtml( '&nbsp;' ), true );
									ranges[0].getCommonAncestor(true,false).remove();
									setTimeouts	= false;
								}
							} catch( ex ){}
						}
					}

					/* Annnnnnnd...now for Firefox.  Sure wish all browsers behaves the same */
					if( e.data.keyCode == 46 && CKEDITOR.env.gecko )
					{
						var selection = ev.editor.getSelection();
						ranges = selection.getRanges();

						/* @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/cant-delete-blank-line-in-quote-r41794
							If we hit delete and the next element is a <br> inside the <div>, it's not removed properly in Firefox. */
						try
						{
							if( ranges[0].getCommonAncestor(true,false).getName() == 'div' && ranges[0].getCommonAncestor(true,false).getParent().getName() == 'blockquote' )
							{
								var _div		= ranges[0].getCommonAncestor(true,false);
								var _bq			= ranges[0].getCommonAncestor(true,false).getParent();
								var _firstChild = ranges[0].getCommonAncestor(true,false).getChild(0);
								var _secondChild = ranges[0].getCommonAncestor(true,false).getChild(1);
								var _thirdChild = ranges[0].getCommonAncestor(true,false).getChild(2);

								/* If we have <blockquote><div><br><p>text</p></div></blockquote>... */
								if( _firstChild.getName() == 'br' && _secondChild.getName() == 'p' && _thirdChild == null )
								{
									/* Grab the paragraph */
									var _pelement	= _secondChild.clone(true);
									/* Remove the div */
									Debug.write("Removing tag: " + _div.getName());
									_div.remove();
									/* Put paragraph back into blockquote */
									_bq.append( _pelement );
									/* Now, the "delete" key will still bubble up, so we have to add a blank character to be deleted and then select it */
									var _space = _pelement.append( CKEDITOR.dom.element.createFromHtml( '&nbsp;' ), true );
									selection.selectElement( _space );
								}
							}
						} catch( ex ){ Debug.write( "Error: " + ex ); }
					}
				
					/* Clean up quote boxes. have to run it twice as the first time may remove stuff */
					if( setTimeouts )
					{
						setTimeout( function() { _this.quoteCleanUp( e, ev, false ) }, 100 );
						setTimeout( function() { _this.quoteCleanUp( e, ev, false ) }, 200 );
					}
				});
				ev.editor.on( 'paste', function( e ) {
					setTimeout( function() { _this.quoteCleanUp( e, ev, true ) }, 100 );
					setTimeout( function() { _this.quoteCleanUp( e, ev, true ) }, 200 );
				});
			}
		}.bind(this) );
	},
	
	quoteCleanUp: function( e, ev, isPaste )
	{
		if ( this.isRte() )
		{
			var selection = ev.editor.getSelection();
			
			/* Have we just arrowed into a cite tag? Firefox lets you start typing there, so move out if so */
			if ( e.data.keyCode == 37 || e.data.keyCode == 38 || e.data.keyCode == 39 || e.data.keyCode == 40 ) {
				var selectedElement = selection.getCommonAncestor();
				
				if ( typeof( selectedElement.getName ) != 'undefined' ) {
					if ( selectedElement.getName() == 'blockquote' ) {
						if ( e.data.keyCode == 37 || e.data.keyCode == 38 ) {
							if ( selectedElement.getPrevious() == null ) {
								para = CKEDITOR.dom.element.createFromHtml( '<p>&nbsp;</p>' );
								selectedElement.insertBeforeMe( para );
								selection.selectElement( para );
							} else {
								selection.selectElement( selectedElement.getPrevious() );
							}
						} else {
							selection.selectElement( selectedElement.getChild(1) );
						}
						selected_ranges = selection.getRanges();
						selected_ranges[0].collapse(true);
						selection.selectRanges(selected_ranges);
					}
				}
				else if( selection.getStartElement().getName() == 'pre' && ( e.data.keyCode == 37 || e.data.keyCode == 38 ) )
				{
					ranges				= selection.getRanges();

					if( ranges[0].startOffset == 0 )
					{
						var selectedElement	= selection.getStartElement();

						if ( selectedElement.getPrevious() == null ) {
							para = CKEDITOR.dom.element.createFromHtml( '<p>&nbsp;</p>' );
							selectedElement.insertBeforeMe( para );
							selection.selectElement( para );
							
							selected_ranges = selection.getRanges();
							selected_ranges[0].collapse(true);
							selection.selectRanges(selected_ranges);
						}
					}
				}
			}
		
			/* Have we just pressed enter or delete or paste? If so, check quotes are okay */
			if ( e.data.keyCode == 8 || e.data.keyCode == 46 || e.data.keyCode == 13 || isPaste == true )
			{
				/* Set up a default cite */
				prevCite = CKEDITOR.dom.element.createFromHtml( '<cite class="ipb" contenteditable="false">'+ipb.lang['ckeditor__quotelabel']+'</cite>' );
			
				/* Loop quote tags */
				blockquoteTags = ev.editor.document.getElementsByTag('blockquote');
				for ( var i = 0; i < blockquoteTags.count() ; i++ )
				{							
					haveCite = false;
					tag = blockquoteTags.getItem(i);
					
					/* Loop child tags */
					children = tag.getChildren();
					for ( var j = 0; j < children.count() ; j++ )
					{
						child = children.getItem(j);
														
						if ( child.getName !== undefined && child.getName() == 'cite' && child.hasClass('ipb') ) {
							haveCite = true;
							prevCite = child.clone( true );
						}
					}
					
					/* If we don't have a <cite> tag */
					if ( !haveCite )
					{				
						/* From deleting (Webkit does this when you hit backspace at the beginning of the quote, or highlight some text inside the quote and some outside) - move the contents out of the quote box, and remove it */
						if ( e.data.keyCode == 8 || e.data.keyCode == 46 )
						{
							next = tag.getNext();
							if ( next == null )
							{
								next = CKEDITOR.dom.element.createFromHtml( '<p>&nbsp;</p>' );
								next.insertAfter( tag );
							}
						
							tag.moveChildren( next );
							Debug.write("Removing tag: " + tag.getName());
							tag.remove();
						}
						/* From hitting enter - add one in */
						else
						{
							tag.append( prevCite, true );
						}
					}
				}
			}
		}
	},
	
	/**
	 * Init CKEditor
	 */
	showEditor: function( content )
	{
		if ( this.options.minimize )
		{
			this.options.minimizeNowOpen = 1;					
			
			$('cke_top_' + this.editorId ).down('.cke_toolbox_collapser').show();
			$('cke_bottom_' + this.editorId ).show();
			$('cke_' + this.editorId ).down('.cke_wrapper').removeClassName('minimized');
			$('cke_' + this.editorId ).down('.cke_toolbox').show();
			
			/* IE bug when clicking the text editor to expand and it also registers a toolbar click*/
			if ( Prototype.Browser.IE9 )
			{
				setTimeout( function() { $('cke_top_' + this.editorId ).down('.cke_toolbox').setStyle( { 'position': 'relative', 'left': '0px' } ); }.bind(this), 300 );
			}
			
			/* Shift screen if need be */
			try
			{
				var dims       = document.viewport.getDimensions();
				var editorDims = $('cke_' + this.editorId).getDimensions();
				var cOffset    = $('cke_' + this.editorId).cumulativeScrollOffset();
				var pOffset    = $('cke_' + this.editorId).positionedOffset();
				
				var bottomOfEditor = pOffset.top + editorDims.height;
				var bottomOfScreen = cOffset.top + dims.height;
				
				if ( bottomOfEditor > bottomOfScreen )
				{
					var diff = bottomOfEditor - bottomOfScreen;
					
					/* Scroll but with 100 extra pixels for comfort */
					window.scrollTo( 0, cOffset.top + diff + 100 );
				}
			}
			catch(e){ }
		}
	},
	
	/**
	 * Check for dirty status and throw it to a callback then cancel timer
	 */
	checkForInput: function()
	{
		if ( this.options.minimize == 1 && this.options.minimizeNowOpen == 0 )
		{
			return false;
		}
		
		/* Only RTE */
		if ( ! this.isRte() )
		{
			return false;
		}
		
		var content = this.getText();
		
		if ( content && content.length && Object.isFunction( this.options.isTypingCallBack ) )
		{
			/* And cancel timer */
			clearInterval( this.timers['dirty'] );
			this.timers['dirty'] = '';
			
			/* We have content, so throw to call back */
			this.options.isTypingCallBack();
		}
	},
	
	/**
	 * Close previously minimized editor
	 */
	minimizeOpenedEditor: function()
	{	
		if ( this.options.minimize == 1 && this.options.minimizeNowOpen == 1 )
		{ 
			if ( ! this.options.bypassCKEditor && this.CKEditor )
			{
				if ( $('cke_' + CKEDITOR.plugins.ipsemoticon.editor.name + '_stray') )
				{
					$('cke_' + CKEDITOR.plugins.ipsemoticon.editor.name + '_stray').remove();
					
					if ( $('ips_x_smile_show_all') )
					{
						$('ips_x_smile_show_all').remove();
					}
				}
								
				/* Re-shrink editor */
				$('cke_top_' + this.editorId ).down('.cke_toolbox_collapser').hide();
				$('cke_bottom_' + this.editorId ).hide();
				if ( this.isRte() )
				{
					$('cke_' + this.editorId ).down('.cke_wrapper').removeClassName('std');
				}
				else
				{
					$('cke_' + this.editorId ).down('.cke_wrapper').addClassName('std');
				}
				$('cke_' + this.editorId ).down('.cke_wrapper').addClassName('minimized');
				$('cke_' + this.editorId ).down('.cke_toolbox').hide();
				
				this.EditorObj.editor.setData('<p></p>');
				this.EditorObj.editor.focusManager.forceBlur();
				
				this.options.minimizeNowOpen = 0;
			}
			
			if ( this.options.bypassCKEditor )
			{
				$( this.editorId ).value = '';
			}
			
			try
			{
				if ( ! Object.isUndefined( $H( this.timers ) ) )
				{
					$H( this.timers ).each( function (timer)
					{
						var name = timer.key;
						
						if ( name.match( /^interval_/ ) )
						{
							clearInterval( this.timers[ name ] );
							Debug.write( 'Cleared Interval ' + name );
						}
						else
						{
							clearTimeout( this.timers[ name ] );
							Debug.write( 'Cleared Timeout ' + name );
						}
						
						this.timers[ name ] = '';
					}.bind(this) );
				}
			}
			catch(e) { Debug.error(e); }
			
			window.focus();
		}
	},
	
	/**
	 * Make sure editor is in view
	 * 
	 */
	scrollTo: function()
	{
		var dims = document.viewport.getDimensions();
		var where = $( this.editorId ).positionedOffset();
		var offsets = document.viewport.getScrollOffsets();

		/* Is editor off the page? */
		if ( offsets.top + dims.height < where.top )
		{
			window.scroll( 0, ( parseInt( where.top ) - 200 ) );
		}
	},
	
	/**
	 * Remove editor completely
	 */
	remove: function()
	{
		CKEDITOR.instances[ this.editorId ].destroy( true );
		
		this.CKEditor	= null;
		this.EditorObj  = null;
		
		/* Remove object */
		ipb.textEditor.mainStore.unset( this.editorId );

		/* Remove timers */
		if ( ! Object.isUndefined( $H( this.timers ) ) )
		{
			$H( this.timers ).each( function (timer)
			{
				var name = timer.key;
				
				if ( name.match( /^interval_/ ) )
				{
					clearInterval( this.timers[ name ] );
					Debug.write( 'Cleared Interval ' + name );
				}
				else
				{
					clearTimeout( this.timers[ name ] );
					Debug.write( 'Cleared Timeout ' + name );
				}
				
				this.timers[ name ] = '';
			}.bind(this) );
		}
	},
	
	/**
	 * Inserts content into the text editor
	 */
	insert: function( content, scrollOnInit, clearFirst )
	{
		/* Minimized... */
		if ( this.options.minimize == 1 && this.options.minimizeNowOpen != 1 )
		{
			/* Scroll to editor when it loads? */
			if ( scrollOnInit )
			{
				$( this.editorId ).scrollTo();
			}
			
			this.showEditor();
			
			try
			{
				/* Force toolbar to show */
				this.EditorObj.editor.execCommand('toolbarCollapse');
				this.EditorObj.editor.execCommand('toolbarCollapse');
			}
			catch(e){}
		}
		else
		{
			/* Scroll always? */
			if ( scrollOnInit === 'always' )
			{
				if ( this.options.bypassCKEditor != 1 )
				{ 
					$( 'cke_' + this.editorId ).scrollTo();
				}
				else
				{
					$( this.editorId ).scrollTo();
				}
			}
		}
				
		if ( this.options.bypassCKEditor != 1 )
		{		
			if ( this.isRte() )
			{												
				// Using insertHtml() because if you have content in the editor and insert more (i.e. click reply on two posts in a topic),
				// subsequent inserts will show the HTML instead of formatting it properly
				if ( clearFirst )
				{
					this.CKEditor.setData( ipb.textEditor.ckPre( content ) );
				}
				else
				{
					var edContents = this.CKEditor.getData();
					
					this.CKEditor.insertHtml( ipb.textEditor.ckPre( content ) );
					
					/* Firefox bug with insertHtml, ALT + {key} doesn't work after running when no other input in the editor */
					if ( edContents == '' )
					{
						var vscroll = (document.all ? document.scrollTop : window.pageYOffset);
						$('hte_' + this.editorId).focus();
						this.CKEditor.focus();
						window.scrollTo( 0, vscroll);
					}
				}				
			}
			else
			{
				/* We need to run this via CKEditors HTML processor to tidy up the HTML and make it consistent with the HTML editor */
				var formatted = this.CKEditor.dataProcessor.toHtml( content );
				var source    = this.CKEditor.dataProcessor.toDataFormat( formatted );
				
				content = ipb.textEditor.ckPre( source );
				 
				if ( this.CKEditor.getData() )
				{
					if ( clearFirst )
					{
						this.CKEditor.setData( content );
					}
					else
					{
						ipsTextArea.insertAtCursor( this.editorId, myParser.toBBCode( content ) );
					}
				}
				else
				{
					this.CKEditor.setData( content );
				}	
			}
		}
		else
		{
			/* Convert to BBCode */
			content = myParser.toBBCode( content );
			
			$( this.editorId ).value += content;
		}
	},

	
	/**
	 * Show any display data we might have
	 */
	displayAutoSaveData: function()
	{
		/* Keep looping until editor is ready */
		try
		{
			if ( Object.isUndefined( this.CKEditor ) || ! this.CKEditor.name || ! $('cke_' + this.editorId ) )
			{
				setTimeout( this.displayAutoSaveData.bind(this), 1000 );
				return;
			}
		} catch(e) { }
		
		Debug.write( 'Ready to show saved data: ' + 'cke_' + this.editorId );
	
		var sd = this.options.ips_AutoSaveData;
		
		if ( sd.key )
		{ 
			html  = this.options.ips_AutoSaveTemplate.evaluate( sd );
			
			if ( $('cke_' + this.editorId ).down('.cke_bottom').select('.cke_path').length < 1 )
			{
				$('cke_' + this.editorId ).down('.cke_resizer').insert( { before: new Element('div').addClassName('cke_path').update(html) } );
				
				ipb.delegate.register('._as_launch', this.launchViewContent.bind(this) );
			}
		}
	},
	
	/**
	 * Show the saved content in a natty little windah
	 */
	launchViewContent: function(e)
	{
		Event.stop(e);
		
		if ( ! Object.isUndefined( this.popups['view'] ) )
		{
			this.popups['view'].show();
		}
		else
		{			
			/* easy one this... */
			this.popups['view'] = new ipb.Popup( 'view', { type: 'modal',
											               initial: new Template( ipb.textEditor.IPS_AUTOSAVEVIEW ).evaluate( { content: this.options.ips_AutoSaveData.parsed } ),
											               stem: false,
											               warning: false,
											               hideAtStart: false,
											               w: '600px' } );
			
			ipb.delegate.register('._as_restore', this.restoreAutoSaveData.bind(this) );
		}
	},
	
	/**
	 * Show the about saved content in a natty little windah
	 */
	launchExplain: function(e)
	{
		Event.stop(e);
		
		var s = '__last_update_stamp_' + this.editorId;
		
		if ( ! Object.isUndefined( this.popups['explain'] ) )
		{
			this.popups['explain'].kill();
		}
					
		/* easy one this... */
		this.popups['explain'] = new ipb.Popup( 'explain', { type: 'balloon',
											                 initial: ipb.textEditor.IPS_AUTOSAVEEXPLAIN,
											                 stem: true,
											                 warning: false,
											                 hideAtStart: false,
											                 attach: { target: $$('.' + s ).first() },
											                 w: '300px' } );						
		
	},
	
	/**
	 * Restore auto saved content
	 */
	restoreAutoSaveData: function(e)
	{
		Event.stop(e);
		
		this.popups['view'].hide();
		
		if ( this.isRte() )
		{
			setTimeout( function() { this.CKEditor.setData( ipb.textEditor.ckPre( this.options.ips_AutoSaveData.raw ) ); }.bind(this), 500 );
		}
	},
	
	/**
	 * Save contents of the editor
	 * @param	object	Current editor object (passed via plugin)
	 * @param	object	Current command object (passed via plugin)
	 */
	save: function( editor, command )
	{
		var _url  = ipb.textEditor.ajaxUrl + '&app=core&module=ajax&section=editor&do=autoSave&secure_key=' + ipb.vars['secure_hash'] + '&autoSaveKey=' + this.options.ips_AutoSaveKey;
				
		Debug.write( _url );
		
		/* Fetch data */
		var content = this.CKEditor.getData();
		
		Debug.write( 'Fetched editor content: ' + content );
		
		new Ajax.Request( _url,
							{
								method: 'post',
								evalJSON: 'force',
								hideLoader: true,
								parameters: { 'content' : content.encodeParam() },
								onSuccess: function(t)
								{										    	
									/* No Permission */
									if ( t.responseJSON && ( t.responseJSON['status'] == 'ok' || t.responseJSON['status'] == 'nothingToSave' ) )
									{
										/* Reset 'dirty' */
										editor.resetDirty();
						
										/* No longer busy */
										command.setState( CKEDITOR.TRISTATE_OFF );
										
										if ( t.responseJSON['status'] == 'ok' )
										{
											this.updateSaveMessage();
										}
									}
								}.bind(this)
							} );	
		
	},
	
	/**
	 * Display the time the post was last auto saved
	 */
	updateSaveMessage: function()
	{
		var s = '__last_update_stamp_' + this.editorId;
		var d = new Date();
		var c = new Template( ipb.textEditor.IPS_SAVED_MSG ).evaluate( { time: d.toLocaleTimeString() } );
		
		/* remove old */
		$$('.' + s ).invoke('remove');
		
		/* Add new */
		$('cke_' + this.editorId ).down('.cke_resizer').insert( { before: new Element('div').addClassName('cke_path ' + s).update(c) } );
		
		ipb.delegate.register('._as_explain', this.launchExplain.bind(this) );
	},

	/**
	 * Clear auto-saved message
	 */
	clearSaveMessage: function()
	{
		$$('.__last_update_stamp_'  + this.editorId ).invoke('remove');
	}
} );