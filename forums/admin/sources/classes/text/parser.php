<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * HTML parsing core
 * Last Updated: $Date: 2012-06-08 09:28:02 +0100 (Fri, 08 Jun 2012) $
 * </pre>
 *
 * @author 		$Author: mmecham $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		9th March 2005 11:03
 * @version		$Revision: 10894 $
 *
 * <code>
 * $html = $editor->process( $_POST['Post'] );
 * 
 * require( IPS_ROOT_PATH . 'sources/classes/text/parser.php' );
 * 
 * $parser = new classes_text_parser();
 * print $parser->HtmlToBBCode( '<strong>Moo!</strong>' );
 * 
 * Prints:
 * [b]Moo[/b]
 * </code>
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * There are three modes
 * html: 		<strong>Moo</strong><br />[sharedmedia:1:string]
 * bbcode: 		[b]moo![/b]\n[sharedmedia:1:string]
 * display: 	<strong>Moo</strong><img src='....' />
 * @author matt
 *
 */
class classes_text_parser
{
	/**
	 * Settings
	 */
	protected static $Perms = array( 'skipBadWords' => false, 'parseBBCode' => true, 'parseHtml' => false, 'parseEmoticons' => true, 'parseArea' => 'posts' );
	private   $_errors;
	
	/**
	 * Used for acroynm replacement
	 */
	private $_currentAcronym = null;
	 
	/**
	 * Legacy method
	 * @todo remove in 4.0
	 */
	public $error = '';

	/**
	 * Force bbcode parser to kick in
	 * @var	bool
	 */
	protected $forceBbcode	= false;
	
	/**
	 * Force bbcode parser to kick in
	 * @var	bool
	 */
	protected static $NoBBCodeAutoLinkify = false;
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	@e void
	 */
	public function __construct()
	{
		/* Make object */
		$this->registry    =  ipsRegistry::instance();
		$this->DB	       =  $this->registry->DB();
		$this->settings    =& $this->registry->fetchSettings();
		$this->request     =& $this->registry->fetchRequest();
		$this->cache	   =  $this->registry->cache();
		$this->caches      =& $this->registry->cache()->fetchCaches();
		$this->lang        = $this->registry->getClass('class_localization');
		
		self::$Perms['memberData'] = ( is_array( self::$Perms['memberData'] ) ) ? self::$Perms['memberData'] : ipsRegistry::member()->fetchMemberData();
	}

	/**
	 * Force bbcode mode (used for emails where bbcode isn't used but autolink parsing needs to be done)
	 *
	 * @param	bool
	 * @return	null
	 */
	public function setForceBbcode( $force=false )
	{
		$this->forceBbcode	= $force;
	}
	
	/**
	 * Set multiple settings
	 * @param array $settings
	 */
	public function set( array $settings )
	{
		foreach( $settings as $setting => $value )
		{
			switch( $setting )
			{
				case 'parseBBCode':
					self::$Perms[ $setting ] = (bool) $value;
				break;
				case 'parseHtml':
					self::$Perms[ $setting ] = (bool) $value;
				break;
				case 'parseEmoticons':
					self::$Perms[ $setting ] = (bool) $value;
				break;
				case 'memberData':
					self::$Perms[ $setting ] = $value;
				break;
				case 'parseArea':
					self::$Perms[ $setting ] = $value;
				break;
			}
		}
	}
	
	/**
	 * Returns errors, yo.
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
	
	/**
	 * Display the HTML to IPB
	 * 
	 * Notes:
	 * CODE: Need to convert _prettyXprint, _linenums _lang- into correct class names
	 * @param	string  HTML
	 * @return	string	Fully parsed HTML
	 */
	public function display( $html )
	{
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/text/parser/bbcode.php', 'class_text_parser_bbcode' );
		$bbcodeParser = new $classToLoad();
		
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/text/parser/html.php', 'class_text_parser_html' );
		$htmlParser = new $classToLoad();
		
		if ( $this->isBBCode( $html ) )
		{
			$html = $bbcodeParser->BBCodeToHtml( $html );
		}

		/* Remove disabled tags */
		if ( ! self::$Perms['parseHtml'] )
		{ 
			$html = $this->removeDisabledTags( $html );
		}

		/* Parse display tags */
		$html = $bbcodeParser->toDisplay( $html );
		
		/* Finish off HTML display */
		$html = $htmlParser->toDisplay( $html );
		
		/* Emoticons */
		if ( self::$Perms['parseEmoticons'] )
		{
			$html = $this->parseEmoticons( $html );
		}
		
		/* Badwords */
		if ( ! self::$Perms['skipBadWords'] )
		{
			$html = $this->parseBadWords( $html );
		}
	
		if ( self::$Perms['parseHtml'] )
		{
			if ( IPS_APP_COMPONENT != 'forums' && IPS_APP_COMPONENT != 'members' )
			{
				/* Allow apps that don't save HTML state HTML editing */
				$html = str_replace( "&#39;" , "'", $html );
				$html = str_replace( "&#33;" , "!", $html );
				$html = str_replace( "&#036;", "$", $html );
				$html = str_replace( "&#124;", "|", $html );
				$html = str_replace( "&amp;" , "&", $html );
				$html = str_replace( "&gt;"	 , ">", $html );
				$html = str_replace( "&lt;"	 , "<", $html );
				$html = str_replace( "&#60;" , "<", $html );
				$html = str_replace( "&#62;" , ">", $html );
				$html = str_replace( "&quot;", '"', $html );
			}
			else
			{
				/* Fixes an issue with legacy posts */
				$html = str_replace( '&quot;', '"', $html );
				$html = str_replace( '&lt;', '<', $html );
				$html = str_replace( '&gt;', '>', $html );
			}
			
			/* Legacy posts and object/embed */
			preg_match_all( '#(codebase|src|href|pluginspage|data|value)="<a href=["\']([^"\']+?)["\']([^>]+?)?>(.+?)</a>#is', $html, $urlMatches );
			
			/* Finish up URLs and such */
			for( $i = 0 ; $i < count( $urlMatches[0] ) ; $i++ )
			{
				$raw  = $urlMatches[0][ $i ];
				$attr = $urlMatches[1][ $i ];
				$url  = $urlMatches[2][ $i ];
				$text = $urlMatches[4][ $i ];
				
				$html = str_replace( $raw, $attr . '="' . $url . '"', $html );
			}
			
			$html = preg_replace( '#<br([^>]+?)?>(\s+?)?<param#is', "<param", $html );
			$html = preg_replace( '#<br([^>]+?)?>(\s+?)?<embed#is', "<embed", $html );
			$html = preg_replace( '#<br([^>]+?)?>(\s+?)?</object>#is', "</object>", $html );
			
			/* Ugh... */
			$html = $this->HtmlAllowedPreContents( $html, 'pre', array( '<', '>' ) );
		}
		
		/* SEO stuffs */
		$html = $this->_seoAcronymExpansion( $html );
		
		/* Little secret codes */
		$html = str_ireplace( "(c)" , "&copy;", $html );
		$html = str_ireplace( "(tm)", "&#153;", $html );
		$html = str_ireplace( "(r)" , "&reg;" , $html );
	
		return $html;
	}
	
	/**
	 * Removes disabled tags
	 *
	 * @param	string
	 * @return	string
	 */
	public function removeDisabledTags( $html )
	{
		$disabledTagMap = array( 'b'     => array('b', 'strong'),
								 'i'     => array('em'),
						    	 's'     => array('strike'),
								 'sup'   => array('sup'),
								 'sub'   => array('sub'),
								 'code'  => array('pre'),
								 'quote' => array('blockquote'),
								 'url'   => array('a') );
		
		$allowedCssProps = array( 'color', 'font-family', 'font-size', 'background-color', 'font-weight', 'font-style', 'text-align', 'margin', 'margin-left', 'display' );
		$disabledTags    = $this->getDisabledTags();
	
		
		if ( count( $disabledTags ) )
		{
			$workingProps = array_combine( $allowedCssProps, $allowedCssProps );
			
			foreach( $disabledTags as $tag )
			{
				switch( $tag )
				{
					case 'font':
						unset( $workingProps['font-family'] );
					break;
					case 'color':
						unset( $workingProps['color'] );
					break;
					case 'background':
						unset( $workingProps['background-color'] );
					break;
					case 'size':
						unset( $workingProps['font-size'] );
					break;
				}
			}
		}
		else
		{
			return $html;
		}
		
		/* Take care of css styles first */
		preg_match_all( '#<(span|p|div) ([^>]+?)?style=([\'"])([^\'"]+?)([\'"])#i', $html, $matches, PREG_SET_ORDER );

		foreach( $matches as $val )
		{
			$all       = $val[0];
			$tag       = $val[1];
			$other     = $val[2];
			$open      = $val[3];
			$rawStyles = trim( $val[4] );
			$close     = $val[5];
			
			if ( count( $workingProps ) )
			{
				$styleArray = explode( ';', $rawStyles );
				$styleClean = array();
				
				foreach( $styleArray as $style )
				{
					$style = trim( $style );
					$tmp   = explode( ':', $style );
					$rule  = trim( $tmp[0] );
					
					if ( in_array( $rule, $workingProps) )
					{
						$styleClean[] = $style;
					}
					
				}
				
				if ( count( $styleClean ) )
				{
					$html = str_replace( $all, '<' . $tag . ' ' . $other . ' style=' . $open . implode( ';', $styleClean ) . $close, $html );
					
				}
				else
				{
					$html = str_replace( $all, '<' . $tag . $other, $html );
				}
				
			}
		}
		
		$tagArray = array();
		
		/* Now the rest of the tags */
		foreach( $disabledTags as $i => $tag )
		{
			if ( isset( $disabledTagMap[ $tag ] ) )
			{
				$tagArray = array_merge( $tagArray, $disabledTagMap[ $tag ] );
			}
			else
			{
				$tagArray[] = $tag;
			}
		}
		
		/* Make sure longest tags go first */
		rsort( $tagArray );
		
		foreach( $tagArray as $tag )
		{
			if ( $tag == 'font' OR $tag == 'background' OR $tag == 'color' OR $tag == 'size' )
			{
				continue;
			}
			
			/* else.. */
			preg_match_all( '#<(/)?' . $tag . '(\s([^>]+?)?>|>)#i', $html, $matches, PREG_SET_ORDER );

			foreach( $matches as $val )
			{
				$all       = $val[0];
				$close     = $val[1];
				$content   = $val[2];
				
				$html = str_replace( $all, '', $html );
			}
		}
		
		return $html;
	}
	
