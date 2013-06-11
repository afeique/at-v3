/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function()
{ 
	CKEDITOR.dialog.add( 'ipscode', function( editor )
		{
			return {
				title : ipb.lang['ckeditor__code_title'],

				minWidth : CKEDITOR.env.ie && CKEDITOR.env.quirks ? 768 : 750,
				minHeight : 440,

				onShow : function()
				{
					// Reset the textarea value.
					this.getContentElement( 'general', 'content' ).getInputElement().setValue( '' );
					$$('.cke_dialog_contents .cke_pastetext').each( function(el) { el.setStyle( { width: '722px', margin: '0 auto' } ); } );
					$$('.cke_dialog_contents .cke_dialog_ui_input_textarea').each( function(el) { el.setStyle( { 'font-family': 'monospace', 'font-size': '13px' } ); } );

					// Set focus in the textarea
					this.getContentElement( 'general', 'content' ).getInputElement().focus();
				},

				onOk : function()
				{
					// Get the textarea value.
					var text   = this.getContentElement( 'general', 'content' ).getInputElement().getValue(),
						editor = this.getParentEditor();
					
					var codeType  = this.getContentElement( 'general', 'ctype' ).getInputElement().getValue();
					var lineNum   = this.getContentElement( 'general', 'clinenum' ).getInputElement().getValue();
					var className = '_prettyXprint';
					
					if ( codeType != 'auto' )
					{
						className += '  _lang-' + codeType;
					}
					
					if ( lineNum )
					{
						className += ' _linenums:' + parseInt( lineNum );
					}
					
 					setTimeout( function()
					{
						var el = new CKEDITOR.dom.element( 'pre' );
						el.addClass( className );
						el.appendText( text + "\n" );
						
						editor.insertElement( el );
						editor.insertHtml('<p></p>');
					}, 0 );
				},

				contents :
				[
					{
						label : ipb.lang['ckeditor__code_title'],
						id : 'general',
						elements :
						[
							{
								type : 'html',
								id : 'pasteMsg',
								html : '<div style="white-space:normal;width:720px;">' + editor.lang.clipboard.pasteMsg + '</div>'
							},
							{
								id : 'ctype',
								type : 'select',
								label : ipb.lang['ckeditor__codetypelabel'],
								items : [
											[ ipb.lang['ckeditor__code_generic'], 'auto' ],
											[ ipb.lang['ckeditor__code_js']     , 'js' ],
											[ ipb.lang['ckeditor__code_html']   , 'html' ],
											[ ipb.lang['ckeditor__code_sql']    , 'sql' ],
											[ ipb.lang['ckeditor__code_css']    , 'css' ],
											[ ipb.lang['ckeditor__code_xml']    , 'xml' ],
											[ ipb.lang['ckeditor__code_none']   , 'nocode' ],
										],
								commit : function( data )
								{
									var element = data.element;
		
									if ( this.getValue() )
										element.setAttribute( 'cke-saved-code-type', this.getValue() );
									else
									{
										element.setAttribute( 'cke-saved-code-type', false );
									}
								}
							},
							{
								id : 'clinenum',
								type : 'text',
								label : ipb.lang['ckeditor__code_linenum'],
								commit : function( data )
								{
									var element = data.element;
		
									if ( this.getValue() )
										element.setAttribute( 'cke-saved-code-linenum', this.getValue() );
									else
									{
										element.setAttribute( 'cke-saved-code-linenum', false );
									}
								}
							},
							{
								type : 'textarea',
								id : 'content',
								'default': '',
										
								onLoad : function()
								{
									var label = this.getDialog().getContentElement( 'general', 'pasteMsg' ).getElement(),
										input = this.getElement().getElementsByTag( 'textarea' ).getItem( 0 );

									input.setAttribute( 'aria-labelledby', label.$.id );
									input.setStyle( 'width', '98%' );
									input.setStyle( 'height', '240px' );
									input.setStyle( 'direction', editor.config.contentsLangDirection );
								},

								focus : function()
								{
									this.getElement().focus();
								},
								
								commit : function( data )
								{
								}
							}
						]
					}
				]
			};
		});
})();
