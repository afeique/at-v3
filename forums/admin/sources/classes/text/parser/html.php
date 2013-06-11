<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Text Parsing: HTML
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
 * Revised in 3.4 by Matt Mecham
 * Refactored into a text parsing module
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_text_parser_html extends classes_text_parser
{
	/**
	 * Parsing array
	 *
	 * @access	public
	 * @var		array
	 */
	public $_nonDelimiters		= array( "=", ' ' );
	
	/**
	 * Parsing array
	 *
	 * @access	public
	 * @var		array
	 */
	public $_delimiters = array( "'", '"' );
	
	/**
	 * Main font sizes
	 *
	 * @access	protected
	 * @var		array
	 */
	private $_fontSizes =  array(   1 => 8,
							        2 => 10,
									3 => 12,
									4 => 14,
									5 => 18,
									6 => 24,
									7 => 36,
									8 => 48 );
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object		Registry object
	 * @return	@e void
	 */
	public function __construct()
	{
		return parent::__construct();
	}
	
	/**
	 * Takes HTML from CKEditor and makes it suitable for IPB display
	 * @param string $html
	 * @return	string
	 */
	public function toDisplay( $html )
	{
		/* Convert _prettyXprint _lang- _linenums */
		preg_match_all( '#<pre\s+?class=[\'"]([^\'"]+?)[\'"]#i', $html, $matches );
		
		foreach( $matches[1] as $id => $match )
		{
			$txt = $matches[0][$id];
			
			$txt = str_replace( '_prettyXprint', 'prettyprint', $txt );
			$txt = str_replace( '_lang-'       , 'lang-'      , $txt );
			$txt = str_replace( '_linenums'    , 'linenums'   , $txt );
			
			$html = str_replace( $matches[0][$id], $txt, $html );
		}
		
		/* ensure emo dir is converted over (legacy posts) */
		if ( strstr( $html, '<#EMO_DIR#>' ) )
		{
			$html = str_replace( '<#EMO_DIR#>', $this->registry->getClass('output')->skin['set_emo_dir'], $html );
		}
		
		/* Add lightbox rel tag. Emoticons should be code at this point */
		if ( ! parent::$Perms['parseHtml'] )
		{
			$html = preg_replace( "/(?!<span rel='lightbox'>)<img(?!.*sharedmedia_screenshot)([^>]+?)>/i", "<span rel='lightbox'><img class='bbc_img'\\1></span>", $html );
			$html = preg_replace( "/<img class='bbc_img'([^>]+?)class='bbc_img'([^>]+?)>/i", "<img class='bbc_img'\\1\\2>", $html );
		}
		
		return $html;
	}
	
	/**
	 * Convert to BBCode
	 * @param	string
	 * @return	string
	 */
	public function toBBCode( $content )
	{
		/* Anything to parse? */
		if ( ! $content )
		{
			return $content;
		}
		
		$ot = $content;
		
		$content = str_replace( array( "\r\n", "\n" ), "\n", $content );
		
		/* Tidy up first */
		$content = str_replace( '<div', '<p', $content );
		$content = str_replace( '</div>', '</p>', $content );
		$content = preg_replace( '#<p>(\s+?)?<ul#is', '<ul', $content );
		$content = preg_replace( '#(\r\n|\r|\n)<p#is', '<p', $content );
		$content = preg_replace( '#</ul>(\s+?)?(<br([^>]+?)?>|</p>)#is', '</ul>', $content );
		
		/* Before we content/strip newlines, lets make code safe */
		$content = $this->_recurseAndParse( 'pre', $content, "_parsePreTag" );
		
		$content = str_replace( '&nbsp;&nbsp;&nbsp;&nbsp;', "{'tab'}", $content );
			
		$content = str_replace( "\t", "{'tab'}", $content );
		$content = str_replace( '&nbsp;', ' ', $content );
			
		/* looks to be non RTE content that has ended up there */
		if ( ! strstr( $content, '<br' ) && ! strstr( $content, '<p' ) && strstr( $content, "\n" ) )
		{
			$content = str_replace( "\n", "<br />", $content );
		}
		else
		{
			$content = str_replace( "\n", "", $content );
		}
		
		/* Restore preserved newlines */
		$content = str_replace( "<!-preserve.newline-->", "\n", $content );
		
		// -----------------------------------------
		// Clean up already encoded HTML
		// -----------------------------------------
		
		$content = str_replace( '&quot;', '"', $content );
		$content = str_replace( '&apos;', "'", $content );
		
		// -----------------------------------------
		// Fix up incorrectly nested urls / BBcode
		// -----------------------------------------
		
		// @link
		// http://community.invisionpower.com/tracker/issue-24704-pasting-content-in-rte-with-image-first/
		// Revert the fix for now as it causes more issues than the original one
		$content = preg_replace( '#<a\s+?href=[\'"]([^>]+?)\[(.+?)[\'"](.+?)' . '>(.+?)\[\\2</a>#is', '<a href="\\1"\\3>\\4</a>[\\2', $content );
		// $content = preg_replace(
		// '#<a\s+?href=[\'"]([^>\'"]+?)[\'"](.*?)>(.+?)\[([^<]+?)</a>#is', '<a
		// href="\\1">\\3</a>[\\4', $content );
		
		// -----------------------------------------
		// Make URLs safe (prevent tag stripping)
		// -----------------------------------------
		
		$content = preg_replace_callback( '#<(a href|img src)=([\'"])([^>]+?)(\\2)#is', array( $this, '_unhtmlUrl' ), $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #1: BR tags to \n
		// -----------------------------------------
		
		$content = preg_replace( '#<br([^>]+?)>#', "<br />", $content );
		$content = str_ireplace( array( "<br>", "<br />" ), "\n", $content );	
		
		$content = trim( $content );
		
		// -----------------------------------------
		// Before we can use strip_tags, we should
		// clean out any javascript and CSS
		// -----------------------------------------
		
		$content = preg_replace( '/\<script(.*?)\>(.*?)\<\/script\>/', '', $content );
		$content = preg_replace( '/\<style(.*?)\>(.*?)\<\/style\>/', '', $content );
		
		// -----------------------------------------
		// Remove tags we're not bothering with
		// with PHPs wonderful strip tags func
		// -----------------------------------------
		
		$content = strip_tags( $content, '<h1><h2><h3><h4><h5><h6><font><span><div><br><p><img><a><li><ol><ul><b><strong><em><i><u><s><strike><del><blockquote><sub><sup><pre>' );	
		
		// -----------------------------------------
		// WYSI-Weirdness #2: named anchors
		// -----------------------------------------
		
		$content = preg_replace( '#<a\s+?name=.+?' . '>(.+?)</a>#is', "\\1", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #2.1: Empty a hrefs
		// -----------------------------------------
		
		$content = preg_replace( '#<a\s+?href([^>]+)></a>#is', "", $content );
		$content = preg_replace( '#<a\s+?href=([\'\"])>\\1(.+?)</a>#is', "\\1", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #2.2: Double linked links
		// -----------------------------------------
		
		$content = preg_replace( '#href=[\"\']\w+://(%27|\'|\"|&quot;)(.+?)\\1[\"\']#is', "href=\"\\2\"", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #3: Headline tags
		// -----------------------------------------
		
		$content = preg_replace( "#<(h[0-9])(?:[^>]+?)?>(.+?)</\\1>#is", "\n[b]\\2[/b]\n", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #4: Font tags
		// -----------------------------------------
		
		$content = preg_replace( '#<font (color|size|face)=\"([a-zA-Z0-9\s\#\-]*?)\">(\s*)</font>#is', " ", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #5a: Fix up smilies: IE RTE
		// @see Ticket 623146
		// -----------------------------------------
		
		$content = preg_replace( '#<img class=(\S+?) alt=(\S+?) src=[\"\'](.+?)[\"\']>#i', "<img src='\\3' class='\\1' alt='\\2' />", $content );
		$content = preg_replace( '#alt=\'[\"\'](\S+?)[\'\"]\'#i', "alt='\\1'", $content );
		$content = preg_replace( '#class=\'[\"\'](\S+?)[\'\"]\'#i', "class='\\1'", $content );
		$content = preg_replace( '#([a-zA-Z0-9])<img src=[\"\'](.+?)[\"\'] class=[\"\'](.+?)[\"\'] alt=[\"\'](.+?)[\"\'] />#i', "\\1 <img src='\\2' class='\\3' alt='\\4' />", $content );
		
		/* Remove <img src="data:"> */
		$content = preg_replace( '#<img\s+?(alt=""\s+?)?src="data:([^"]+?)"\s+?/>#', '', $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #6: Image tags
		// -----------------------------------------
		
		$content = preg_replace( '#<img alt=[\"\'][\"\'] height=[\"\']\d+?[\"\'] width=[\"\']\d+?[\"\']\s+?/>#', "", $content );
		$content = preg_replace( '#<img.+?src=[\"\'](.+?)[\"\']([^>]+?)?' . '>#is', "[img]\\1[/img]", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #7: Linked URL tags
		// -----------------------------------------
		
		$content = preg_replace( '#\[url=(\"|\'|&quot;)<a\s+?href=[\"\'](.*)/??[\'\"]\\2/??</a>#is', "[url=\\1\\2", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #8: Make relative images full links
		// -----------------------------------------
		
		$content = preg_replace( '#\[img\](/)?public/style_(emoticons|images)#i', '[img]' . $this->settings['board_url'] . '/public/style_' . '\\2', $content );
		
		// -----------------------------------------
		// Clean up whitespace between lists
		// -----------------------------------------
		
		$content = preg_replace( '#<li>\s+?(\S)#', '<li>\\1', $content );
		$content = preg_replace( '#</li>\s+?(\S)#', '</li>\\1', $content );
		$content = preg_replace( '#<br />(\s+?)?</li>#si', '</li>', $content );
		
		// -----------------------------------------
		// Now, recursively parse the other tags
		// to make sure we get the nested ones
		// -----------------------------------------
		
		$content = $this->_recurseAndParse( 'b', $content, "_parseSimpleTag", 'b' );
		$content = $this->_recurseAndParse( 'u', $content, "_parseSimpleTag", 'u' );
		$content = $this->_recurseAndParse( 'strong', $content, "_parseSimpleTag", 'b' );
		$content = $this->_recurseAndParse( 'i', $content, "_parseSimpleTag", 'i' );
		$content = $this->_recurseAndParse( 'em', $content, "_parseSimpleTag", 'i' );
		$content = $this->_recurseAndParse( 'strike', $content, "_parseSimpleTag", 's' );
		$content = $this->_recurseAndParse( 'del', $content, "_parseSimpleTag", 's' );
		$content = $this->_recurseAndParse( 's', $content, "_parseSimpleTag", 's' );
		$content = $this->_recurseAndParse( 'sup', $content, "_parseSimpleTag", 'sup' );
		$content = $this->_recurseAndParse( 'sub', $content, "_parseSimpleTag", 'sub' );
		
		// -----------------------------------------
		// More complex tags
		// -----------------------------------------
		
		$content = $this->_recurseAndParse( 'a', $content, "_parseAnchorTag" );
		$content = $this->_recurseAndParse( 'font', $content, "_parseFontTag" );
		$content = $this->_recurseAndParse( 'div', $content, "_parseDivTag" );
		$content = $this->_recurseAndParse( 'p', $content, "_parseParagraphTag" );
		$content = $this->_recurseAndParse( 'span', $content, "_parseSpanTag" );
		$content = $this->_recurseAndParse( 'blockquote', $content, "_parseBlockquoteTag" );
		
		/* Possibility of preceeding \n because of P tag */
		$content = trim( $content );
		
		// -----------------------------------------
		// Lists
		// -----------------------------------------
		
		$content = $this->_recurseAndParse( 'ol', $content, "_parseListTag" );
		$content = $this->_recurseAndParse( 'ul', $content, "_parseListTag" );
		
		// -----------------------------------------
		// WYSI-Weirdness #10: Random junk
		// -----------------------------------------
		
		$content = str_ireplace( array( "<a>", "</a>", "</li>" ), "", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #11: Fix up list stuff
		// -----------------------------------------
		
		$content = preg_replace( '#<li>(.*)((?=<li>)|</li>)#is', '\\1', $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #11.1: Safari badness
		// -----------------------------------------
		
		$content = str_replace( "</div>", "", $content );
			
		/*
		 * Sometimes, unclosed P tags remain if text is copied and pasted
		 * directly
		 */
		$content = preg_replace( '#<(p|div|ul|li)([^\>]+?)\>#is', '', $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #12: Convert rest to HTML
		// -----------------------------------------
		
		//$content = str_replace( '&lt;', '<', $content );
		//$content = str_replace( '&gt;', '>', $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #13: Remove useless tags
		// -----------------------------------------
		
		/* Remove embedded stuffs */
		$content = preg_replace( '#\[(center|right|left)\]\[\1\]#i', '[\1]', $content );
		$content = preg_replace( '#\[/(center|right|left)\]\[/\1\]#i', '[/\1]', $content );
		
		while ( preg_match( '#\<(b|u|i|s|li)\>(\s+?)?\</\1\>#is', $content ) )
		{
			$content = preg_replace( '#\<(b|u|i|s|li)\>(\s+?)?\</\1\>#is', "", $content );
		}
		
		// -----------------------------------------
		// WYSI-Weirdness #14: Opera crap
		// -----------------------------------------
		
		$content = preg_replace( '#\[(font|size|color)\]=[\"\']([^\"\']+?)[\"\']\]\[/\\1\]#is', "", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #14.1: Safari crap
		// -----------------------------------------
		
		$content = preg_replace( '#\[(font|size|color)=&quot;([^\"\']+?)&quot;\]\[/\\1\]#is', "", $content );
		
		// -----------------------------------------
		// WYSI-Weirdness #15: No domain in FF?
		// -----------------------------------------
		
		$content = preg_replace( '#(http|https):\/\/index.php(.*?)#is', $this->settings['board_url'] . '/index.php\\2', $content );
		$content = preg_replace( '#\[url=[\'\"]index.php(.*?)[\"\']#is', "[url=\"" . $this->settings['board_url'] . '/index.php\\1"', $content );
		
		/* Fix up incorrect tags outside of quotes */
		$content = preg_replace( '#\[(b|u|s)\](\[quote)#', '\2[\1]', $content );
		$content = preg_replace( '#(\[/quote])\[/(b|u|s)\]#', '[/\2]\1', $content );
		
		// -----------------------------------------
		// Replace tabs
		// -----------------------------------------
		// PSDebug::addLogMessage( $content, 'editor', false, true );
		$content = str_replace( "{'tab'}", "\t", $content );
		
		/* Replace newlines */
		$content = str_replace( "\n", "<br />", $content );
		
		// -----------------------------------------
		// Now call the santize routine to make
		// html and nasties safe. VITAL!!
		// -----------------------------------------
		
		//$content = $this->_clean( trim( $content ), $NO_SANITIZE, true );
		
		/* Ensure [xxx=&quot; is fixed */
		$content = preg_replace( '#\[(\w+?)=&quot;(.+?)&quot;\]#', "[\\1=\"\\2\"]", $content );
		
		/* Relative paths */
		$content = preg_replace( '#\[img\](../../|&\#46;&\#46;/&\#46;&\#46;/)public/#', '[img]' . $this->settings['board_url'] . '/public/', $content );
		
		return $content;
	}
	
	/**
	 * RTE: Parse List tag
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseListTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		$list_type = trim( preg_replace( '#"?list-style-type:\s+?([\d\w\_\-]+);?"?#si', '\\1', $this->_getValueOfOption( 'style', $opening_tag ) ) );
		$css = $this->_getValueOfOption( 'class', $opening_tag );
		
		// -----------------------------------------
		// Set up a default...
		// -----------------------------------------
		
		if ( ( stristr( $css, ' decimal' ) ) or ! $list_type and $tag == 'ol' )
		{
			$list_type = 'decimal';
		}
		
		// -----------------------------------------
		// Tricky regex to clean all list items
		// -----------------------------------------
		
		$between_text = preg_replace( '#<li([^\>]+?)\>#is', '<li>', $between_text );
		$between_text = preg_replace( '#<li>\s+?</li>#is', '', $between_text );
		//$between_text = preg_replace( '#<li>((.(?!</li))*)(?=</?ul|</?ol|\[list|<li|\[/list)#siU', '<li>\\1</li>', $between_text );
		
		$between_text = trim( $this->_recurseAndParse( 'li', $between_text, "_parseListElement" ) );
		
		$allowed_types = array( 'upper-alpha' => 'A', 'upper-roman' => 'I', 'lower-alpha' => 'a', 'lower-roman' => 'i', 'decimal' => '1' );
		
		if ( ! $allowed_types[$list_type] )
		{
			$open_tag = "[list]\n";
		}
		else
		{
			$open_tag = '[list=' . $allowed_types[$list_type] . "]\n";
		}
		
		return $open_tag . $this->_recurseAndParse( $tag, $between_text, '_parseListTag' ) . "\n[/list]";
	}
	
	/**
	 * RTE: Parse List Element tag
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseListElement( $tag, $between_text, $opening_tag, $parse_tag )
	{
		/* Check for quote tags */
		$openQuote = substr_count( strtolower( $between_text ), '[quote' );
		$closeQuote = substr_count( strtolower( $between_text ), '[/quote]' );
		
		if ( $openQuote != $closeQuote )
		{
			$between_text = str_replace( array( '[quote', '[/quote]' ), array( '&#91;quote', '&#91;/quote&#93;' ), $between_text );
		}
		
		return '[*]' . rtrim( $between_text ) . "\n";
	}
	
	/**
	 * RTE: Parse paragraph tags
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseParagraphTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		/* Got any text to wrap? beware empty() because we might have 0 */
		if ( $between_text == '' )
		{
			return;
		}
		
		// -----------------------------------------
		// Reset local start tags
		// -----------------------------------------
		
		$start_tags = "";
		$end_tags = "";
		
		// -----------------------------------------
		// Check for inline style moz may have added and append start_tags
		// -----------------------------------------
		
		$this->_parseStyles( $opening_tag, $start_tags, $end_tags );
		
		// -----------------------------------------
		// Now parse align and style (if any)
		// -----------------------------------------
		
		$align = $this->_getValueOfOption( 'align', $opening_tag );
		$style = $this->_getValueOfOption( 'style', $opening_tag );
		$css   = $this->_getValueOfOption( 'class', $opening_tag );
		$textAlign = $this->_extractCssValue( $style, 'text-align' );
		$marginLeft = intval( $this->_extractCssValue( $style, 'margin-left' ) );
		
		if ( $align == 'center' or $textAlign == 'center' or stristr( $css, 'bbc_center' ) )
		{
			$start_tags = "\n" . $start_tags;
			
			if ( ! stristr( $start_tags, '[center]' ) )
			{
				$start_tags .= '[center]';
				$end_tags .= '[/center]';
			}
		}
		else if ( $align == 'left' or $textAlign == 'left' )
		{
			$start_tags = "\n" . $start_tags;
			
			if ( ! stristr( $start_tags, '[left]' ) )
			{
				$start_tags .= '[left]';
				$end_tags .= '[/left]';
			}
		}
		else if ( $align == 'right' or $textAlign == 'right' )
		{
			$start_tags = "\n" . $start_tags;
			
			if ( ! stristr( $start_tags, '[right]' ) )
			{
				$start_tags .= '[right]';
				$end_tags .= '[/right]';
			}
		}
		else if ( $marginLeft )
		{
			$level = ( $marginLeft > 40 ) ? $marginLeft / 40 : 1;
			
			if ( trim( $between_text ) )
			{
				$start_tags = "\n" . $start_tags;
				
				$start_tags .= '[indent=' . $level . ']';
				$end_tags .= '[/indent]';
			}
		}
		else
		{
			// No align? Make paragraph
			$start_tags .= "\n";
		}
		
		/* Was there just a blank space in there? */
		if ( preg_match( '#^[ ]+$#', $between_text ) )
		{
			return "\n";
		}
		
		return $start_tags . $this->_recurseAndParse( 'p', $between_text, '_parseParagraphTag' ) . $end_tags;
	}
	
	/**
	 * RTE: Parse pre tags
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parsePreTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		/* Got any text to wrap? beware empty() because we might have 0 */
		if ( $between_text == '' )
		{
			return;
		}
		
		$class   = $this->_getValueOfOption( 'class', $opening_tag );
		$lang    = 'auto';
		$linenum = 0;
		
		$names = preg_split( '/ /', $class, null, PREG_SPLIT_NO_EMPTY );
		
		if ( count( $names ) )
		{
			foreach( $names as $name )
			{
				if ( strstr( $name, '_lang-' ) )
				{
					list( $meh, $oohYeah ) = explode( '-', $name );
					
					$lang = trim( $oohYeah );
				}
				
				if ( strstr( $name, '_linenums:' ) )
				{
					list( $Jacob, $Edward ) = explode( ':', $name );
					
					$linenum = intval( $Edward );
				}
			}
		}
		
		/* Make newlines safe */
		$between_text = str_replace( "\n", "<!-preserve.newline-->", $between_text );
		
		return '[code=' . $lang . ':' . $linenum . ']' . $this->_recurseAndParse( 'pre', $between_text, '_parsePreTag' ) . '[/code]';
	}
	
	/**
	 * RTE: Parse Span tag
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseSpanTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		$start_tags = "";
		$end_tags = "";
		
		// -----------------------------------------
		// Check for inline style moz may have added
		// -----------------------------------------
		
		$this->_parseStyles( $opening_tag, $start_tags, $end_tags );
		
		return $start_tags . $this->_recurseAndParse( 'span', $between_text, '_parseSpanTag' ) . $end_tags;
	}
	
	/**
	 * RTE: Parse Fieldset tag used to contain code
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseFieldsetTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		// -----------------------------------------
		// Reset local start tags
		// -----------------------------------------
		$start_tags = "";
		$end_tags = "";
		
		// -----------------------------------------
		// Check for inline style moz may have added
		// -----------------------------------------
		
		$this->_parseStyles( $opening_tag, $start_tags, $end_tags );
		
		// -----------------------------------------
		// Now parse align (if any)
		// -----------------------------------------
		
		$class = $this->_getValueOfOption( 'class', $opening_tag );
		
		if ( $class == 'ipbCode' )
		{
			$start_tags .= '[code]';
			$end_tags .= '[/code]';
		}
		
		// -----------------------------------------
		// Get recursive text
		// -----------------------------------------
		
		$final = $this->_recurseAndParse( 'fieldset', trim( $between_text ), '_parseFieldsetTag' );
		
		// -----------------------------------------
		// Now return
		// -----------------------------------------
		
		return $start_tags . $final . $end_tags;
	}
	
	/**
	 * RTE: Parse BLOCKQUOTE tag
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseBlockquoteTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		// -----------------------------------------
		// Reset local start tags
		// -----------------------------------------
		$start_tags = "";
		$end_tags = "";
		
		// -----------------------------------------
		// Check for inline style moz may have added
		// -----------------------------------------
	
		$attr = $this->_parseDataAttributes( $opening_tag );
		$extra = '';
		
		if ( $attr['author'] )
		{
			$extra .= ' name="' . trim( str_replace( '"', '&quot;', $attr['author'] ) ) . '"';
		}
		
		if ( $attr['cid'] )
		{
			$extra .= ' post="' . intval( $attr['cid'] ) . '"';
		}
		
		if ( $attr['time'] )
		{
			$extra .= ' timestamp="' . intval( $attr['time'] ) . '"';
		}
		
		if ( $attr['date'] )
		{
			$extra .= ' date="' . trim( str_replace( '"', '&quot;', $attr['date'] ) ) . '"';
		}
		
		// -----------------------------------------
		// Get recursive text
		// -----------------------------------------
		
		$final = $this->_recurseAndParse( 'blockquote', $between_text, '_parseBlockquoteTag' );
		
		// -----------------------------------------
		// Now return
		// -----------------------------------------
		
		return '[quote' . $extra . ']' . $final . '[/quote]';
	}
	
	/**
	 * RTE: Parse DIV tag
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseDivTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		// -----------------------------------------
		// Reset local start tags
		// -----------------------------------------
		$start_tags = "";
		$end_tags = "";
		$allowEndNl = true;
		
		// -----------------------------------------
		// Check for inline style moz may have added
		// -----------------------------------------
		
		$this->_parseStyles( $opening_tag, $start_tags, $end_tags );
		
		// -----------------------------------------
		// Now parse align (if any)
		// -----------------------------------------
		
		$align = $this->_getValueOfOption( 'align', $opening_tag );
		
		if ( $align == 'center' )
		{
			$start_tags .= '[center]';
			$end_tags .= '[/center]';
			$allowEndNl = false;
		}
		else if ( $align == 'left' )
		{
			$start_tags .= '[left]';
			$end_tags .= '[/left]';
			$allowEndNl = false;
		}
		else if ( $align == 'right' )
		{
			$start_tags .= '[right]';
			$end_tags .= '[/right]';
			$allowEndNl = false;
		}
		else
		{
			// No align? Make paragraph
			//$start_tags .= "\n";
		}
		
		// -----------------------------------------
		// Get recursive text
		// -----------------------------------------
		
		$final = $this->_recurseAndParse( 'div', $between_text, '_parseDivTag' );
		
		// -----------------------------------------
		// Now return
		// -----------------------------------------
		
		return $start_tags . $final . $end_tags;
	}
	
	/**
	 * RTE: Parse HTML5 data- attributes
	 *
	 * @access protected
	 * @param  string	Opening tag
	 * @return array 
	 */
	protected function _parseDataAttributes( $opening_tag )
	{
		$attributes = array();
		
		preg_match_all( '#data-(\w+?)=(?:[\'"])([^\'"]+?)(?:[\'"])#i', $opening_tag, $matches );
		
		if ( is_array( $matches[1] ) && count( $matches[1]) )
		{
			foreach( $matches[1] as $id => $match )
			{
				$attr = $matches[1][$id];
				$val  = $matches[2][$id];
				
				$attributes[ $attr ] = $val;
			}
		}
		
		return $attributes;
	}
	
	/**
	 * RTE: Parse style attributes (color, font, size, b, i..etc)
	 *
	 * @access protected
	 * @param
	 *        	string	Opening tag
	 * @param
	 *        	string	Start tags
	 * @param
	 *        	string	End tags
	 * @return string text
	 */
	protected function _parseStyles( $opening_tag, &$start_tags, &$end_tags )
	{
		$style_list = array( array( 'tag' => 'color', 'rx' => '(?<![\w\-])color:\s*([^;]+);?', 'match' => 1 ), array( 'tag' => 'font', 'rx' => 'font-family:\s*([^;]+);?', 'match' => 1 ), array( 'tag' => 'size', 'rx' => 'font-size:\s*(.+);?', 'match' => 1 ), array( 'tag' => 'b', 'rx' => 'font-weight:\s*(bold);?' ), array( 'tag' => 'i', 'rx' => 'font-style:\s*(italic);?' ), array( 'tag' => 'u', 'rx' => 'text-decoration:\s*(underline);?' ), array( 'tag' => 'left', 'rx' => 'text-align:\s*(left);?' ), array( 'tag' => 'center', 'rx' => 'text-align:\s*(center);?' ), array( 'tag' => 'right', 'rx' => 'text-align:\s*(right);?' ), array( 'tag' => 'background', 'rx' => 'background-color:\s*([^;]+);?', 'match' => 1 ) );
		
		// -----------------------------------------
		// get style option
		// -----------------------------------------
		
		$style = $this->_getValueOfOption( 'style', $opening_tag );
		$class = $this->_getValueOfOption( 'class', $opening_tag );
		
		// -----------------------------------------
		// Convert RGB to hex
		// -----------------------------------------
		
		$style = preg_replace_callback( '#(?<![\w\-])color:\s+?rgb\((\d+,\s+?\d+,\s+?\d+)\)(;?)#i', array( &$this, '_rgbToHex' ), $style );
		
		// -----------------------------------------
		// Pick through possible styles
		// -----------------------------------------
		
		foreach( $style_list as $data )
		{
			if ( preg_match( '#' . $data['rx'] . '#i', $style, $match ) )
			{
				if ( $data['match'] )
				{
					if ( $data['tag'] != 'size' )
					{
						if ( $data['tag'] != 'font' or $match[$data['match']] != 'Verdana, arial, sans-serif' )
						{
							if( $data['tag'] == 'font' AND strpos( $match[$data['match']], ',' ) !== false )
							{
								// Gotta fix stuff like 'trebuchet ms', helvetica, sans-serif
								$_matches				= explode( ',', $match[$data['match']] );
								$match[$data['match']]	= $_matches[0];
							}

							$start_tags .= "[{$data['tag']}={$match[$data['match']]}]";
						}
					}
					else
					{
						$start_tags .= "[{$data['tag']}=" . $this->_convertRealsizeToBbsize( $match[$data['match']] ) . "]";
					}
				}
				else
				{
					$start_tags .= "[{$data['tag']}]";
				}
				
				if ( $start_tags && $data['tag'] != 'font' or $match[$data['match']] != 'Verdana, arial, sans-serif' )
				{
					$end_tags = "[/{$data['tag']}]" . $end_tags;
				}
			}
		}
		
		if ( $class == 'bbc_underline' )
		{
			$start_tags = '[u]';
			$end_tags = '[/u]';
		}
	}
	
	/**
	 * RTE: Parse FONT tag
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseFontTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		$font_tags = array( 'font' => 'face', 'size' => 'size', 'color' => 'color' );
		$start_tags = "";
		$end_tags = "";
		
		// -----------------------------------------
		// Check for attributes
		// -----------------------------------------
		
		foreach( $font_tags as $bbcode => $string )
		{
			$option = $this->_getValueOfOption( $string, $opening_tag );
			
			if ( $option )
			{
				$start_tags .= "[{$bbcode}=\"{$option}\"]";
				$end_tags = "[/{$bbcode}]" . $end_tags;
				
				if ( $this->debug == 2 )
				{
					print "<br />Got bbcode=$bbcode / opening_tag=$opening_tag";
					print "<br />- Adding [$bbcode=\"$option\"] [/$bbcode]";
					print "<br />-- start tags now: {$start_tags}";
					print "<br />-- end tags now: {$end_tags}";
				}
			}
		}
		
		// -----------------------------------------
		// Now check for inline style moz may have
		// added
		// -----------------------------------------
		
		$this->_parseStyles( $opening_tag, $start_tags, $end_tags );
		
		return $start_tags . $this->_recurseAndParse( 'font', $between_text, '_parseFontTag' ) . $end_tags;
	}
	
	/**
	 * RTE: Simple tags
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseSimpleTag( $tag, $between_text, $opening_tag, $parse_tag )
	{
		if ( ! $parse_tag )
		{
			$parse_tag = $tag;
		}
		
		return "[{$parse_tag}]" . $this->_recurseAndParse( $tag, $between_text, '_parseSimpleTag', $parse_tag ) . "[/{$parse_tag}]";
	}
	
	/**
	 * RTE: Parse A HREF tag
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Opening tag complete
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _parseAnchorTag( $tag, $between_text, $opening_tag, $parse_tag = '' )
	{
		$mytag = 'url';
		$href = $this->_getValueOfOption( 'href', $opening_tag );
		$class = $this->_getValueOfOption( 'class', $opening_tag );
		
		$href = str_replace( '<', '&lt;', $href );
		$href = str_replace( '>', '&gt;', $href );
		$href = str_replace( ' ', '%20', $href );
		
		if ( preg_match( '#^mailto\:#is', $href ) )
		{
			$mytag = 'email';
			$href = str_replace( "mailto:", "", $href );
		}
		
		return "[{$mytag}=\"{$href}\"]" . $this->_recurseAndParse( $tag, $between_text, '_parseAnchorTag', $parse_tag ) . "[/{$mytag}]";
	}
	
	/**
	 * RTE: Recursively parse tags
	 *
	 * @access protected
	 * @param
	 *        	string	Tag
	 * @param
	 *        	string	Text between opening and closing tag
	 * @param
	 *        	string	Callback Function
	 * @param
	 *        	string	Parse tag
	 * @return string text
	 */
	protected function _recurseAndParse( $tag, $text, $function, $parse_tag = '' )
	{
		// -----------------------------------------
		// INIT
		// -----------------------------------------
		$tag = strtolower( $tag );
		$open_tag = "<" . $tag;
		$open_tag_len = strlen( $open_tag );
		$close_tag = "</" . $tag . ">";
		$close_tag_len = strlen( $close_tag );
		$start_search_pos = 0;
		$tag_begin_loc = 1;
		
		// -----------------------------------------
		// Start the loop
		// -----------------------------------------
		
		while ( $tag_begin_loc !== FALSE )
		{
			$lowtext = strtolower( $text );
			$tag_begin_loc = @strpos( $lowtext, $open_tag, $start_search_pos );
			$lentext = strlen( $text );
			$quoted = '';
			$got = FALSE;
			$tag_end_loc = FALSE;
			
			// -----------------------------------------
			// No opening tag? Break
			// -----------------------------------------
			
			if ( $tag_begin_loc === FALSE )
			{
				break;
			}
			
			// -----------------------------------------
			// Pick through text looking for delims
			// -----------------------------------------
			
			for( $end_opt = $tag_begin_loc ; $end_opt <= $lentext ; $end_opt ++ )
			{
				$chr = $text{$end_opt};
				
				// -----------------------------------------
				// We're now in a quote
				// -----------------------------------------
				
				if ( ( in_array( $chr, $this->_delimiters ) ) and $quoted == '' )
				{
					$quoted = $chr;
				}
				
				// -----------------------------------------
				// We're not in a quote any more
				// -----------------------------------------
				
				else if ( ( in_array( $chr, $this->_delimiters ) ) and $quoted == $chr )
				{
					$quoted = '';
				}
				
				// -----------------------------------------
				// Found the closing bracket of the open tag
				// -----------------------------------------
				
				else if ( $chr == '>' and ! $quoted )
				{
					$got = TRUE;
					break;
				}
				
				else if ( ( in_array( $chr, $this->_nonDelimiters ) ) and ! $tag_end_loc )
				{
					$tag_end_loc = $end_opt;
				}
			}
			
			// -----------------------------------------
			// Not got the complete tag?
			// -----------------------------------------
			
			if ( ! $got )
			{
				break;
			}
			
			// -----------------------------------------
			// Not got a tag end location?
			// -----------------------------------------
			
			if ( ! $tag_end_loc )
			{
				$tag_end_loc = $end_opt;
			}
			
			// -----------------------------------------
			// Extract tag options...
			// -----------------------------------------
			
			$tag_opts = substr( $text, $tag_begin_loc + $open_tag_len, $end_opt - ( $tag_begin_loc + $open_tag_len ) );
			$actual_tag_name = substr( $lowtext, $tag_begin_loc + 1, ( $tag_end_loc - $tag_begin_loc ) - 1 );
			
			// -----------------------------------------
			// Check against actual tag name...
			// -----------------------------------------
			
			if ( $actual_tag_name != $tag )
			{
				$start_search_pos = $end_opt;
				continue;
			}
			
			// -----------------------------------------
			// Now find the end tag location
			// -----------------------------------------
			
			$tag_end_loc = strpos( $lowtext, $close_tag, $end_opt );
			
			// -----------------------------------------
			// Not got one? Break!
			// -----------------------------------------
			
			if ( $tag_end_loc === FALSE )
			{
				break;
			}
			
			// -----------------------------------------
			// Check for nested tags
			// -----------------------------------------
			
			$nest_open_pos = strpos( $lowtext, $open_tag, $end_opt );
			
			while ( $nest_open_pos !== FALSE and $tag_end_loc !== FALSE )
			{
				// -----------------------------------------
				// It's not actually nested
				// -----------------------------------------
				
				if ( $nest_open_pos > $tag_end_loc )
				{
					break;
				}
				
				if ( $this->debug == 2 )
				{
					print "\n\n<hr>( " . htmlspecialchars( $open_tag ) . " ) NEST FOUND</hr>\n\n";
				}
				
				$tag_end_loc = strpos( $lowtext, $close_tag, $tag_end_loc + $close_tag_len );
				$nest_open_pos = strpos( $lowtext, $open_tag, $nest_open_pos + $open_tag_len );
			}
			
			// -----------------------------------------
			// Make sure we have an end location
			// -----------------------------------------
			
			if ( $tag_end_loc === FALSE )
			{
				$start_search_pos = $end_opt;
				continue;
			}
			
			$this_text_begin = $end_opt + 1;
			$between_text = substr( $text, $this_text_begin, $tag_end_loc - $this_text_begin );
			$offset = $tag_end_loc + $close_tag_len - $tag_begin_loc;
			
			// -----------------------------------------
			// Pass to function
			// -----------------------------------------
			
			$final_text = $this->$function( $tag, $between_text, $tag_opts, $parse_tag );
			
			// -----------------------------------------
			// #DEBUG
			// -----------------------------------------
			
			if ( $this->debug == 2 )
			{
				print "<hr><b>REPLACED {$function}($tag, ..., $tag_opts):</b><br />" . htmlspecialchars( substr( $text, $tag_begin_loc, $offset ) ) . "<br /><b>WITH:</b><br />" . htmlspecialchars( $final_text ) . "<hr>NEXT ITERATION";
			}
			
			// -----------------------------------------
			// Swap text
			// -----------------------------------------
			
			$text = substr_replace( $text, $final_text, $tag_begin_loc, $offset );
			$start_search_pos = $tag_begin_loc + strlen( $final_text );
		}
		
		return $text;
	}
	
	/**
	 * RTE: Extract option HTML
	 *
	 * @access protected
	 * @param
	 *        	string	Option
	 * @param
	 *        	string	Text
	 * @return string text
	 */
	protected function _getValueOfOption( $option, $text )
	{
		if ( $option == 'face' )
		{
			// Bad font face, bad
			preg_match( "#{$option}(\s+?)?\=(\s+?)?[\"']?(.+?)([\"']|$|color|size|>)#is", $text, $matches );
		}
		else
		{
			// @link
			// http://community.invisionpower.com/tracker/issue-29336-colours-do-not-work-in-signatures
			// ckeditor should have a more universal formatting so let's go with
			// the one regex now
			// if( $option == 'style' AND ( $this->memberData['userAgentKey'] ==
			// 'safari' OR $this->memberData['userAgentKey'] == 'chrome' ) )
			// {
			/*
			 * @link
			 * http://community.invisionpower.com/tracker/issue-37385-editor-removes-apostrophe-from-url/
			 * tightened up regex end ([\"'](\s|$|>))
			 */
			preg_match( "#{$option}(\s*?)?\=(\s*?)?[\"']?(.+?)([\"'](\s|$|>))#is", $text, $matches );
			// }
			// else
			// {
			// preg_match(
		// "#{$option}(\s*?)?\=(\s*?)?[\"']?(.+?)([\"']|$|\s|>)#is", $text,
		// $matches );
			// }
		}
		
		if ( $option == 'style' )
		{
			switch( $matches[3] )
			{
				case 'font-size: x-small;' :
					$matches[3] = 'font-size: 8;';
					break;
				
				case 'font-size: small;' :
					$matches[3] = 'font-size: 10;';
					break;
				
				case 'font-size: medium;' :
					$matches[3] = 'font-size: 12;';
					break;
				
				case 'font-size: large;' :
					$matches[3] = 'font-size: 14;';
					break;
				
				case 'font-size: x-large;' :
					$matches[3] = 'font-size: 18;';
					break;
				
				case 'font-size: xx-large;' :
					$matches[3] = 'font-size: 24;';
					break;
				
				case 'font-size: xxx-large;' :
				case 'font-size: -webkit-xxx-large;' :
					$matches[3] = 'font-size: 36;';
					break;
			}
		}
		
		return isset( $matches[3] ) ? trim( $matches[3] ) : '';
	}
	
	/**
	 * unhtml url: Removes < and >
	 *
	 * @access protected
	 * @param
	 *        	array Matches from preg_replace_callback
	 * @return string text
	 */
	protected function _unhtmlUrl( $matches = array() )
	{
		$url = stripslashes( $matches[3] );
		$type = stripslashes( $matches[1] ? $matches[1] : 'a href' );
		
		$url = str_replace( '<', '&lt;', $url );
		$url = str_replace( '>', '&gt;', $url );
		$url = str_replace( ' ', '%20', $url );
		
		return '<' . $type . '="' . $url . '"';
	}
	
	/**
	 * Fetches the value of an inline style
	 * 
	 * @param string $style        	
	 * @param string $lookFor        	
	 * @return string boolean
	 */
	protected function _extractCssValue( $style, $lookFor )
	{
		if ( strstr( $style, 'style=' ) )
		{
			$style = $this->_getValueOfOption( 'style', $style );
		}
		
		if ( strstr( $style, $lookFor ) )
		{
			if ( preg_match( '#' . preg_quote( $lookFor, '#' ) . ':(?:\s+?)?(.+?)(;|$|\n)#', $style, $matches ) )
			{
				return trim( $matches[1] );
			}
		}
		
		return false;
	}
	
	/**
	 * Converts color:rgb(x,x,x) to color:#xxxxxx
	 *
	 * @access protected
	 * @param
	 *        	string	rgb contents: x,x,x
	 * @param
	 *        	string	regex end
	 * @return string text
	 */
	protected function _rgbToHex( $matches )
	{
		$t = $matches[1];
		$t2 = $matches[2];
		
		$tmp = array_map( "trim", explode( ",", $t ) );
		return 'color: ' . sprintf( "#%02X%02X%02X" . $t2, intval( $tmp[0] ), intval( $tmp[1] ), intval( $tmp[2] ) );
	}
	
	/**
	 * Get BBCode font size from real PX size
	 *
	 * @access	public
	 * @param	integer		PX Size
	 * @return	integer		BBCode size
	 */
	protected function _convertRealsizeToBbsize( $real )
	{
		$real = intval( $real );
		$flip = array_flip( $this->_fontSizes );
		
		//-----------------------------------------
		// If we have a true mapping, use it
		//-----------------------------------------
	
		if ( $flip[ $real ] )
		{
			return $flip[ $real ];
		}
		else
		{
			//-----------------------------------------
			// Otherwise find the next closest size down
			//-----------------------------------------
				
			foreach( $flip as $font => $bbcode )
			{
				if ( $real < $font )
				{
					return ( ( $bbcode - 1 ) > 1 ) ? ( $bbcode - 1 ) : 1;
				}
			}
				
			return 2;
		}
	}
	
}