	/**
	 * Takes content from the DB and processes before it gets to the editor
	 *
	 * @param string $html
	 * @return	string
	 */
	public function htmlToEditor( $html )
	{
		$html = $this->_processNoParseCodes( $html, 1, true );

		/* Don't attempt to convert manually entered CODE and QUOTE as this can come from a preview where someone has entered
		 * manual tags in the RTE and breaks because this below fires up the legacy parser which expects <br> and not PRE linebreaks, etc
		 * And also they can retain the manual tags they entered.
		 */
		if ( ! $this->lang->isRtl() )
		{
			/* RTL tries to move square brakcets around */
			$html = str_ireplace( '[code' , '<!--open:code-->' , $html );
			$html = str_ireplace( '[quote', '<!--open:quote-->', $html );
			$html = str_ireplace( '[url'  , '<!--open:url-->', $html );
			$html = str_ireplace( '[img'  , '<!--open:img-->', $html );
		}
		
		/* Editing an older post? */
		if ( $this->isBBCode( $html ) )
		{
			self::$NoBBCodeAutoLinkify = true;
			
			$html = $this->BBCodeToHtml( $html );
			
			self::$NoBBCodeAutoLinkify = false;
		}
		
		$html = str_replace( '<!--open:code-->' , '[code' , $html );
		$html = str_replace( '<!--open:quote-->', '[quote', $html );
		$html = str_replace( '<!--open:url-->'  , '[url'  , $html );
		$html = str_replace( '<!--open:img-->'  , '[img'  , $html );
		
		/* Dollar signs confuse CKEditor */
		$html = str_replace( '$', '&#36;', $html );
		
		/* We want to restore CODE boxes */
		if ( preg_match( '#<pre\s+?class=["\']_prettyXprint#i', $html ) AND self::$Perms['parseHtml'] )
		{
			$html = $this->preToCode( $html );
		}
		
		/* ARGH MY EYES ARGH MY EYES SOMEONE DID A BAD WORD */
		$html = $this->parseBadWords( $html );
		
		/* Make sure no parse tags are correct */
		$html = $this->_processNoParseCodes( $html, 2 );
		$html = $this->_processNoParseCodes( $html, 3 );
		
		return $html;
	}
	
	/**
	 * Takes content from the editor and makes it lovely and clean for saving
	 * 
	 * @param string $html
	 * @return	string
	 */
	public function editorToHtml( $editor )
	{
		$editor = $this->emoticonImgtoCode( $editor );
		$editor = $this->_stripEmptyLeadingAndTrailingParagraphTags( $editor );
		
		$editor = $this->_processNoParseCodes( $editor, 1 );
		
		/* always make sure CODE goes first */
		foreach( array( 'codebox', 'code', 'xml', 'sql', 'html' ) as $tagName )
		{ 
			$editor = $this->codeToPre( $editor, $tagName );
		}

		/* Pre quote tags */
		$editor	= $this->blockquoteToBlockquote( $editor );

		/* MANUAL QUOTE TAGS */
		$editor = $this->quoteToBlockquote( $editor );
		
		/* Make sure no parse tags are correct */
		$editor = $this->_processNoParseCodes( $editor, 2 );
		//$editor = $this->_processNoParseCodes( $editor, 3 );
		
		return $editor;
	}

	/**
	 *  Code to pre
	 */
	public function codeToPre( $editor, $tagName )
	{
		$data    = $this->getTagPositions( $editor, $tagName, array( '[' , ']' ) );

		if ( is_array( $data['open'] ) )
		{
			foreach( $data['openWithTag'] as $id => $val )
			{
				$o = $data['openWithTag'][$id];
				$c = $data['closeWithTag'][$id] - $o;
				
				if ( $c < 1 )
				{
					continue;
				}
				
				$slice = substr( $editor, $o, $c );
				$openTag = substr( $editor, $o, $data['open'][$id] - $o );
				$closeTag = substr( $editor, $data['close'][$id], strlen( $tagName ) + 3 );
				
				$_openTagLen = strlen( $openTag );
				$_closeTagLen = strlen( $closeTag );
				
				list( $tag, $rawAttr )  = explode( '=', $openTag );
				list( $lang, $lineNum ) = explode( ':', str_replace( array( '[', ']' ), '', $rawAttr ) );
				
				// Fix lang if using XML tag
				if ( ! $lang && $tagName != 'code' )
				{
					$lang = $tagName;
				}
				
				// Need to bump up lengths of opening and closing
				$_origLength = strlen( $slice );
				
				$sliceContents = substr( $editor, $data['open'][$id], $data['close'][$id] - $data['open'][$id] );
				
				/* Extra conversion for BBCODE>HTML mode */
				$replacement = $sliceContents;
				$replacement = str_replace( array( "\r\n", "\r" ), "\n", $replacement );
				$replacement = preg_replace( '#(https|http|ftp)://#', '\1-~~-//', $replacement );
				$replacement = str_replace( '[', '&#91;', $replacement );
				$replacement = preg_replace( "#<br([^>]+?)?>(\n)?#i", "\n", $replacement );
				$replacement = trim( str_replace( "</p>\n", "\n", $replacement ) );
				
				/* Stop (r) (tm) and (c) from switching out */
				$replacement = preg_replace( '#\((tm|r|c)\)#i', '&#40;$1&#41;', $replacement );
				
				$replacement = IPSText::stripTags( $replacement, 'pre' );
				
				$slice = str_replace( $sliceContents, $replacement, $slice );
				
				/* add in class attributes */
				$classExtra .= '_linenums:' . intval( trim( $lineNum ) );
				
				if ( $lang )
				{
					$classExtra .= ' _lang-' . trim( htmlspecialchars( $lang ) );
				}
		
				/* Convert tags */
				$_newOpenTag = "<pre class=\"_prettyXprint " . $classExtra . "\">";
				$_newCloseTag = "</pre>";
				
				$slice = str_replace( $openTag, $_newOpenTag, $slice );
				$slice = str_replace( $closeTag, $_newCloseTag, $slice );
				
				$editor = substr_replace( $editor, $slice, $o, $c );
				
				break;
			}
		}
		
		/* Recursively parse quotes */
		if ( count( $data['open'] ) > 1 AND count( $data['close'] ) > 1 )
		{
			$editor	= $this->codeToPre( $editor, $tagName );
		}
			
		$editor = preg_replace( '#<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>(\s+?)?<pre#i', '<pre', $editor );
		$editor = preg_replace( '#</pre>(\s+?)?<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>#i', '</pre>', $editor );
		
		return $editor;
	}

	/**
	 * Convert QUOTE to blockquote
	 */
	public function blockquoteToBlockquote( $text )
	{
		/* BLOCKQUOTE: Fetch paired opening and closing tags */
		$data = $this->getTagPositions( $text, 'blockquote', array( '<' , '>' ) );

		if ( is_array( $data['open'] ) )
		{
			foreach( $data['open'] as $id => $val )
			{
				$o = $data['open'][ $id ];
				$c = $data['close'][ $id ] - $o;
		
				$slice = substr( $text, $o, $c );
				
				/* Need to bump up lengths of opening and closing */
				$_origLength = strlen( $slice );
		
				/* Wrap in a P tags */
				$slice = '<p>' . $this->_stripParagraphWrap( $this->blockquoteToBlockquote( $slice ) ) . '</p>';

				$_newLength  = strlen( $slice );
		
				$editor = substr_replace( $text, $slice, $o, $c );

				break;
			}
		}

		return $text;
	}

	/**
	 * Convert QUOTE to blockquote
	 */
	public function quoteToBlockquote( $text, $inner=false )
	{
		/* MANUAL QUOTE TAGS */
		$data    = $this->getTagPositions( $text, 'quote', array( '[' , ']' ) );

		if ( is_array( $data['open'] ) && count( $data['open'] ) == count( $data['close'] ) )
		{
			foreach( $data['openWithTag'] as $id => $val )
			{
				$o = $data['openWithTag'][ $id ];
				$c = $data['closeWithTag'][ $id ] - $o;

				if ( $o < 1 || $c < 1 )
				{
					continue;
				}
				
				$slice     = substr( $text, $o, $c );
				$openTag   = substr( $text, $o, $data['open'][ $id ] - $o  );
				$closeTag  = substr( $text, $data['close'][ $id ], 8 );

				$sliceContents = substr( $text, $data['open'][ $id ], $data['close'][ $id ] - $o );

				$slice = str_replace( $sliceContents, $this->_stripParagraphWrap( $sliceContents ), $slice );

				$options   = $this->getTagAttributes( $openTag );
				
				# Need to bump up lengths of opening and closing
				$_origLength = strlen( $slice );
		
				$ops = array();

				# Allow collapse
				$options['collapsed'] = ( isset( $options['collapse'] ) ) ? $options['collapse'] : $options['collapsed'];
				
				if ( $options['name'] )
				{
					$ops[] = 'data-author="' . $options['name'] . '"';
				}
				
				if ( $options['post'] )
				{
					$ops[] = 'data-cid="' . $options['post'] . '"';
				}
				
				if ( $options['timestamp'] )
				{
					$ops[] = 'data-time="' . $options['timestamp'] . '"';
				}
				
				if ( $options['date'] )
				{
					$ops[] = 'data-date="' . $options['date'] . '"';
				}
				
				if ( $options['collapsed'] )
				{
					$ops[] = 'data-collapsed="' . intval( $options['collapsed'] ) . '"';
				}

				$slice	= substr_replace( $slice, '</blockquote>', strlen($slice) - 8, 8 );
				$slice	= substr_replace( $slice, "<blockquote class='ipsBlockquote' " . implode( ' ', $ops ) . '>', 0, strlen($openTag) );

				$text = substr_replace( $text, '<p>' . $this->_stripParagraphWrap( $slice ) . '</p>', $o, $c );

				break;
			}
		}

		/* Recursively parse quotes */
		if ( count( $data['open'] ) > 1 && count( $data['close'] ) > 1 )
		{
			$text	= $this->quoteToBlockquote( $text, true );
		}	

		if( $inner )
		{
			return $text;
		}

		$text = preg_replace( '#<(div|p)([^>]+?)?>(?:\s+?)?(<blockquote([^>]+?)>)(?:\s+?)?</(div|p)>#', '\3<br />', $text );
		$text = preg_replace( '#<(div|p)([^>]+?)?>(?:\s+?)?(</blockquote>)(?:\s+?)?</(div|p)>#', '\3', $text );
		$text = preg_replace( '#<(div|p)([^>]+?)?>(?:\s+?)?(</p></blockquote>)(?:\s+?)?</(div|p)>#', '\3', $text );
		
		return $text;
	}
	
