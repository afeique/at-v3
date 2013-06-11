<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Text Parsing: LEGACY CR..STUFF
 * Last Updated: $Date: 2012-06-08 09:28:02 +0100 (Fri, 08 Jun 2012) $
 * </pre>
 *
 * @author 		$Author: mmecham $
 * @copyright	(c) 2001 - 2012 Invision Power Services, Inc.
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

class class_text_parser_legacy extends classes_text_parser
{
	
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
	 * Takes data from the editor (HTML) and makes it BBCode
	 * @param string $content
	 */
	public function postEditor( $content )
	{ 
		$content = $this->_postPurifyCodeBoxes( $content );
		
		/* Restore preserved newlines */
		$content = str_replace( "<!-preserve.newline-->", "\n", $content );
		
		if ( self::$Perms['parseHtml'] !== true )
		{
			$content = $this->HtmlToBBCode( $content );
		}
	
		return $content;
	}
	
	/**
	 * Takes stuff given and makes it all lovely for the editor
	 * @param string $content
	 */
	public function preEditor( $content )
	{
		$content = $this->_postPurifyCodeBoxes( $content );
		
		/* Restore preserved newlines */
		$content = str_replace( "<!-preserve.newline-->", "\n", $content );
		
		$content = $this->BBCodeToHtml( $content );
		
		/* Fix up old PRE tags */
		$content = preg_replace( '#<pre class=(["\'])prettyprint#', '<pre class=\1_prettyXprint', $content );
		
		return $content;
	}
	
	/**
	 * Additional methods for preDbParse
	 */
	public function preDbParse( $content )
	{
		$content = $this->_postPurifyCodeBoxes( $content );
		
		return $content;
	}

	/**
	 * Check and make safe embedded codes (removes mark-up HTML purify might have added)
	 * @param array $matches
	 */
	protected function _postPurifyCodeBoxes( $content )
	{
		/* Fetch paired opening and closing tags */
		$data = $this->getTagPositions( $content, 'CODE', array( '[', ']') );

		if ( is_array( $data ) && count( $data ) )
		{
			foreach( $data['open'] as $id => $val )
			{
				$o = $data['open'][ $id ];
				$c = $data['close'][ $id ] - $o;
	
				$slice = substr( $content, $o, $c );
					
				$_origLen = strlen( $slice );
				
				$slice = IPSText::stripTags( $slice, '<pre>,<br>' );
				$slice = str_replace( "<br />" , "\n", $slice );
				$slice = str_replace( "\n"     , "<!-preserve.newline-->", $slice );
				
				$slice = preg_replace( '#(https|http|ftp)://#' , '\1&#58;//', $slice );
				
				$content = substr_replace( $content, $slice, $o, $c );
				
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
			}
		}
		
		
		return $content;
	}
	
}