	/**
	 *  Pre to Code
	 */
	public function preToCode( $editor )
	{
		$tagName = 'pre';
		$data    = $this->getTagPositions( $editor, $tagName, array( '<' , '>' ) );
	
		if ( is_array( $data['open'] ) )
		{
			foreach( $data['openWithTag'] as $id => $val )
			{
				$o = $data['openWithTag'][$id];
				$c = $data['closeWithTag'][$id] - $o;
	
				if ( $c < 1 )
				{
					continue;
				}
	
				$slice    = substr( $editor, $o, $c );
				$openTag  = substr( $editor, $o, $data['open'][$id] - $o );
				$closeTag = substr( $editor, $data['close'][$id], strlen( $tagName ) + 3 );
				$lineNum  = 0;
				$lang     = 'auto';
				
				$_openTagLen  = strlen( $openTag );
				$_closeTagLen = strlen( $closeTag );
	
				/* Line num */
				preg_match( '#_linenums:(\d+?)#', $openTag, $match );
				
				if ( $match[1] )
				{
					$lineNum = intval( $match[1] );
				}
				
				/* Line num */
				preg_match( '#_lang-([a-z-_])#i', $openTag, $match );
				
				if ( $match[1] )
				{
					$lang = $match[1];
				}
	
				// Need to bump up lengths of opening and closing
				$_origLength = strlen( $slice );
	
				$sliceContents = substr( $editor, $data['open'][$id], $data['close'][$id] - $data['open'][$id] );
	
				/* Extra conversion for BBCODE>HTML mode */
				$replacement = $sliceContents;
	
				$replacement = IPSText::htmlspecialchars( $replacement );
	
				$slice = str_replace( $sliceContents, $replacement, $slice );
	
				/* Convert tags */
				$_newOpenTag = "[code=" . $lang . ':'. $lineNum . ']';
				$_newCloseTag = "[/code]";
	
				$slice = str_replace( $openTag, $_newOpenTag, $slice );
				$slice = str_replace( $closeTag, $_newCloseTag, $slice );
	
				$_newOpenTagLen = strlen( $openTag );
				$_newCloseTagLen = strlen( $closeTag );
				$_newLength = strlen( $slice );
	
				$editor = substr_replace( $editor, $slice, $o, $c );
	
				break;
			}
		}
	
		/* Recursively parse quotes */
		if ( count( $data['open'] ) > 1 )
		{
			$editor	= $this->preToCode( $editor );
		}
		
		return $editor;
	}
	
	/**
	 * Convert HTML to BBCode
	 * @param	string	HTML
	 * @param	string	BBCode
	 */
	public function HtmlToBBCode( $text )
	{
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/text/parser/html.php', 'class_text_parser_html' );
		$html = new $classToLoad();
		
		$text = $html->toBBCode( $text );
		
		return $text;
	}
	
	/**
	 * Convert BBCode to HTML
	 * @param   string $text
	 * @return 	string	$text
	 */
	public function BBCodeToHtml( $text )
	{
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/text/parser/bbcode.php', 'class_text_parser_bbcode' );
		$bbcode = new $classToLoad();
		
		if ( $this->isBBCode( $text ) )
		{ 
			$text = $bbcode->toHtml( $text );
		}
			
		return $text;
	}
	
	/**
	 * Does it need conversion?
	 * @param	string
	 * @return 	boolean
	 * @since	3.4
	 */
	public function isBBCode( $string )
	{ 
		if ( $this->forceBbcode )
		{
			return true;
		}
		
		if ( strstr( $string, '[' ) )
		{
			if ( preg_match( '#\[((?:b|i|u|s|strike|font|size|color|background|sup|sub|list|\*|url|img|center|left|right|indent|email|code|quote)(\s|\]|=))#i', $string, $matches ) )
			{
				return true;
			}
		}
			
		return false;
	}
	
	/**
	 * Takes HTML (*not* display) and checks it for built in limits such as quote and IMG
	 * 
	 * @param string $text
	 */
	public function testForParsingLimits( $text, $check=array('img', 'quote', 'emoticons', 'urls') )
	{
		$quoteCount = $this->getQuoteCount($text);
		$imageCount = $this->getImageCount($text);
		$emoCount   = $this->getEmoticonCount( $this->parseEmoticons( $text ) );

		/* IMG CHECK */
		if ( ( is_numeric( $this->settings['max_images'] ) ) && ( $check == 'all' || in_array( 'all', $check ) || in_array( 'img', $check ) ) )
		{
			if ( $imageCount > $this->settings['max_images'] )
			{
				$this->_addParsingError( 'too_many_img' );
			}
		}
		
		/* QUOTE CHECK */
		if ( ( is_numeric( $this->settings['max_quotes_per_post'] ) ) && ( $check == 'all' || in_array( 'all', $check ) || in_array( 'quote', $check ) ) )
		{
			if ( $quoteCount > $this->settings['max_quotes_per_post'] )
			{
				$this->_addParsingError( 'too_many_quotes' );
			}
		}
		
		/* EMO CHECK */
		if ( ( is_numeric( $this->settings['max_emos'] ) ) && ( $check == 'all' || in_array( 'all', $check ) || in_array( 'emoticons', $check ) ) )
		{
			if ( $emoCount > $this->settings['max_emos'] )
			{
				$this->_addParsingError( 'too_many_emoticons' );
			}
		}
		
		/* IMG EXT CHECK */
		preg_match_all( '#<img([^>]+?)?>#i', $text, $matches );
		foreach( $matches[1] as $id => $match )
		{
			if ( stristr( $match, 'src=' ) && ! stristr( $match, 'class="bbc_emoticon"' ) )
			{
				preg_match( '#src=[\'"]([^\'"]+?)[\'"]#i', $match, $url );
				
				if ( $this->isAllowedImgUrl( $url[1] ) !== true )
				{
					$this->_addParsingError( 'invalid_ext' );
					break;
				}
				
				if ( $this->isAllowedUrl( $url[1] ) !== true )
				{
					$this->_addParsingError( 'domain_not_allowed' );
					break;
				}
			}
		}
		
		/* A HREF CHECK */
		if ( $check == 'all' || in_array( 'all', $check ) || in_array( 'urls', $check ) )
		{
			preg_match_all( '#<a([^>]+?)?>#i', $text, $matches );
			foreach( $matches[1] as $id => $match )
			{
				if ( stristr( $match, 'href=' ) )
				{
					preg_match( '#href=[\'"]([^\'"]+?)[\'"]#i', $match, $url );
			
					if ( $this->isAllowedUrl( $url[1] ) !== true )
					{
						$this->_addParsingError( 'domain_not_allowed' );
						break;
					}
				}
			}
		}
		
		return ( count( $this->_errors ) ) ? false : true;
	}
	
	/**
	 * Get number of quotes
	 * @param	string
	 */
	public function getQuoteCount( $text )
	{
		return substr_count( $text, '<blockquote' );
	}
	
	
	/**
	 * Get the number of images
	 * @param string $text
	 */
	public function getImageCount( $text )
	{
		$count = 0;
		preg_match_all( '#<img([^>]+?)?>#i', $text, $matches );
		
		foreach( $matches[1] as $id => $match )
		{
			if ( ! stristr( $match, 'class="bbc_emoticon"' ) )
			{
				$count++;
			}
		}
		
		return $count;
	}
	
	/**
	 * Get the number of URLs
	 * @param string $text
	 */
	public function getUrlCount( $text )
	{
		$count = 0;
		preg_match_all( '#<a([^>]+?)?>#i', $text, $matches );
	
		foreach( $matches[1] as $id => $match )
		{
			if ( stristr( $match, 'href' ) )
			{
				$count++;
			}
		}
	
		return $count;
	}
	
	/**
	 * Get the number of images
	 * @param string $text
	 * @param	boolean	$parseTest
	 */
	public function getEmoticonCount( $text )
	{
		$count = 0;
		$text = str_replace( "<#EMO_DIR#>", "", $text );
		
		preg_match_all( '#<img([^>]+?)?>#i', $text, $matches );
	
		foreach( $matches[1] as $id => $match )
		{
			if ( stristr( $match, 'class="bbc_emoticon"' ) || stristr( $match, "class='bbc_emoticon'" ) )
			{
				$count++;
			}
		}
		
		return $count;
	}
	
	/**
	 * Is an allowed URL type
	 * @param string $url
	 * @return boolean
	 */
	public function isAllowedImgUrl( $url )
	{
		if ( $this->settings['img_ext'] )
		{
			$path	= @parse_url( html_entity_decode( trim( $url ) ), PHP_URL_PATH );
			$pieces	= explode( '.', $path );
			$ext	= array_pop( $pieces );
			$ext	= strtolower( $ext );

			if ( ! in_array( $ext, explode( ',', str_replace( '.', '', strtolower($this->settings['img_ext']) ) ) ) )
			{
				return false;
			}
		}

		return true;
	}
	
	/**
	 * Is allowed URL
	 * @param string $url
	 * @return boolean
	 */
	public function isAllowedUrl( $url )
	{
		if ( $this->settings['ipb_use_url_filter'] )
		{
			$list_type = $this->settings['ipb_url_filter_option'] == "black" ? "blacklist" : "whitelist";
				
			if( $this->settings['ipb_url_' . $list_type ] )
			{
				$list_values 	= array();
				$list_values 	= explode( "\n", str_replace( "\r", "", $this->settings['ipb_url_' . $list_type ] ) );
		
				if( $list_type == 'whitelist' )
				{
					$list_values[]	= "http://{$_SERVER['HTTP_HOST']}/*";
				}
		
				if ( count( $list_values ) )
				{
					$good_url = 0;
						
					foreach( $list_values as $my_url )
					{
						if( !trim($my_url) )
						{
							continue;
						}
		
						$my_url = preg_quote( $my_url, '/' );
						$my_url = str_replace( '\*', "(.*?)", $my_url );
		
						if ( $list_type == "blacklist" )
						{
							if( preg_match( '/' . $my_url . '/i', $url ) )
							{
								return false;
							}
						}
						else
						{
							if ( preg_match( '/' . $my_url . '/i', $url ) )
							{
								$good_url = 1;
							}
						}
					}
						
					if ( ! $good_url AND $list_type == "whitelist" )
					{
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Replace bad words
	 *
	 * @param	string	Raw text
	 * @return	string	Converted text
	 */
	public function parseBadWords( $text='' )
	{
		/* @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/report-center-bypass-word-filter-r40719 */
		if( self::$Perms['memberData']['member_group_id'] AND !self::$Perms['memberData']['g_id'] )
		{
			self::$Perms['memberData']	= array_merge( self::$Perms['memberData'], $this->caches['group_cache'][ self::$Perms['memberData']['member_group_id'] ] );

			if( self::$Perms['memberData']['mgroup_others'] )
			{
				self::$Perms['memberData']	= ips_MemberRegistry::setUpSecondaryGroups( self::$Perms['memberData'] );
			}
		}

		/* Empty text or bypass? */
		if ( $text == '' || self::$Perms['memberData']['g_bypass_badwords'] )
		{
			return $text;
		}
	
		$badwords  = $this->cache->getCache('badwords');
		$temp_text = $text;
		$urls      = array();
	
		/* Got any naughty words? */
		if ( ! is_array( $badwords ) OR ! count( $badwords ) )
		{
			return $text;
		}
	
		/* strip out URLs so replacements aren't made */
		preg_match_all( '#((http|https|news|ftp)://(?:[^<>\)\[\"\s]+|[a-zA-Z0-9/\._\-!&\#;,%\+\?:=]+))#is', $text, $matches );
	
		foreach( $matches[0] as $m )
		{
			$c = count( $urls );
			$urls[ $c ] = $m;
				
			$text = str_replace( $m, '<!--url{' . $c . '}-->', $text );
		}

		//-----------------------------------------
		// Convert back entities
		//-----------------------------------------
			
		for( $i = 65; $i <= 90; $i++ )
		{
			$text = str_replace( "&#" . $i . ";", chr($i), $text );
		}
	
		for( $i = 97; $i <= 122; $i++ )
		{
			$text = str_replace( "&#" . $i . ";", chr($i), $text );
		}
	
		//-----------------------------------------
		// Go all loopy
		//-----------------------------------------

		foreach( $badwords as $r )
		{
			$r['type']	= str_replace( '&', '&amp;', IPSText::UNhtmlspecialchars( $r['type'] ) );

			if ( $this->parseType != 'topics' )
			{
				$r['swop'] = strip_tags( $r['swop'] );
			}
	
			$replace	= $r['swop'] ? $r['swop'] : '######';

			if ( $r['m_exact'] )
			{
				$r['type']	= preg_quote( $r['type'], "/" );
	
				/* Link */
// 				if ( IPS_DOC_CHAR_SET == 'UTF-8' && IPSText::isUTF8( $text ) )
// 				{
// 					$text = preg_replace( '/(^|\p{L}|\s)' . $r['type'] . '(\p{L}|!|\?|\.|,|$)/i', "\\1{$replace}\\2", $text );
// 				}
// 				else
// 				{
				// \b does not work well because it matches word boundary, which is technically a \w to \W shift
				// @see http://stackoverflow.com/questions/6531724/how-exactly-do-regular-expression-word-boundaries-work-in-php
				// What we really want to look for is a non-word character on either side, so this works
				// Bad word filter for $!^& becomes $!^&amp;.  Submitted in a post that is <p>$!^&amp;</p> and </ is not a shift from non-word to word character
					$text = preg_replace( '/(^|\W)' . $r['type'] . '(\W|$)/i', "\\1" . $replace . "\\2", $text );
					
					/* I'd retest that for a dollar! */
					if ( strstr( $r['type'], '$' ) )
					{
						$test = preg_replace( '#(\\\\)?\$#', '$', $r['type'] );
						
						$text = preg_replace( '/(^|\W)' . preg_quote( $test ) . '(\W|$)/i', "\\1" . $replace . "\\2", $text );
					}
					
//				}
			}
			else
			{
				//----------------------------
				// 'ass' in 'class' kills css
				//----------------------------
	
				if( $r['type'] == 'ass' )
				{
					$text		= preg_replace( "/(?<!cl)" . $r['type'] . "/i", $replace, $text );
				}
				else
				{
					$text		= str_ireplace( $r['type'], $replace, $text );
				}
			}
		}

		/* replace urls */
		if ( count( $urls ) )
		{
			preg_match_all( '#\<\!--url\{(\d+?)\}--\>#is', $text, $matches );
				
			for ( $i = 0; $i < count($matches[0]); $i++ )
			{
				if ( isset( $matches[1][$i] ) )
				{
					$text = str_replace( $matches[0][$i], $urls[ $matches[1][$i] ], $text );
				}
			}
		}

		return $text ? $text : $temp_text;
	}
	
	/**
	 * Parse emoticons in text
	 *
	 * @param string $txt        	
	 * @return string $txt
	 */
	public function parseEmoticons( $txt )
	{
		/* Sort them in length order first */
		$this->_sortSmilies();
		
		$_codeBlocks = array();
		$_c 		 = 0;
		
		/* Now parse them! */
		if ( self::$Perms['parseEmoticons'] && ! $this->parse_html )
		{
			/* Make CODE tags safe... */
			while( preg_match( '/(<pre(.+?(?=<\/pre>))<\/pre>)/s', $txt, $matches ) )
			{
				$find    = $matches[0];
				$replace = '<!--Cj' . $_c . 'j-->';
				
				$_codeBlocks[ $_c ] = $find;
				
				$txt = str_replace( $find, $replace, $txt );
				
				$_c++;
			}
			
			/* Make CODE tags safe... */
			while( preg_match( '/(\[code(.+?(?=\[\/code\]))\[\/code\])/s', $txt, $matches ) )
			{
				$find    = $matches[0];
				$replace = '<!--Cj' . $_c . 'j-->';
			
				$_codeBlocks[ $_c ] = $find;
			
				$txt = str_replace( $find, $replace, $txt );
			
				$_c++;
			}
		
			$codes_seen = array();
			
			if ( count( $this->_sortedSmilies ) > 0 )
			{
				foreach( $this->_sortedSmilies as $row )
				{
					if ( is_array( $this->registry->output->skin ) and $this->registry->output->skin['set_emo_dir'] and $row['emo_set'] != $this->registry->output->skin['set_emo_dir'] )
					{
						continue;
					}
					
					$code = IPSText::UNhtmlspecialchars( $row['typed'] );
					
					if ( in_array( $code, $codes_seen ) )
					{
						continue;
					}
					
					$codes_seen[] = $code;
					
					// -----------------------------------------
					// Now, check for the html safe versions
					// -----------------------------------------
					
					$_emoCode = str_replace( '<', '&lt;', str_replace( '>', '&gt;', $code ) );
					$_emoImage = $row['image'];
					$emoPosition = 0;
					
					/* Cheap check */
					if ( ! stristr( $txt, $_emoCode ) )
					{
						continue;
					}
					
					// -----------------------------------------
					// These are chars that can't surround the emo
					// -----------------------------------------
					
					$invalidWrappers = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'\"/";
					
					// -----------------------------------------
					// Have any more chars to look at?
					// -----------------------------------------
					
					while ( ( $position = stripos( $txt, $_emoCode, $emoPosition ) ) !== false )
					{
						$lastOpenTagPosition = strrpos( substr( $txt, 0, $position ), '[' );
						$lastCloseTagPosition = strrpos( substr( $txt, 0, $position ), ']' );
						
						// -----------------------------------------
						// Are we at the start of the string, or
						// is the preceeding char not an invalid wrapper?
						// -----------------------------------------
						
						if ( ( $position === 0 or stripos( $invalidWrappers, substr( $txt, $position - 1, 1 ) ) === false )
	
						//-----------------------------------------
						// Are we inside a [tag]
						//-----------------------------------------
		
						AND ( ( $lastOpenTagPosition === FALSE || $lastCloseTagPosition === FALSE ) or ( $lastCloseTagPosition !== FALSE and $lastCloseTagPosition > $lastOpenTagPosition ) )
		
						//-----------------------------------------
						// Are we at the end of the string or is the
						// next char not an invalid wrapper?
						//-----------------------------------------
		
						AND ( strlen( $txt ) == ( $position + strlen( $_emoCode ) ) or stripos( $invalidWrappers, substr( $txt, ( $position + strlen( $_emoCode ) ), 1 ) ) === false ) )
						{
							// -----------------------------------------
							// Replace the emoticon and increment position
							// counter
							// -----------------------------------------
							
							$replace = $this->_retrieveSmiley( $_emoCode, $_emoImage );
							$txt = substr_replace( $txt, $replace, $position, strlen( $_emoCode ) );
							
							$position += strlen( $replace );
						}
						
						$emoPosition = $position + 1;
						
						if ( $emoPosition > strlen( $txt ) )
						{
							break;
						}
					}
				}
			}
			
			/* Put alt tags in */
			if ( is_array( $this->emoticon_alts ) && count( $this->emoticon_alts ) )
			{
				foreach( $this->emoticon_alts as $r )
				{
					$txt = str_replace( $r[0], $r[1], $txt );
				}
			}
			
			/* Convert code tags back... */
			while( preg_match( '/<!--Cj(\d+?)j-->/', $txt, $matches ) )
			{
				$find    = $matches[0];
				$replace = $_codeBlocks[ $matches[1] ];
								
				$txt = str_replace( $find, $replace, $txt );
			}
		}
	
		return $txt;
	}
	
	/**
	 * Remove quotes
	 * @param	string $txt
	 * @param	bool	If true, only quotes created by us will be stripped
	 */
	public function stripQuotes( $txt, $onlyStripIpsQuotes=true )
	{
		if ( stristr( $txt, '[quote' ) )
		{
			$txt = $this->stripBbcode( 'quote', $txt );
		}
		
		if ( stristr( $txt, '<blockquote' ) )
		{
			/* PRE: Fetch paired opening and closing tags */
			$data = $this->getTagPositions( $txt, 'blockquote', array( '<' , '>' ) );
			
			if ( is_array( $data['openWithTag'] ) )
			{
				foreach( $data['openWithTag'] as $id => $val )
				{
					if ( $onlyStripIpsQuotes )
					{
						$tag = substr( $txt, $data['openWithTag'][ $id ], ( $data['open'][ $id ] - $data['openWithTag'][ $id ] ) );
						if ( strpos( $tag, 'ipsBlockquote' ) === false )
						{
							continue;
						}
					}
				
					$o = $data['openWithTag'][ $id ];
					$c = $data['closeWithTag'][ $id ] - $o;
						
					$slice = substr( $txt, $o, $c );
						
					/* Need to bump up lengths of opening and closing */
					$_origLength = strlen( $slice );
						
					/* Remove */
					$slice = '';
			
					$_newLength  = strlen( $slice );
						
					$txt = substr_replace( $txt, $slice, $o, $c );
						
					/* Bump! */
					if ( $_newLength != $_origLength )
					{
						foreach( $data['openWithTag'] as $_id => $_val )
						{
							$_o = $data['openWithTag'][ $_id ];
								
							if ( $_o > $o )
							{
								$data['openWithTag'][ $_id ]  += ( $_newLength - $_origLength );
								$data['closeWithTag'][ $_id ] += ( $_newLength - $_origLength );
							}
						}
					}
				}
			}
		}
		
		return $txt;
	}
	
	/**
	 * Removes bbcode tag + contents within the tag
	 *
	 * @access public
	 * @param
	 *        	string		Tag to strip
	 * @param
	 *        	string		Raw text
	 * @return string text
	 */
	public function stripBbcode( $tag, $txt )
	{
		// -----------------------------------------
		// Protect against endless loops
		// -----------------------------------------
		static $iteration = array();
		
		if ( isset( $iteration[$tag] ) and $iteration[$tag] > $this->settings['max_bbcodes_per_post'] )
		{
			return $txt;
		}
		
		$iteration[$tag] = isset( $iteration[$tag] ) ? $iteration[$tag] ++ : 1;
		
		// Got Quotes (tm)? or any tag really
		if ( stripos( $txt, '[' . $tag ) !== false )
		{
			// -----------------------------------------
			// First grab start and end positions
			// -----------------------------------------
			
			$start_position = stripos( $txt, '[' . $tag );
			$end_position = stripos( $txt, '[/' . $tag . ']', $start_position );
			
			// -----------------------------------------
			// If no end position or start position,
			// we have a mismatched bbcode...return
			// -----------------------------------------
			
			if ( $start_position === false or $end_position === false )
			{
				return $txt;
			}
			
			// -----------------------------------------
			// Then extract the content inside the bbcode
			// -----------------------------------------
			
			$inner_content = substr( $txt, stripos( $txt, ']', $start_position ) + 1, $end_position - ( stripos( $txt, ']', $start_position ) + 1 ) );
			
			// -----------------------------------------
			// Is this bbcode nested in the inner content
			// -----------------------------------------
			
			$extra_closers = substr_count( $inner_content, '[' . $tag );
			
			// -----------------------------------------
			// If so we need to move to the last ending tag
			// -----------------------------------------
			
			if ( $extra_closers > 0 )
			{
				for( $done = 0 ; $done < $extra_closers ; $done ++ )
				{
					$end_position = stripos( $txt, '[/' . $tag . ']', $end_position + 1 );
				}
			}
			
			// -----------------------------------------
			// Get rid of the bbcode opening + content + closing
			// -----------------------------------------
			
			$txt = substr_replace( $txt, '', $start_position, $end_position - $start_position + strlen( '[/' . $tag . ']' ) );
			
			// -----------------------------------------
			// And parse recursively
			// -----------------------------------------
			
			return $this->stripBbcode( $tag, trim( $txt ) );
		}
		else
		{
			return $txt;
		}
	}
	
	/**
	 * Remove ALL tags
	 *
	 * @access public
	 * @param
	 *        	string		Raw text
	 * @param
	 *        	boolean		Whether or not to run through pre-edit-parse first
	 * @return string text
	 */
	public function stripAllTags( $txt )
	{
		$txt = $this->stripBbcode( 'quote', $txt );
		
		foreach( $this->cache->getCache( 'bbcode' ) as $bbcode )
		{
			$txt = preg_replace( "#\[{$bbcode['bbcode_tag']}\](.+?)\[/{$bbcode['bbcode_tag']}\]#is", "\\1 ", $txt );
			$txt = preg_replace( "#\[{$bbcode['bbcode_tag']}=([^\]]+?)\](.+?)\[/{$bbcode['bbcode_tag']}\]#is", "\\2 ", $txt );
			$txt = str_ireplace( "[{$bbcode['bbcode_tag']}]", '', $txt );
			$txt = str_ireplace( "[/{$bbcode['bbcode_tag']}]", '', $txt );
			
			// -----------------------------------------
			// Strip single bbcodes properly
			// -----------------------------------------
			
			if ( $bbcode['bbcode_single_tag'] )
			{
				$regex = $bbcode['bbcode_single_tag'];
				
				// -----------------------------------------
				// If this has option, adjust regex
				// -----------------------------------------
				
				if ( $bbcode['bbcode_useoption'] )
				{
					$regex .= '=([^\]]+?)';
				}
				
				$txt = preg_replace( "#\[{$regex}\]#is", " ", $txt );
			}
		}
		
		// $txt = preg_replace( "#\[(.+?)\]#is", " ", $txt );
		$txt = preg_replace( '#\[([^\]]+?)=([^\]]+?)\]#is', " ", $txt );
		$txt = preg_replace( '#\[/([^\]]+?)\]#is', " ", $txt );
		$txt = preg_replace( '#\[attachment=(.+?)\]#is', " ", $txt );
		$txt = str_replace( '[*]', '', $txt );
		
		return $txt;
	}
	
	/**
	 * Remove raw smilies
	 *
	 * @access public
	 * @param
	 *        	string		Raw text
	 * @return string with smiley codes removed
	 */
	public function stripEmoticons( $txt )
	{
		$codes_seen = array();
		
		if ( count( $this->cache->getCache( 'emoticons' ) ) > 0 )
		{
			foreach( $this->cache->getCache( 'emoticons' ) as $row )
			{
				if ( is_array( $this->registry->output->skin ) and $this->registry->output->skin['set_emo_dir'] and $row['emo_set'] != $this->registry->output->skin['set_emo_dir'] )
				{
					continue;
				}
				
				$code = $row['typed'];
				
				if ( in_array( $code, $codes_seen ) )
				{
					continue;
				}
				
				$codes_seen[] = $code;
				
				// -----------------------------------------
				// Now, check for the html safe versions
				// -----------------------------------------
				
				$_emoCode = str_replace( '<', '&lt;', str_replace( '>', '&gt;', $code ) );
				$_emoImage = $row['image'];
				$emoPosition = 0;
				$invalidWrappers = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
				
				/* Cheap check */
				if ( ! stristr( $txt, $_emoCode ) )
				{
					continue;
				}
				
				while ( ( $position = strpos( $txt, $_emoCode, $emoPosition ) ) !== false )
				{
					if ( strpos( $invalidWrappers, substr( $txt, $position - 1, 1 ) ) === false and strpos( $invalidWrappers, substr( $txt, ( $position + strlen( $_emoCode ) ), 1 ) ) === false )
					{
						$txt = substr_replace( $txt, '', $position, strlen( $_emoCode ) );
						
						$position += strlen( $_emoCode );
					}
					
					$emoPosition = $position + 1;
					
					if ( $emoPosition > strlen( $txt ) )
					{
						break;
					}
				}
			}
		}
		
		return $txt;
	}
	
	/**
	 * Strip shared media
	 *
	 * @param 	string			Raw posted text
	 * @return	string			Raw text with no shared media
	 */
	public function stripSharedMedia( $txt )
	{
		$txt	= preg_replace( '#\[sharedmedia=([^\]]+?)\]#is', " ", $txt );
	
		return $txt;
	}
	
	/**
	 * Strip images
	 * 
	 * @param	string
	 * @return	string
	 */
	public function stripImages( $txt )
	{
		$txt = preg_replace( '#<img([^>]+?)>#i', '', $txt );
		
		return $txt;
	}
	
	/**
	 * Convert IMG codes into text smilies
	 * 
	 * @param text $txt
	 * @return text $txt
	 */
	public function emoticonImgtoCode( $txt )
	{
		if ( count( $this->cache->getCache( 'emoticons' ) ) > 0 )
		{
			$emoDir = IPSText::getEmoticonDirectory();

			$txt    = str_replace( '<#EMO_DIR#>', $this->registry->output->skin['set_emo_dir'], $txt );
			
			foreach( $this->cache->getCache( 'emoticons' ) as $row )
			{
				if ( $row['emo_set'] != $emoDir )
				{
					continue;
				}

				/* This can shave a lot of loading time off of a site if there is a lot of large text being parsed, 
					especially if there are a lot of emoticons too */
				if( strpos( $txt, $row['image'] ) === false )
				{
					continue;
				}
				
				/* BBCode */
				$txt = preg_replace( '#(\s)?\[img\]' . preg_quote( $this->settings['public_cdn_url'] . 'style_emoticons/' . $this->registry->output->skin['set_emo_dir'] . '/' . $row['image'], '#' ) . '\[/img\]#', ' ' . $row['typed'], $txt );
				
				/* HTML */
				$txt = preg_replace( '#(\s)?<img([^>]+?)src=(?:[\'"])' . preg_quote( $this->settings['public_cdn_url'] . 'style_emoticons/' . $this->registry->output->skin['set_emo_dir'] . '/' . $row['image'], '#' ) . '(?:[\'"])(?:[^>]+?)?>#', ' ' . $row['typed'], $txt );
			}
		}
		
		return $txt;
	}
	
	/**
	 * Returns an array of tags this user is not allowed
	 * to use.
	 * @return array
	 */
	public function getDisabledTags()
	{
		$disabled    = array();
		$coreTags = array( "font", "size", "img", "url", "code", "quote", "color" );
		$bbcodeCache = $this->cache->getCache('bbcode');

		foreach( $coreTags as $tag )
		{
			if ( !in_array( $tag, array_keys( $bbcodeCache ) ) )
			{
				$disabled[] = $tag;
			}	
		}
		
		foreach( $bbcodeCache as $bbcode )
		{
			/* Allowed this BBCode? */
			if ( $bbcode['bbcode_sections'] != 'all' || $bbcode['bbcode_groups'] != 'all' )
			{			
				$sections	= explode( ',', $bbcode['bbcode_sections'] );
				$groups     = array_diff( explode( ',', $bbcode['bbcode_groups'] ), array( '' ) );
				$mygroups   = array( self::$Perms['memberData']['member_group_id'] );
				$group_pass       = false;
				$section_pass     = false;
				
				if ( self::$Perms['memberData']['mgroup_others'] )
				{
					$mygroups = array_diff( array_merge( $mygroups, explode( ',', IPSText::cleanPermString( self::$Perms['memberData']['mgroup_others'] ) ) ), array( '' ) );
				}
				
				/* Perms */
				if ( $bbcode['bbcode_groups'] != 'all' )
				{
					foreach( $groups as $g_id )
					{
						if ( in_array( $g_id, $mygroups ) )
						{
							$group_pass = true;
						}
					}
				}
				else
				{
					$group_pass = true;
				}
				
				/* Sections */
				if ( $bbcode['bbcode_sections'] != 'all' )
				{
					foreach( $sections as $section )
					{
						if ( $section == self::$Perms['parseArea'] )
						{
							$section_pass = true;						
						}
					}
				}
				else
				{
					$section_pass = true;
				}
				
				if ( $section_pass == false || $group_pass == false )
				{
					$disabled[] = $bbcode['bbcode_tag'];
				}
			}
		}
		
		return $disabled;
	}
	
	/**
	 * Return paired opening and closing positions.
	 * @param string $txt
	 * @param string $tag
	 * @return array
	 */
	public function getTagPositions( $txt, $tag, $brackets=array('[',']') )
	{
		$close_tag = $brackets[0] . '/' . $tag . $brackets[1];
		$open_tag  = $brackets[0] . $tag;
		$map      = array();
		$iteration = 0;
	
		/* Pick through bit of code */
		while( ( $curPos = stripos( $txt, $open_tag, $curPos ) ) !== false )
		{
			if ( $iteration > 1000 )
			{
				break;
			}
			
			/* Make sure the next character is either ] = or a space */
			$nextChar = substr( $txt, $curPos + strlen( $tag ) + 1, 1 );
				
			if ( $nextChar != $brackets[1] && $nextChar != '=' && $nextChar != ' ' )
			{
				if ( $curPos > strlen($txt) )
				{
					$curPos	= 0;
					break;
				}

				$curPos++;
				continue;
			}
			
			$map['openWithTag'][ $iteration ] = $curPos;
			$map['open'][ $iteration ]        = $curPos + strlen( $open_tag );
				
			$new_pos = stripos( $txt, $brackets[1], $curPos ) ? stripos( $txt, $brackets[1], $curPos ) : $curPos + 1;
				
			/* Got an option, grab that */
			$_option = substr( $txt, $curPos + strlen($open_tag), (stripos( $txt, $brackets[1], $curPos ) - ($curPos + strlen($open_tag) )) );
			
			$map['open'][ $iteration ] += intval( strlen( $_option ) ) + 1;
			
			/* Got a closing tag? */
			$closingTagPos = stripos( $txt, $close_tag, $new_pos );
				
			if ( $closingTagPos !== false )
			{
				$map['close'][ $iteration ]        = $closingTagPos;
				$map['closeWithTag'][ $iteration ] = $closingTagPos + strlen( $close_tag );
				
				/* What content do we believe we have between the opening and closing tags? */
				$_content  = substr( $txt, ($curPos + strlen( $open_tag )  + strlen($_option) + 1), ($closingTagPos - ($curPos + strlen($open_tag) + strlen($_option) + 1)) );

				/* Did we have an opening tag in that mess? */
				if ( $_content && stristr( $_content, $open_tag ) )
				{
					/* How many opening tags did we find...probably just 1 */
					$count = substr_count( strtolower( $_content ), strtolower( $open_tag) );

					/* Found N opening tags in portion of text */
					if ( $count > 0 )
					{
						/* So now find Nth closing tag */
						$_nPos = $closingTagPos + strlen( $close_tag );

						/* While we have opening tags to inspect... */
						while( $count > 0 )
						{
							$_closePos = stripos( $txt, $close_tag, $_nPos );
								
							if ( $_closePos !== false )
							{
								$map['close'][ $iteration ]        = $_closePos;
								$map['closeWithTag'][ $iteration ] = $_closePos + strlen( $close_tag );

								$_content	= substr( $txt, ($curPos + strlen( $open_tag )  + strlen($_option) + 1), ($_closePos - ($curPos + strlen($open_tag) + strlen($_option) + 1)) );
								$count		= substr_count( strtolower( $_content ), strtolower( $open_tag ) );
								$ccount		= substr_count( strtolower( $_content ), strtolower( $close_tag ) );

								if( $count == $ccount )
								{
									$count	= 0;
								}

								$_nPos = $_closePos + strlen( $close_tag );
	
								if ( $_nPos >= strlen( $txt ) )
								{
									$count == 0;
								}
							}
							else
							{
								$count	= 0;
							}
						}
					}
				}
			}
				
			$iteration++;
				
			$curPos = $closingTagPos ? $closingTagPos : $curPos + 1;
	
			if ( $curPos > strlen($txt) )
			{
				$curPos	= 0;
				break;
			}
		}
	
		return $map;
	}
	
	/**
	 * Build a quote tag
	 * @param string $content
	 * @param string $author
	 * @param string $date
	 * @param int $pid
	 */
	public function buildQuoteTag( $content, $author='', $date='', $collapsed=0, $pid=0 )
	{	
		$ops = array();
		
		if ( $author )
		{
			$ops[] = 'data-author="' . $author . '"';
		}
		
		if ( $pid )
		{
			$ops[] = 'data-cid="' . $pid . '"';
		}
		
		if ( $date )
		{
			if ( strlen( $date ) == 10 && intval( $date ) == $date )
			{
				$ops[] = 'data-time="' . $date . '"';
			}
			else
			{
				$ops[] = 'data-date="' . $date . '"';
			}
		}
		
		if ( $collapsed )
		{
			$ops[] = 'data-collapsed="' . $collapsed . '"';
		}
		
		/* Parse out attachments and make into links */
		preg_match_all( '#\[attachment=(.+?):(.+?)\]#', $content, $_matches );
	
		if( is_array( $_matches[1] ) && count( $_matches[1] ) )
		{
			foreach( $_matches[1] as $idx => $attach_id )
			{
				$content = str_replace( "[attachment={$attach_id}:{$_matches[2][$idx]}]", $this->registry->getClass('output')->getReplacement('post_attach_link') . " <a href='{$this->settings['board_url']}/index.php?app=core&amp;module=attach&amp;section=attach&amp;attach_rel_module=post&amp;attach_id={$attach_id}' target='_blank'>{$_matches[2][$idx]}</a>", $content );
			}
		}
		
		/* Convert if we need to */
		if ( $this->isBBCode( $content ) )
		{
			$content = $this->BBCodeToHtml( $content );
		}
		
		/* ARGH MY EYES ARGH MY EYES SOMEONE DID A BAD WORD */
		$content = $this->parseBadWords( $content );

		/* We need the wrapping <div> here - _stripParagraphWrap removes the first and last <p> tags if they exist, but that means content like this remains unchanged:
			<p>something</p>
			<br />
			<p>something else</p>
			and there's no wrapper container, which is necessary in the editor for our javascript.  This fixes these reports:
			@link http://community.invisionpower.com/resources/bugs.html/_/ip-board/cannot-go-down-using-keyboard-arrow-in-some-cases-r41735
			@link http://community.invisionpower.com/resources/bugs.html/_/ip-board/cite-in-firefox-problematic-r41737
			@link http://community.invisionpower.com/resources/bugs.html/_/ip-board/quote-inappropriately-split-r41736
			@link http://community.invisionpower.com/resources/bugs.html/_/ip-board/two-lines-sometimes-still-removed-r41738
			@link http://community.invisionpower.com/resources/bugs.html/_/ip-board/cant-make-new-line-in-editor-r41722 */
		return "<p>&nbsp;</p><blockquote class='ipsBlockquote'" . implode( ' ', $ops ) . '><div><p>' . $this->_stripParagraphWrap( $content ) . '</p></div></blockquote><p>&nbsp;</p>';
	}
	
	/**
	 * Parses the bbcode to be shown in the polls.
	 * Parses img and url, if enabled
	 *
	 * @param 	string			Raw input text to parse
	 * @return	string			Parsed text ready to be displayed
	 */
	public function parsePollTags( $text )
	{
		if ( stristr( $text, '[img' ) || stristr( $text, '[url' ) || stristr( $text, '[sharedmedia' ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/text/parser/bbcode.php', 'class_text_parser_bbcode' );
			$bbcode = new $classToLoad();
			
			$text = $this->display( $bbcode->_parseBBCode( $text, 'display', array( 'img', 'url', 'sharedmedia' ) ) );
		}
		
		return $text;
	}
	
	/**
	 * To fix legacy issues, all HTML entities are parsed into HTML tags
	 * So this function restores code boxes
	 * @param 	string 	$html
	 * @param	string	$tag
	 * @param	array	$brackets
	 */
	public function HtmlAllowedPreContents( $html, $tag, $brackets )
	{ 
		/* PRE: Fetch paired opening and closing tags */
		$data = $this->getTagPositions( $html, $tag, $brackets );
		
		if ( is_array( $data['open'] ) )
		{
			$count = count( $data['open'] );
			
			foreach( range( 0, $count ) as $id )
			{
				$o = $data['open'][ $id ] ;
				$c = $data['close'][ $id ] - $o;
					
				$slice = substr( $html, $o, $c );
				
				/* Need to bump up lengths of opening and closing */
				$_origLength = strlen( $slice );
				
				/* Fix up <p>[code]</p>....<p>[/code]</p> */
				$slice = preg_replace( '#^</p>#', '', $slice );
				$slice = trim( preg_replace( '#<p>$#', '', trim( $slice ) ) );
				
				/* Extra conversion for BBCODE>HTML mode */
				$slice = str_replace( "<", "&lt;", $slice );
				$slice = str_replace( ">", "&gt;", $slice );
	
				$_newLength  = strlen( $slice );
					
				$html = substr_replace( $html, $slice, $o, $c );
					
				/* Bump! */
				if ( $_newLength != $_origLength )
				{
					foreach( $data['open'] as $_id => $_val )
					{
						$_o = $data['open'][ $_id ];
							
						if ( $_o > $o )
						{
							$data['open'][ $_id ]  += ( $_newLength - $_origLength );
							$data['close'][ $_id ] += ( $_newLength - $_origLength );
						}
					}
				}
			}
		}
		
		return $html;
	}
	
	/**
	 * Get attributes from a tag (lazy)
	 * @param string $tag
	 */
	public function getTagAttributes( $tag )
	{
		$processedTag = str_replace( array( '&quot;' ), '"', $tag );
		$processedTag = str_replace( array( '&#039;', '&#39;' ), '\'', $processedTag );
		
		$processedTag = str_replace( '&nbsp;', ' ', $processedTag );
		$attributes   = array();
		
		/* http://community.invisionpower.com/resources/bugs.html/_/ip-board/apostrophe-in-rte-in-quote-name-is-cut-off-r41322 */
		preg_match_all( '#(\S+?)=(?:[\'"])(.+?)(?:[\'"])(\s|>|\]$)#', $processedTag, $matches, PREG_SET_ORDER );
	
		foreach( $matches as $val )
		{
			$attributes[ trim( $val[1] ) ] = $val[2];
		}
		
		return $attributes;
	}
	
	/**
	 * Expand the acronyms for SEO
	 * @param string $txt
	 */
	protected function _seoAcronymExpansion( $txt )
	{
		if ( $txt == '' )
		{
			return $txt;
		}

		$acronyms = $this->cache->getCache('ipseo_acronyms');

		if( !is_array($acronyms) OR !count($acronyms) )
		{
			return $txt;
		}

		$temp_text = $txt;
		$urls      = array();
		$tags      = array();
		$txt       = str_replace( '<#EMO_DIR#>', '-#-#-#EMO_DIR#-#-#-', $txt );
		
		/* Grab images */
		preg_match_all( '#<img([^>]+?)>#i', $txt, $matches );
		
		foreach( $matches[0] as $m )
		{
			$c = count( $urls );
			$urls[ $c ] = $m;
		
			$txt = str_replace( $m, '<!--url{' . $c . '}-->', $txt );
		}
		
		/* Grab <a> */
		preg_match_all( '#<a([^>]+?)>#i', $txt, $matches );
		
		foreach( $matches[0] as $m )
		{
			$c = count( $urls );
			$urls[ $c ] = $m;
		
			$txt = str_replace( $m, '<!--url{' . $c . '}-->', $txt );
		}
		
		/* Grab all other tags */
		preg_match_all( '#<(?:[/a-z]{1,})([^>]+?)>#i', $txt, $matches );
		
		foreach( $matches[0] as $m )
		{
			$c = count( $tags );
			$tags[ $c ] = $m;
		
			$txt = str_replace( $m, '<!--tag{' . $c . '}-->', $txt );
		}
		
		/* Grab non linked URLs */
		preg_match_all( '#((http|https|news|ftp)://(?:[^<>\)\[\"\s]+|[a-zA-Z0-9/\._\-!&\#;,%\+\?:=]+))#is', $txt, $matches );
		
		foreach( $matches[0] as $m )
		{
			$c = count( $urls );
			$urls[ $c ] = $m;
		
			$txt = str_replace( $m, '<!--url{' . $c . '}-->', $txt );
		}
		
		//-----------------------------------------
		// Convert back entities
		//-----------------------------------------
		
		for( $i = 65; $i <= 90; $i++ )
		{
			$txt = str_replace( "&#" . $i . ";", chr($i), $txt );
		}
		
		for( $i = 97; $i <= 122; $i++ )
		{
			$txt = str_replace( "&#" . $i . ";", chr($i), $txt );
		}		
		
		//-----------------------------------------
		// Go all loopy
		//-----------------------------------------

		if ( is_array($acronyms) && count($acronyms) )
		{
			foreach( $acronyms as $r )
			{
				$this->_currentAcronym = $r;
				
				/*																								vv Ticket #835804 */
				$wordModifier	= ( IPS_DOC_CHAR_SET == 'UTF-8' && IPSText::isUTF8( $txt ) ) ? '[^<>\p{L}]|\b' : '[^<>a-zA-Z0-9-_&;]';
				$caseModifier	= empty($r['a_casesensitive']) ? 'i' : '';
				$r['a_short']	= preg_quote( $r['a_short'], "/" );

				$txt			= preg_replace_callback( '/(^|\b|\W)(' . $r['a_short'] . ')(\b|\W|$)/' . $caseModifier, array( $this, '_replaceAcronym' ), $txt );
			}
		}

		/* replace urls */
		if ( count( $urls ) )
		{
			foreach( $urls as $k => $v )
			{
				$txt = str_replace( "<!--url{" . $k . "}-->", $v, $txt );
			}
		}
		
		/* replace tags */
		if ( count( $tags ) )
		{
			foreach( $tags as $k => $v )
			{
				$txt = str_replace( "<!--tag{" . $k . "}-->", $v, $txt );
			}
		}
		
		$txt = str_replace( '-#-#-#EMO_DIR#-#-#-', '<#EMO_DIR#>', $txt );
		
		return $txt ? $txt : $temp_text;
	}
	
	/**
	 * Callback function to replace a found acronym
	 *
	 * @param	array		$matches		Array of matches
	 * @return	@e string	Replaced text
	 */
	private function _replaceAcronym( $matches=array() )
	{
		return $this->_currentAcronym['a_semantic'] ? "{$matches[1]}<acronym title='{$this->_currentAcronym['a_long']}' class='bbc ipSeoAcronym'>{$matches[2]}</acronym>{$matches[3]}" : $matches[1] . $this->_currentAcronym['a_long'] . $matches[3];
	}
	
	/**
	 * Strip paragraph wrap tags
	 * @param string $txt
	 * @return string
	 */
	protected function _stripParagraphWrap( $txt )
	{
		$txt = trim( $txt );

		/* Clean up */
		$txt = preg_replace( '#^(<br([^>]+?)?>){1,}#i', '', $txt );
		
		$txt = trim( $this->_stripEmptyLeadingAndTrailingParagraphTags( $txt ) );
	
		if ( substr( $txt, 0, 3 ) == '<p>' && substr( $txt, -4 ) == '</p>' )
		{
			$txt = substr( $txt, 3, -4 );
		}
		
		$txt = trim( $txt );
		
		return $txt;
	}
	
	/**
	 * Strips off blank or empty P tags
	 * @param string $txt
	 * @return string
	 */
	protected function _stripEmptyLeadingAndTrailingParagraphTags( $txt )
	{
		/* Strip leading Ps */
		while( preg_match( '#^<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>#i', $txt, $match ) )
		{
			$txt = trim( preg_replace( '#^<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>#i', '', $txt ) );
		}
		
		/* Strip trailing Ps */
		while( preg_match( '#<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>$#i', $txt, $match ) )
		{
			$txt = trim( preg_replace( '#<p([^>]+?)?' . '>((&nbsp;|\s)+?)?</p>$#i', '', $txt ) );
		}
		
		/* Strip trailing <br /> */
		while( preg_match( '#<br([^>]+?)?' . '/>((&nbsp;|\s)+?)?$#i', $txt, $match ) )
		{
			$txt = trim( preg_replace( '#<br([^>]+?)?' . '/>((&nbsp;|\s)+?)?$#i', '', $txt ) );
		}
		
		/* Strip trailing <div /> */
		while( preg_match( '#<div([^>]+?)?' . '>((&nbsp;|\s)+?)?</div>$#i', $txt, $match ) )
		{
			$txt = trim( preg_replace( '#<div([^>]+?)?' . '>((&nbsp;|\s)+?)?</div>$#i', '', $txt ) );
		}
		
		return $txt;
	}
	
	/**
	 * Check and make safe embedded codes
	 * @param array $matches
	 */
	protected function _preserveCodeBoxes( $txt )
	{
		$map = array();

		/* CODE: Fetch paired opening and closing tags */
		$data = $this->getTagPositions( $txt, 'code', array( '[' , ']' ) );
	
		if ( is_array( $data['open'] ) )
		{
			foreach( $data['open'] as $id => $val )
			{
				$o = $data['open'][ $id ];
				$c = $data['close'][ $id ] - $o;
				
				/* Prevent unclosed tags from breaking this */
				if ( $o < 1 || $c < 1 )
				{
					continue;
				}
				
				$slice = substr( $txt, $o, $c );
	
				/* Need to bump up lengths of opening and closing */
				$_origLength = strlen( $slice );
	
				/* Extra conversion for BBCODE>HTML mode */
				$slice = str_replace( "[", "&#91;", $slice );
				$slice = preg_replace( '#(https|http|ftp)://#' , '\1&#58;//', $slice );
				#$slice = str_replace( "]", "&#93;", $slice );
				$slice = str_replace( "\n", "<!-preserve.newline-->", $slice );
				
				/* Stop (r) (tm) and (c) from switching out */
				$slice = preg_replace( '#\((tm|r|c)\)#i', '&#40;$1&#41;', $slice );
				
				$_newLength  = strlen( $slice );
	
				$txt = substr_replace( $txt, $slice, $o, $c );
	
				/* Bump! */
				if ( $_newLength != $_origLength )
				{
					foreach( $data['open'] as $_id => $_val )
					{
						$_o = $data['open'][ $_id ];
							
						if ( $_o > $o )
						{
							$data['open'][ $_id ]  += ( $_newLength - $_origLength );
							$data['close'][ $_id ] += ( $_newLength - $_origLength );
						}
					}
				}
			}
		}
		
		/* PRE: Fetch paired opening and closing tags */
		$data = $this->getTagPositions( $txt, 'pre', array( '<' , '>' ) );
	
		if ( is_array( $data['open'] ) )
		{
			foreach( $data['open'] as $id => $val )
			{
				$o = $data['open'][ $id ] ;
				$c = $data['close'][ $id ] - $o;
					
				$slice = substr( $txt, $o, $c );
					
				/* Need to bump up lengths of opening and closing */
				$_origLength = strlen( $slice );
					
				/* Extra conversion for BBCODE>HTML mode */
				$slice = str_replace( "[", "&#91;", $slice );
				$slice = str_replace( "]", "&#93;", $slice );
				$slice = preg_replace( '#(https|http|ftp)://#' , '\1&#58;//', $slice );
				$slice = str_replace( "\n", "<!-preserve.newline-->", $slice );
				
				/* Stop (r) (tm) and (c) from switching out */
				$slice = preg_replace( '#\((tm|r|c)\)#i', '&#40;$1&#41;', $slice );
				
				$_newLength  = strlen( $slice );
					
				$txt = substr_replace( $txt, $slice, $o, $c );
					
				/* Bump! */
				if ( $_newLength != $_origLength )
				{
					foreach( $data['open'] as $_id => $_val )
					{
						$_o = $data['open'][ $_id ] + strlen( '<pre' );
							
						if ( $_o > $o )
						{
							$data['open'][ $_id ]  += ( $_newLength - $_origLength );
							$data['close'][ $_id ] += ( $_newLength - $_origLength );
						}
					}
				}
			}
		}
		
		return $txt;
	}
	
	/**
	 * Sort the smilies nicely in order of length.
	 */
	protected function _sortSmilies()
	{
		$emoticons = array();
	
		if ( ! count( $this->_sortedSmilies ) )
		{
			/* Sort them! */
			$this->_sortedSmilies = $this->cache->getCache('emoticons');
				
			usort( $this->_sortedSmilies, array( $this, '_thisUsort' ) );
		}
	}
	
	/**
	 * Custom sort operation
	 *
	 * @param	string		A
	 * @param	string		B
	 * @return	integer
	 */
	protected static function _thisUsort($a, $b)
	{
		if ( strlen($a['typed']) == strlen($b['typed']) )
		{
			return 0;
		}
	
		return ( strlen($a['typed']) > strlen($b['typed']) ) ? -1 : 1;
	}
	
	/**
	 * Add to errors
	 * @param string $error
	 */
	protected function _addParsingError( $error )
	{
		if ( $error && is_string( $error ) )
		{
			$this->_errors[] = $error;
		
			/* Legacy @todo remove in 4 */
			$this->error = $error;
		}
	}
	
	/**
	 * Retrieve the proper emoticon image code
	 *
	 * @access	protected
	 * @param	string		Emoticon code we are replacing (i.e. :D)
	 * @param	string		Emoticon image to display (i.e. 'biggrin.png')
	 * @return	string		Converted text
	 */
	protected function _retrieveSmiley( $_emoCode, $_emoImage )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
	
		if ( ! $_emoCode or ! $_emoImage )
		{
			return '';
		}
	
		$this->emoticon_count++;
	
		$this->emoticon_alts[] = array( "#EMO_ALT_{$this->emoticon_count}#", $_emoCode );
	
		//-----------------------------------------
		// Return
		//-----------------------------------------
	
		return "<img src='" . $this->settings['emoticons_url'] . "/{$_emoImage}' class='bbc_emoticon' alt='#EMO_ALT_{$this->emoticon_count}#' />";
	}
	
	/**
	 * Prevent auto parsing codes
	 */
	protected function _processNoParseCodes( $content, $passLevel=1, $allowCodeTag=false )
	{
		if ( $passLevel == 3 )
		{
			/* At this point bbcode has been converted... */
			return preg_replace( '#(https|http|ftp)-~~-//#', '\1://', $content );
		}
		
		$noParse = array();
	
		/* Find no parse codes */
		foreach( $this->cache->getCache('bbcode') as $bbcode )
		{
			/* Allowed this BBCode? */
			if ( $bbcode['bbcode_no_parsing'] )
			{
				/* CODE is a special case */
				if ( $allowCodeTag === false && $bbcode['bbcode_tag'] == 'code' )
				{
					continue;
				}
				
				$noParse[ $bbcode['bbcode_tag'] ] = $bbcode['bbcode_tag'];
							
				if ( $bbcode['bbcode_aliases'] )
				{
					$tmp = explode( ',', $bbcode['bbcode_aliases'] );
					
					foreach( $tmp as $bc )
					{
						if ( trim( $bc ) )
						{
							$noParse[ $bc ] = $bc;
						}
					}
				}
			}
		}
	
		/* Got anything? */
		if ( ! count( $noParse ) )
		{
			return $content;
		}
		
		/* Sort by key length so that IMGTEST parses before IMG, for example */
		uksort( $noParse, create_function('$a,$b', 'return strlen($a) < strlen($b);') );
		
		foreach( $noParse as $tag )
		{
			if( $tag == 'img' )
			{
				continue;
			}

			/* Fetch paired opening and closing tags */
			$data = $this->getTagPositions( $content, $tag, array( '[', ']') );

			if ( is_array( $data ) && count( $data ) )
			{
				foreach( $data['open'] as $id => $val )
				{
					$o = $data['open'][ $id ];
					$c = $data['close'][ $id ] - $o;
					
					/* Prevent unclosed tags from breaking this */
					if ( $o < 1 || $c < 1 )
					{
						continue;
					}
					
					$slice = substr( $content, $o, $c );
	
					$_origLen = strlen( $slice );
						
					if ( $passLevel == 2 )
					{					
						//* No linkify */
						preg_match_all( '#<a(?:[^>]+?)href=["\']([^"\']+?)["\']([^>]+?)?>(.+?)(</a>|$)#is', $slice, $urlMatches );
				
						/* Finish up URLs and such */
						for( $i = 0 ; $i < count( $urlMatches[0] ) ; $i++ )
						{
							$raw  = $urlMatches[0][ $i ];
							$url  = $urlMatches[1][ $i ];
							$text = $urlMatches[3][ $i ];
							
							/* Some posts end up with the closing tag as part of the URL */
							$url  = str_replace( '%5B/' . $tag . '%5D', '', $url );
							
							$slice = str_replace( $raw, preg_replace( '#(https|http|ftp)://#' , '\1-~~-//', $url ), $slice );
						}
					}
					else
					{
						$slice = preg_replace( '/\[/', '&#91;', $slice );
						$slice = preg_replace( '/\/(\w+?)\]/', '/\1&#93;', $slice );
						$slice = preg_replace( '#\((r|tm|c)\)#', '&#40;\1&#41;', $slice );
						$slice = str_replace( "{parse", "&#123;parse", $slice );
						$slice = preg_replace( '#(https|http|ftp)://#' , '\1-~~-//', $slice );
					}
						
					$_newLen  = strlen( $slice );
	
					if ( $_newLen != $_origLen )
					{
						/* Bump up next */
						foreach( $data['open'] as $_id => $_val )
						{
							$_o = $data['open'][ $_id ];
	
							/* Ascii face alert */
							if ( $_o > $o )
							{
								$data['open'][ $_id ]  += ( $_newLen - $_origLen );
								$data['close'][ $_id ] += ( $_newLen - $_origLen );
							}
						}
					}
	
					$content = substr_replace( $content, $slice, $o, $c );
				}
			}
		}
	
		return $content;
	}
	
}
