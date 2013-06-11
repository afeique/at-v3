<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * BBCode parsing core - common methods
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

/**
 * @author matt
 *
 */
class class_text_parser_bbcode extends classes_text_parser
{
	

	/**
	 * Strip quotes?
	 * Strips quotes from the resulting text
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $strip_quotes			= false;

	/**
	 * Auto convert newlines to html line breaks
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $parse_nl2br				= true;

	

	/**
	 * Section keyword for parsing area
	 *
	 * @access	public
	 * @var		string
	 */	
	public $parseType			= 'post';
	
	
	/**
	 * Allow unicode (parses escaped entities to actual entities)
	 *
	 * @access	public
	 * @var		boolean
	 */	
	public $allow_unicode			= false;

	/**
	 * Maximum number of embeded quotes
	 *
	 * @access	public
	 * @var		integer
	 */	
	public $max_embed_quotes		= 15;
	
	/**
	 * Error code stored
	 *
	 * @access	public
	 * @var		string
	 */	
	public $error					= '';
	
	/**
	 * Warning code stored (warning is like an error but will not stop parsing execution)
	 *
	 * @access	public
	 * @var		string
	 */	
	public $warning					= '';
	
	/**
	 * Number of images we've parsed so far
	 *
	 * @access	protected
	 * @var		integer
	 */	
	protected $image_count			= 0;

	/**
	 * Number of emoticons we've parsed so far
	 *
	 * @access	protected
	 * @var		integer
	 */	
	protected $emoticon_count		= 0;
	
	/**
	 * Array of emoticon alt tags
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $emoticon_alts		= array();

	/**
	 * Registry object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $registry;
	
	/**
	 * Database object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $DB;
	
	/**
	 * Settings object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $settings;
	
	/**
	 * Request object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $request;
	
	/**
	 * Language object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $lang;
	
	/**
	 * Member object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $member;
	protected $memberData;
	
	/**
	 * Cache object
	 *
	 * @access	protected
	 * @var		object
	 */	
	protected $cache;
	protected $caches;
	
	/**
	 * BBCodes we should parse.
	 * Takes into account what section we are in, and our group.
	 *
	 * @access	protected
	 * @var		array
	 */	
	protected $_bbcodes				= array();
	
	/**
	 * Plugin objects
	 *
	 * @access	protected
	 * @var		array 					Holds plugin objects
	 */	
	protected $plugins					= array();
	
	/**
	 * Current position in the text document
	 *
	 * @access	protected
	 * @var		integer
	 */	
	protected $cur_pos					= 0;
	
	/**
	 * Multi-dimensional array of bbcodes not being parsed inside
	 *
	 * @access	protected
	 * @var		array
	 */	
	protected $noParseStorage				= array();
	
	/**
	 * Identifier for replacement
	 *
	 * @access	protected
	 * @var		integer
	 */
	protected $_storedNoParsing			= 0;
	
	/**
	 * Emoticon code
	 *
	 * @access	protected
	 * @var		string
	 */	
	protected $_emoCode					= '';
	
	/**
	 * Emoticon image
	 *
	 * @access	protected
	 * @var		string
	 */	
	protected $_emoImage					= '';
	
	protected $_mediaUrlConverted			= array();
	protected $_sortedSmilies				= array();
	protected $_isConvertingForEditor       = null;
	protected $_urlsEnabled					= true;

	/**
	 * Build in BBCode we can convert from CKEditor safely
	 * @param	array
	 */
	private $_builtInBBCode = array( 'b', 'i', 'u', 's', 'strike', 'font', 'size', 'color', 'background', 'sup', 'sub', 'list', 'url', 'img', 'center', 'left', 'right', 'indent', 'email', 'code', 'quote');
	
	/**
	 * Block level BBCodes
	 * CKEditor wraps [tag] in P tags so we need to strip these
	 * @param	array
	 */
	private $_blockBBcode = array( 'spoiler' );
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object		Registry object
	 * @return	@e void
	 */
	public function __construct()
	{
		/* Make object */
		$this->registry   =  ipsRegistry::instance();
		$this->DB	      =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang	      =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	  =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
			
		/* Load and init BBCodes */
		foreach( $this->cache->getCache('bbcode') as $bbcode )
		{
			/* Allowed this BBCode? */
			if ( $bbcode['bbcode_sections'] != 'all' && parent::$Perms['parseArea'] != 'global' )
			{
				$pass		= false;
				$sections	= explode( ',', $bbcode['bbcode_sections'] );
		
				foreach( $sections as $section )
				{
					if( $section == parent::$Perms['parseArea'] )
					{
						$pass = true;
						break;
					}
				}
		
				if ( ! $pass )
				{
					continue;
				}			
			}
			
			/* Cheat a bit */
			if ( in_array( $bbcode['bbcode_tag'], array( 'code', 'acronym', 'img' ) ) )
			{
				$bbcode['bbcode_no_auto_url_parse'] = 1;
			}
		
			/* Store */
			$this->_bbcodes[ $bbcode['bbcode_tag'] ] = $bbcode;
		}
		
		/* Can we parse URLs */
		if ( isset( $this->_bbcodes['url'] ) )
		{
			/* Allowed to use this? */
			if ( $this->_bbcodes['url']['bbcode_groups'] != 'all' and parent::$Perms['memberData']['member_group_id'] )
			{
				$pass     = false;
				$groups   = array_diff( explode( ',', $this->_bbcodes['url']['bbcode_groups'] ), array( '' ) );
				$mygroups = array( parent::$Perms['memberData']['member_group_id'] );
					
				if ( parent::$Perms['memberData']['mgroup_others'] )
				{
					$mygroups = array_diff( array_merge( $mygroups, explode( ',', IPSText::cleanPermString( parent::$Perms['memberData']['mgroup_others'] ) ) ), array( '' ) );
				}
					
				foreach( $groups as $g_id )
				{
					if ( in_array( $g_id, $mygroups ) )
					{
						$pass = true;
						break;
					}
				}
					
				if ( ! $pass )
				{
					$this->_urlsEnabled = false;
				}
			}
		}
	
		/* Check for emoticons */
		if ( ! $this->cache->exists('emoticons') )
		{
			$emoticons = $this->cache->getCache('emoticons');
			
			/* Fallback on recache */
			if ( ! is_array($emoticons) OR ! count( $emoticons ) )
			{
				$this->cache->rebuildCache( 'emoticons', 'global' );
			}
		}
	}
	
	/**
	 * Takes plain unparsed BBCode (not HTML) and converts it to HTML for storing in the DB
	 * (Common codes parsed, URLs parsed, QUOTE/CODE/App specific not parsed)
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public function toHtml( $txt="" )
	{
		/* Reset */
		$this->_resetPointers();
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_media', 0 );
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_images', 0 );
		
		/* Fix no parsable */
		$txt = $this->_processNoParseCodes( $txt );
		
		$txt = str_replace( array( "\r\n", "\r" ), "\n", $txt );
		
		/* Remove session IDs */
		$txt = preg_replace_callback( '#(\?|&amp;|;|&)s=([0-9a-zA-Z]){32}(&amp;|;|&|$)?#', array( $this, '_bashSession' ), $txt );
		
		/* Preserve newlines in codeboxes */
		$txt = $this->_preserveCodeBoxes( $txt );
		
		/* Fix newlines */
		if ( ! strstr( $txt, "\n" ) && ( stristr( $txt, '<br>' ) || stristr( $txt, '<br />' ) ) )
		{
			$txt = str_ireplace( array( '<br>', '<br />' ), "\n", $txt );
		}
		else
		{
			if ( stristr( $txt, '<br>' ) || stristr( $txt, '<br />' ) )
			{
				$txt = str_replace( "\n", "", $txt );
			}
		}
		
		/* Stop direction swapping */
		$txt	= str_replace( "&#8234;", '', $txt );
		$txt	= str_replace( "&#8235;", '', $txt );
		$txt	= str_replace( "&#8236;", '', $txt );
		$txt	= str_replace( "&#8237;", '', $txt );
		$txt	= str_replace( "&#8238;", '', $txt );
		
		/* Fix old expression "protection" */
		$txt	= str_replace( "exp<b></b>ression", "expression", $txt );

		/* Convert to BR */
		$txt = nl2br( $txt );
		
		/* Restore preserved newlines */
		$txt = str_replace( "<!-preserve.newline-->", "\n", $txt );
		
		/* BBCodes to parse */
		$bbcodes = $this->_builtInBBCode;

		/* Parse BBCode */
		if ( parent::$Perms['parseBBCode']  )
		{
			$txt = $this->_parseBBCode( $txt, 'html', $bbcodes );
		}

		/* Tidy up */
		$txt = str_replace( '</p><br />', '</p>', $txt );
		$txt = str_replace( '</pre><br />', '</pre>', $txt );
		$txt = str_replace( '</div><br />', '</div>', $txt );
		$txt = str_replace( '</blockquote><br />', '</blockquote>', $txt );
		
		$txt = $this->_autoLinkUrls( $txt );
		
		return $txt;
	}
	
	/**
	 * Finish parsing BBCode for display
	 * 
	 * @param string $txt        	
	 * @return string
	 */
	public function toDisplay( $txt )
	{
		/* Init */
		$_storedLinks = array();
		$_counter = 0;
		$_bbcodes = array();
		
		/* Reset */
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_media', 0 );
		
		/* Fix no parsable, second parse */
		$txt = $this->_processNoParseCodes( $txt, 2 );
		
		/* Get BBCode to parse.. and then do it! */
		foreach( $this->_bbcodes as $tag => $data )
		{
			if ( in_array( $tag, $this->_builtInBBCode ) )
			{
				continue;
			}
			else
			{
				$_bbcodes[] = $tag;
			}
		}
		
		/* Clean up block level BBCode */
		$txt = $this->_cleanBlockBBCode( $txt );
		
		/* Parse BBCode */
		if ( parent::$Perms['parseBBCode'] )
		{
			$txt = $this->_parseBBCode( $txt, 'display', $_bbcodes );
		}
		
		/* Finish URLS */
		$txt = $this->_finishUrlsForDisplay( $txt );
		
		/* Fix up URLs made safe previously */
		$txt = $this->_processNoParseCodes( $txt, 3 );
		
		return $txt;
	}
	
	/**
	 * Loop over the bbcode and make replacements as necessary
	 *
	 * @access public
	 * @param string		Current text
	 * @return string text
	 */
	protected function _parseBBCode( $txt, $mode='html', $bbcodes=array() )
	{
		// -----------------------------------------
		// We want preDbParse method called for shared
		// media for permission checking, so force it for now..
		// -----------------------------------------
		
		//$this->_bbcodes[$cur_method]['sharedmedia'] = $this->_bbcodes['display']['sharedmedia'];
		
		/* Replace them */
		if ( count( $this->_bbcodes ) )
		{
			foreach( $this->_bbcodes as $_tag => $_bbcode )
			{
				/* Check to see if we can parse these */
				if ( is_array( $bbcodes ) and count( $bbcodes ) )
				{
					if ( ! in_array( $_bbcode['bbcode_tag'], $bbcodes ) )
					{
						continue;
					}
				}
				
				/* Allowed to use this? */
				if ( $_bbcode['bbcode_groups'] != 'all' and parent::$Perms['memberData']['member_group_id'] )
				{
					$pass     = false;
					$groups   = array_diff( explode( ',', $_bbcode['bbcode_groups'] ), array( '' ) );
					$mygroups = array( parent::$Perms['memberData']['member_group_id'] );
					
					if ( parent::$Perms['memberData']['mgroup_others'] )
					{
						$mygroups = array_diff( array_merge( $mygroups, explode( ',', IPSText::cleanPermString( parent::$Perms['memberData']['mgroup_others'] ) ) ), array( '' ) );
					}
					
					foreach( $groups as $g_id )
					{
						if ( in_array( $g_id, $mygroups ) )
						{
							$pass = true;
							break;
						}
					}
					
					if ( ! $pass )
					{
						continue;
					}
				}
				
				// -----------------------------------------
				// Reset our current position
				// -----------------------------------------
				
				$this->cur_pos = 0;
				
				// -----------------------------------------
				// Store teh tags
				// -----------------------------------------
				
				$_tags = array( $_bbcode['bbcode_tag'] );
				
				// -----------------------------------------
				// We'll also need to check for any aliases
				// -----------------------------------------
				
				if ( $_bbcode['bbcode_aliases'] )
				{
					$aliases = explode( ',', trim( $_bbcode['bbcode_aliases'] ) );
					
					if ( is_array( $aliases ) and count( $aliases ) )
					{
						foreach( $aliases as $alias )
						{
							$_tags[] = trim( $alias );
						}
					}
				}
				
				// -----------------------------------------
				// If we have a plugin, just pass off
				// -----------------------------------------
				
				if ( $_bbcode['bbcode_php_plugin'] )
				{
					$file = IPS_ROOT_PATH . 'sources/classes/text/parser/bbcode/' . $_bbcode['bbcode_php_plugin'];
					
					$_key = md5( $_bbcode['bbcode_tag'] );
					
					/* The legacy 'list' bbcode tag replaces \n out of necessity, however this breaks legacy code boxes upon upgrade.  Ticket 853240 */
					if( $_bbcode['bbcode_tag'] == 'list' )
					{
						/* Preserve newlines in codeboxes */
						$txt = $this->_preserveCodeBoxes( $txt );
					}

					// -----------------------------------------
					// Do we already have this plugin in our registry?
					// -----------------------------------------
					
					if ( isset( $this->plugins[$_key] ) )
					{				
						// -----------------------------------------
						// Run the method if it exists
						// -----------------------------------------
						
						if ( method_exists( $this->plugins[$_key], 'run' ) )
						{
							$_original = $txt;
							
							$txt = $this->plugins[$_key]->run( $txt, $mode );
							
							if ( ! $txt )
							{
								$txt = $_original;
							}
							else if ( $this->plugins[$_key]->error )
							{
								$this->_addParsingError( $this->plugins[$_key]->error );

								if( $_bbcode['bbcode_tag'] == 'list' )
								{
									/* Restore preserved newlines */
									$txt = str_replace( "<!-preserve.newline-->", "\n", $txt );
								}

								continue;
							}
							else if ( $this->plugins[$_key]->warning )
							{
								$this->warning = $this->plugins[$_key]->warning;
							}
						}
					}
					
					// -----------------------------------------
					// First time we've called this plugin
					// -----------------------------------------
					
					elseif ( is_file( $file ) )
					{
						$_classname = IPSLib::loadLibrary( $file, 'bbcode_plugin_' . IPSText::alphanumericalClean( $_bbcode['bbcode_tag'] ) );
						
						// -----------------------------------------
						// Class we need exists
						// -----------------------------------------
						
						if ( class_exists( $_classname ) )
						{
							// -----------------------------------------
							// New instance of class, store in plugin registry
							// for use next time
							// -----------------------------------------
							
							$plugin = new $_classname( $this->registry, $this );
							
							$this->plugins[md5( $_bbcode['bbcode_tag'] )] = $plugin;
							
							// -----------------------------------------
							// Method we need exists
							// -----------------------------------------
							
							if ( method_exists( $plugin, 'run' ) )
							{
								$_original = $txt;
								$txt = $plugin->run( $txt, $mode );
								
								if ( ! $txt )
								{
									$txt = $_original;
								}
								else if ( $plugin->error )
								{
									$this->_addParsingError( $plugin->error );

									if( $_bbcode['bbcode_tag'] == 'list' )
									{
										/* Restore preserved newlines */
										$txt = str_replace( "<!-preserve.newline-->", "\n", $txt );
									}

									continue;
								}
								else if ( $plugin->warning )
								{
									$this->warning = $plugin->warning;
								}
							}
						}
					}
					
					// -----------------------------------------
					// When we run a plugin, we don't do any other processing
					// "automatically".
					// Plugin is capable of doing what it wants that way.
					// -----------------------------------------

					if( $_bbcode['bbcode_tag'] == 'list' )
					{
						/* Restore preserved newlines */
						$txt = str_replace( "<!-preserve.newline-->", "\n", $txt );
					}

					continue;
				}
				
				// -----------------------------------------
				// Loop over this bbcode's tags
				// -----------------------------------------
				
				foreach( $_tags as $_tag )
				{
					// -----------------------------------------
					// Infinite loop catcher
					// -----------------------------------------
					
					$_iteration = 0;
					
					// -----------------------------------------
					// Start building open tag
					// -----------------------------------------
					
					$open_tag = '[' . $_tag;
					
					// -----------------------------------------
					// Doz I can haz opin tag? Loopy loo
					// -----------------------------------------
					
					while ( ( $this->cur_pos = stripos( $txt, $open_tag, $this->cur_pos ) ) !== false )
					{
						// -----------------------------------------
						// Stop infinite loops
						// -----------------------------------------
						
						if ( $_iteration > $this->settings['max_bbcodes_per_post'] )
						{
							break;
						}
						
						$open_length = strlen( $open_tag );
						
						// -----------------------------------------
						// Grab the new position to jump to
						// -----------------------------------------
						
						$new_pos = strpos( $txt, ']', $this->cur_pos ) ? strpos( $txt, ']', $this->cur_pos ) : $this->cur_pos + 1;
						
						// -----------------------------------------
						// Extract the option (like surgery)
						// -----------------------------------------
						
						$_option = '';
						
						if ( $_bbcode['bbcode_useoption'] )
						{
							// -----------------------------------------
							// Is option optional?
							// -----------------------------------------
							
							if ( $_bbcode['bbcode_optional_option'] )
							{
								// -----------------------------------------
								// Does we haz it?
								// -----------------------------------------
								
								if ( substr( $txt, $this->cur_pos + strlen( $open_tag ), 1 ) == '=' )
								{
									$open_length += 1;
									$_option = substr( $txt, $this->cur_pos + $open_length, ( strpos( $txt, ']', $this->cur_pos ) - ( $this->cur_pos + $open_length ) ) );
								}
								
								// -----------------------------------------
								// If not, [u] != [url] (for example)
								// -----------------------------------------
								
								else if ( ( strpos( $txt, ']', $this->cur_pos ) - ( $this->cur_pos + $open_length ) ) !== 0 )
								{
									if ( strpos( $txt, ']', $this->cur_pos ) )
									{
										$this->cur_pos = $new_pos;
										continue;
									}
									else
									{
										break;
									}
								}
							}
							
							// -----------------------------------------
							// No? Then just grab it
							// -----------------------------------------
							
							else
							{
								$open_length += 1;
								$_option = substr( $txt, $this->cur_pos + $open_length, ( strpos( $txt, ']', $this->cur_pos ) - ( $this->cur_pos + $open_length ) ) );
							}
						}
						
						// -----------------------------------------
						// [img] != [i] (for example)
						// -----------------------------------------
						
						else if ( ( strpos( $txt, ']', $this->cur_pos ) - ( $this->cur_pos + $open_length ) ) !== 0 )
						{
							if ( strpos( $txt, ']', $this->cur_pos ) )
							{
								$this->cur_pos = $new_pos;
								continue;
							}
						}
						
						$_iteration ++;
						
						// -----------------------------------------
						// Protect against XSS
						// -----------------------------------------
						
						$_optionStrLen = IPSText::mbstrlen( $_option );
						$_optionSlenstr = strlen( $_option );
						$_option = $this->checkXss( $_option, false, $_tag );
						
						
						
						if ( $_option !== FALSE )
						{
							/*
							 * Not parsing URls? - Needs to be AFTER the FALSE
							 * check just above
							 */
							if ( ! empty( $_bbcode['bbcode_no_auto_url_parse'] ) )
							{
								$_option = preg_replace( "#(http|https|news|ftp)://#i", "\\1&#58;//", $_option );
							}
							
							// -----------------------------------------
							// If this is a single tag, that's it
							// -----------------------------------------
							
							if ( $_bbcode['bbcode_single_tag'] )
							{
								$txt = substr_replace( $txt, $this->_parseBBCodeTag( $_bbcode, $_option, '' ), $this->cur_pos, ( $open_length + $_optionSlenstr + 1 ) );
							}
							
							// -----------------------------------------
							// Otherwise replace out the content too
							// -----------------------------------------
							
							else
							{
								$close_tag = '[/' . $_tag . ']';
								
								if ( stripos( $txt, $close_tag, $new_pos ) !== false )
								{
									$_content = substr( $txt, ( $this->cur_pos + $open_length + $_optionSlenstr + 1 ), ( stripos( $txt, $close_tag, $this->cur_pos ) - ( $this->cur_pos + $open_length + $_optionSlenstr + 1 ) ) );
									
									if ( $_bbcode['bbcode_useoption'] and $_bbcode['bbcode_optional_option'] and ! $_option and ! stristr( $_bbcode['bbcode_replace'], '{option}' ) )
									{
										$_option = $_content;
										$_option = $this->checkXss( $_option, false, $_tag );
									}
									
									/* Not parsing URls? */
									if ( ! empty( $_bbcode['bbcode_no_auto_url_parse'] ) )
									{
										$_content = preg_replace( "#(http|https|news|ftp)://#i", "\\1&#58;//", $_content );
									}
									
									$txt = substr_replace( $txt, $this->_parseBBCodeTag( $_bbcode, $_option, $_content ), $this->cur_pos, ( stripos( $txt, $close_tag, $this->cur_pos ) + strlen( $close_tag ) - $this->cur_pos ) );
								}
								else
								{
									// -----------------------------------------
									// If there's no close tag, no need to
									// continue
									// -----------------------------------------
									
									break;
								}
							}
						}
						
						// -----------------------------------------
						// And reset current position to end of open tag
						// Bug 14744 - if we jump to $new_pos it can skip the
						// opening of the next bbcode tag
						// when the replacement HTML is shorter than the full
						// bbcode representation...
						// -----------------------------------------
						
						$this->cur_pos = stripos( $txt, $open_tag ) ? stripos( $txt, $open_tag ) : $this->cur_pos + 1; // $new_pos;
						
						if ( $this->cur_pos > strlen( $txt ) )
						{
							break;
						}
					}
				}
			}
		}
			
		return $txt;
	}
	
	/**
	 * Does the actual bbcode replacement
	 *
	 * @access	protected
	 * @param	string		Current bbcode to parse
	 * @param	string		[Optional] Option text
	 * @param	string		[Optional for single tag bbcodes] Content text
	 * @return	string		Converted text
	 */
	protected function _parseBBCodeTag( $_bbcode, $option='', $content='')
	{
		// -----------------------------------------
		// Strip the optional quote delimiters
		// -----------------------------------------
		$option = str_replace( '&amp;quot;', '"', $option );
		$option = str_replace( '&quot;', '"', $option );
		$option = str_replace( '&#39;', "'", $option );
		$option = trim( $option, '"' . "'" );
		
		// -----------------------------------------
		// Stop CSS injection
		// -----------------------------------------
		
		if ( $option )
		{
			// -----------------------------------------
			// Cut off for entities in option
			// @see
			// http://community.invisionpower.com/tracker/issue-19958-acronym/
			// -----------------------------------------
			

			$option = IPSText::UNhtmlspecialchars( $option );
			$option = str_replace( '&#33;', '!', $option );
			
			/* http://community.invisionpower.com/resources/bugs.html/_/ip-board/error-in-accented-characters-in-the-option-tag-bbcode-r40739 */
			$option = IPSText::convertNumericEntityToNamed( $option );
			
			$option = IPSText::decodeNamedHtmlEntities( $option );
			$option = IPSText::UNhtmlspecialchars( $option );
			
			if ( strpos( $option, ';' ) !== false )
			{
				$option = substr( $option, 0, strpos( $option, ';' ) );
			}
			
			$charSet = ( IPS_DOC_CHAR_SET == 'ISO-8859-1' ) ? 'ISO-8859-15' : IPS_DOC_CHAR_SET;

			$option = @htmlentities( $option, ENT_NOQUOTES, $charSet );
			$option = str_replace( '!', '&#33;', $option );
		}
		
		$option = str_replace( '"', '&quot;', $option );
		$option = str_replace( "'", '&#39;', $option );
		
		// -----------------------------------------
		// Swapping option/content?
		// -----------------------------------------
		
		if ( $_bbcode['bbcode_switch_option'] )
		{
			$_tmp = $content;
			$content = $option;
			$option = $_tmp;
		}
		
		// -----------------------------------------
		// Replace
		// -----------------------------------------
		
		$replaceCode = $_bbcode['bbcode_replace'];
		$replaceCode = str_replace( '{base_url}', $this->settings['board_url'] . '/index.php?', $replaceCode );
		$replaceCode = str_replace( '{image_url}', $this->settings['img_url'], $replaceCode );
		
		preg_match( '/\{text\.(.+?)\}/i', $replaceCode, $matches );
		
		if ( is_array( $matches ) and count( $matches ) )
		{
			$replaceCode = str_replace( $matches[0], $this->lang->words[$matches[1]], $replaceCode );
		}
		
		$replaceCode = str_replace( '{option}', $option, $replaceCode );
		$replaceCode = str_replace( '{content}', $content, $replaceCode );
		
		// -----------------------------------------
		// Fix linebreaks in textareas
		// -----------------------------------------
		
		if ( stripos( $replaceCode, "<textarea" ) !== false )
		{
			$replaceCode = str_replace( '<br />', "", $replaceCode );
			$replaceCode = str_replace( "\r", "", $replaceCode );
			$replaceCode = str_replace( "\n", "<br />", $replaceCode );
		}
		
		return $replaceCode;
	}
	
	
	
	
	/**
	 * Reset function to call to trigger "new post" for non-parsed content
	 *
	 * @access	public
	 * @return	@e void
	 */
	public function resetPerPost()
	{
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_images', 0 );
	}



	/**
	 * Check against XSS
	 *
	 * NOTE: When this function is updated, please also update classIncomingEmail::cleanMessage()
	 *
	 * @access	public
	 * @param	string		Original string
	 * @param	boolean		Fix script HTML tags
	 * @return	string		"Cleaned" text
	 */
	public function checkXss( $txt='', $fixScript=false, $tag='' )
	{
		//-----------------------------------------
		// Opening script tags...
		// Check for spaces and new lines...
		//-----------------------------------------
		
		if ( $fixScript )
		{
			$txt = preg_replace( '#<(\s+?)?s(\s+?)?c(\s+?)?r(\s+?)?i(\s+?)?p(\s+?)?t#is'        , "&lt;script" , $txt );
			$txt = preg_replace( '#<(\s+?)?/(\s+?)?s(\s+?)?c(\s+?)?r(\s+?)?i(\s+?)?p(\s+?)?t#is', "&lt;/script", $txt );
		}
				
		/* got a tag? */
		if ( $tag )
		{
			$tag = strip_tags( $tag, '<br>' );
			
			switch ($tag)
			{
				case 'entry':
				case 'blog':
				case 'topic':
				case 'post':
					$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
					if ( ! is_numeric( $test ) )
					{
						$txt	= false;
					}
				break;
				
				case 'acronym':
					$test  = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
					$test1 = str_replace( array( '<', ">", '[', ']' ), "", $test );//IPSText::alphanumericalClean( $test, '.+&#; ' );
					if ( $test != $test1 )
					{
						$txt	= false;
					}
				break;
				
				case 'email':
					$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
					$test = ( IPSText::checkEmailAddress( $test ) ) ? $txt : FALSE;
				break;
				
				case 'font':
					/* Make sure it's clean */
					$test = str_replace( array( '&amp;quot;', '"', "'", '&quot;', '&#39;' ), "", $txt );
					$test1 = IPSText::alphanumericalClean( $test, '#.+, ' );
					if ( $test != $test1 )
					{
						$txt = false;
					}
				break;
				case 'background':
				case 'color':
					/* Make sure it's clean */
					$test = str_replace( array( '&amp;quot;', '"', "'", '&quot;', '&#39;' ), "", $txt );
					
					/* Make rgb() safe */
					$test  = preg_replace( '#rgb(a)?\(([^\)]+?)\)#i', '', $test );
					
					$test1 = IPSText::alphanumericalClean( $test, '#.+, ' );
					if ( $test != $test1 )
					{
						$txt = false;
					}
				break;
				
				default:
					$_regex	  = null;
					$_bbcodes = $this->cache->getCache('bbcode');
					
					if ( !$txt and $_bbcodes[ $tag ]['bbcode_optional_option'] )
					{
						continue;
					}
					
					$_regex	  = $_bbcodes[ $tag ]['bbcode_custom_regex'];
										
					if( $_regex )
					{
						$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );

						if( !preg_match( $_regex, $test ) )
						{
							$txt	= false;
						}
					}
				break;
			}
			

			/* If we didn't actually get any option data, then return false */
			$test = str_replace( array( '"', "'", '&quot;', '&#39;' ), "", $txt );
			
			if ( strlen($txt) AND strlen( $test ) < 1 )
			{
				$txt = false;
			}

			if ( $txt === false )
			{
				return false;
			}
			
			/* Still here? Safety, then */
			$txt	= strip_tags( $txt, '<br>' );
			
			if( strpos( $txt, '[' ) !== false OR strpos( $txt, ']' ) !== false )
			{
				$txt	= str_replace( array( '[', ']' ), array( '&#91;', '&#93;' ), $txt );
			}
		}
		
		/* Attempt to make JS safe */
		$txt = IPSText::xssMakeJavascriptSafe( $txt );

		return $txt;
	}

	
	
	/**
	 * Check against blacklisted URLs
	 *
	 * @access	public
	 * @param 	string			Raw posted text
	 * @return	bool			False if blacklisted url present, otherwise true
	 */
	public function checkBlacklistUrls( $t )
	{
		if( !$t )
		{
			return true;
		}

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
							if( preg_match( '/' . $my_url . '/i', $t ) )
							{
								return false;
							}
						}
						else
						{
							if ( preg_match( '/' . $my_url . '/i', $t ) )
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
	 * Makes data for quote strings "safe"
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public function makeQuoteSafe( $txt='' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
	
		$begin	= '';
		$end	= '';
		
		//-----------------------------------------
		// Come via preg_replace_callback?
		//-----------------------------------------
		
		if ( is_array( $txt ) )
		{
			$begin = $txt[1];
			$end   = $txt[3];
			$txt   = $txt[2];
		}
		
		//-----------------------------------------
		// Sort name
		//-----------------------------------------
		
		$txt = str_replace( "+", "&#043;" , $txt );
		$txt = str_replace( "-", "&#045;" , $txt );
		$txt = str_replace( ":", "&#58;"  , $txt );
		$txt = str_replace( "[", "&#91;"  , $txt );
		$txt = str_replace( "]", "&#93;"  , $txt );
		$txt = str_replace( ")", "&#41;"  , $txt );
		$txt = str_replace( "(", "&#40;"  , $txt );
		$txt = str_replace( "'", "&#039;" , $txt );
		
		return $begin . $txt . $end;
	}
	
	/**
	 * Fix block level bbcodes
	 */
	protected function _cleanBlockBBCode( $txt )
	{
		foreach( $this->_blockBBcode as $tag )
		{
			/* <p>[spoiler]</p> */
			$txt = preg_replace( '#<(?:p|div)(?:[^>]+?)?>\[' . $tag . '([^\]]+?)?\]</(?:p|div)>#i', '[' . $tag . '$1]', $txt );
			
			/* <p>[/spoiler]</p> */
			$txt = preg_replace( '#<(?:p|div)(?:[^>]+?)?>\[/' . $tag . '\]</(?:p|div)>#i', '[/' . $tag . ']', $txt );
		}
		
		return $txt;
	}
	
	/**
	 * Finish URLs for display
	 * Truncates them, applies white/black lists, adds rel / targets
	 * @param	string	In
	 * @return	string	Out
	 */
	protected function _finishUrlsForDisplay( $txt )
	{
		/* If HTML mode, don't clean links */
		if ( parent::$Perms['parseHtml'] )
		{
			return $txt;
		}
		
		/* Reset counter */
		$this->cache->updateCacheWithoutSaving( '_tmp_bbcode_media', 0 );
		
		/* Parse media URLs that are NOT linked */
		$txt = preg_replace_callback( '#(^|\s|\)|\(|\{|\}|>|\]|\[|;|href=\S)((http|https|news|ftp)://(?:[^<>\)\[\"\s]+|[a-zA-Z0-9/\._\-!&\#;,%\+\?:=]+))(</a>)?#is', array( $this, '_parseMediaUrls_CallBack' ), $txt );
		
		/* LEGACY stuffs - a post from < 3.4 may not be HTMLised properly */
		if ( $this->_urlsEnabled === true && preg_match( '#(http|https)://#', $txt ) && ! stristr( $txt, '<a' ) )
		{
			$txt = $this->_autoLinkUrls( $txt );
		}

		preg_match_all( '#<a\s+?(?:[^>]*?)href=["\']([^"\']+?)?["\']([^>]*?)?>(.+?)</a>#is', $txt, $urlMatches );

		/* Finish up URLs and such */
		for( $i = 0 ; $i < count( $urlMatches[0] ) ; $i++ )
		{
			$raw	= $urlMatches[0][ $i ];
			$url	= $urlMatches[1][ $i ];
			$attr	= $urlMatches[2][ $i ];
			$text	= $urlMatches[3][ $i ];
			$done	= false;
			$pm		= true;
			
			preg_match( '#data-ipb=["\']([^"\']+?)?["\']#i', $raw, $matches );
			
			if ( $matches[1] && stristr( $matches[1], 'noparse' ) )
			{
				continue;
			}
			else if( $matches[1] && stristr( $matches[1], 'nomediaparse' ) )
			{
				$pm	= false;
			}
			
			preg_match( '#rel=["\']([^"\']+?)?["\']#i', $raw, $matches );
				
			if ( $matches[1] && stristr( $matches[1], 'lightbox' ) )
			{
				continue;
			}
			
			/* Urls disabled? */
			if ( $this->_urlsEnabled !== true )
			{
				$txt = str_replace( $raw, $url, $txt );
				continue;
			}
			
			/* Restored 1st March, Matt @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/some-previously-embedded-content-youtube-etc-now-showing-as-links-after-upgrade-r41411 */
			/* Is this a media URL? */
			/* Updated 14 May, http://community.invisionpower.com/resources/bugs.html/_/ip-board/url-tags-get-changed-to-media-tags-automatically-r40467 - 
				Editor now sets "noparsemedia" data-ipb attribute, which we can skip here for automatic parsing */
			if ( $pm AND $this->settings['bbcode_automatic_media'] and isset( $this->_bbcodes['media'] ) and ( $this->_bbcodes['media']['bbcode_sections'] == 'all' or in_array( parent::$Perms['parseArea'], explode( ',', $this->_bbcodes['media']['bbcode_sections'] ) ) ) )
			{
				$media = $this->cache->getCache( 'mediatag' );
					
				if ( $url == $text && is_array( $media ) AND count( $media ) )
				{
					foreach( $media as $type => $r )
					{
						if ( preg_match( "#^" . $r['match'] . "$#is", $url ) )
						{
							$this->cache->updateCacheWithoutSaving( '_tmp_autoparse_media', 1 );
							$_result = $this->_parseBBCode( '[media]' . $url . '[/media]', 'display', array( 'media' ) );
							$this->cache->updateCacheWithoutSaving( '_tmp_autoparse_media', 0 );
			
							$txt  = str_replace( $raw, $_result, $txt );
							$done = true;
						}
					}
				}
			}
				
			/* Format the URL */
			if ( $done !== true )
			{
				// -----------------------------------------
				// URL filtering?
				// -----------------------------------------
				
				if ( ! $this->isAllowedUrl( $url ) )
				{
					/* Unlink */
					$txt = str_replace( $raw, $url, $txt );
				}
				
				// -----------------------------------------
				// Let's remove any nested links..
				// -----------------------------------------
				
				$text = preg_replace( '/<a href=[\'"](.+?)[\'"](.*?)>(.+?)<\/a>/is', "\\3", $text );
				
				// -----------------------------------------
				// Need to "truncate" the "content" to ~35
				// EDIT: but only if it's the same as content
				// -----------------------------------------
				
				/*
				 * Changes here @link
				 * http://community.invisionpower.com/tracker/issue-36082-long-links-on-mobile-extend-width/   # V V Don't split if URL has entities V V #
				 */
				if ( ( empty( $this->settings['__noTruncateUrl'] ) ) and IPSText::mbstrlen( $text ) > 38 && ( ! preg_match( '#&\#([0-9]{2,4})#i', $text ) ) and ( substr( $text, 0, 7 ) == 'http://' or substr( $text, 0, 8 ) == 'https://' ) )
				{
					$text = htmlspecialchars( IPSText::mbsubstr( html_entity_decode( urldecode( $text ) ), 0, 20 ) ) . '...' . htmlspecialchars( IPSText::mbsubstr( html_entity_decode( urldecode( $text ) ), - 15 ) );
				}
				
				// -----------------------------------------
				// Adding rel='nofollow'?
				// -----------------------------------------
				
				$rels = array();
				$rel = '';
				$_title = '';
				
				/* Skipping VigLink? */
				if ( $this->settings['viglink_norewrite'] and IPSMember::isInGroup( parent::$Perms['memberData'], explode( ',', $this->settings['viglink_norewrite'] ) ) )
				{
					$rels[] = 'norewrite';
				}
				
				/* Fetch actual host for better matching */
				$data = @parse_url( $url );
				
				if ( $this->settings['posts_add_nofollow'] )
				{
					if ( ! stristr( $data['host'], $_SERVER['HTTP_HOST'] ) )
					{
						$rels[] = "nofollow";
					}
				}
				
				if ( $this->settings['links_external'] )
				{
					if ( ! stristr( $data['host'], $_SERVER['HTTP_HOST'] ) )
					{
						/* Look a little closer */
						$rels[] = "external";
						$_title = $this->lang->words['bbc_external_link'];
					}
				}
				
				if ( count( $rels ) )
				{
					$rel = " rel='" . implode( ' ', $rels ) . "'";
				}
				
				$replace = "<a href='{$url}' class='bbc_url' title='{$_title}'{$rel}>{$text}</a>";
				
				$txt = str_replace( $raw, $replace, $txt );
			}
		}
		
		return $txt;
	}
	
	/**
	 * HTML Auto link URLs
	 * @param string $html
	 */
	protected function _autoLinkUrls( $html )
	{
		if ( parent::$NoBBCodeAutoLinkify === true )
		{
			return $html;
		}
		
		/*
		 * Capture 'href="' and '</a>' as [URL] is now parsed first, we
		 * discard these in _autoParseUrls
		 */
		$html = preg_replace_callback( '#(^|\s|\)|\(|\{|\}|/>|>|\]|\[|;|href=\S)((http|https|news|ftp)://(?:[^<>\)\[\"\s]+|[a-zA-Z0-9/\._\-!&\#;,%\+\?:=]+))(</a>)?#is', array( $this, '_autoLinkUrls_CallBack' ), $html );
		
		return $html;
	}
	
	/**
	 * Callback to auto-parse media urls
	 *
	 * @access	protected
	 * @param	array		Matches from the regular expression
	 * @return	string		Converted text
	 */
	protected function _parseMediaUrls_CallBack( $matches )
	{
		$_extra		= '';
	
		/* Basic checking */
		if ( stristr( $matches[1], 'href' ) )
		{
			return $matches[0];
		}
	
		if( strlen( $matches[2] ) < 12 )
		{
			return $matches[0];
		}
	
		if ( isset( $matches[4] ) AND stristr( $matches[4], '</a>' ) )
		{
			return $matches[0];
		}
	
		/* Check for XSS */
		if ( ! IPSText::xssCheckUrl( $matches[2] ) )
		{
			return $matches[0];
		}
	
		if( substr( $matches[2], -1 ) == ',' )
		{
			$matches[2]	= rtrim( $matches[2], ',' );
			$_extra		= ',';
		}
	
		/* Check for ! which is &#xx; at this point */
		if( preg_match( '/&#\d+?;$/', $matches[2], $_m ) )
		{
			$matches[2]	= str_replace( $_m[0], '', $matches[2] );
			$_extra		= $_m[0];
		}
	
		/* Is this a media URL? */
		if( $this->settings['bbcode_automatic_media'] and isset( $this->_bbcodes['media'] ) and ( $this->_bbcodes['media']['bbcode_sections'] == 'all' or in_array( parent::$Perms['parseArea'], explode( ',', $this->_bbcodes['media']['bbcode_sections'] ) ) ) )
		{
			$media		= $this->cache->getCache( 'mediatag' );
				
			/* Already converted? */
			if ( in_array( $matches[2], $this->_mediaUrlConverted ) )
			{
				return $matches[0];
			}
				
			if ( is_array( $media ) AND count( $media ) )
			{
				foreach( $media as $type => $r )
				{
					if ( preg_match( "#^" . $r['match'] . "$#is", $matches[2] ) )
					{
						$this->cache->updateCacheWithoutSaving( '_tmp_autoparse_media', 1 );
						$_result	= $this->_parseBBCode( $matches[1] . '[media]' . $matches[2] . '[/media]' . $_extra, 'html', array( 'media' ) );
						$this->cache->updateCacheWithoutSaving( '_tmp_autoparse_media', 0 );
	
						return $_result;
					}
				}
			}
		}
		
		return $matches[1] . $matches[2] .  $_extra;
	}
	
	/**
	 * Callback to auto-parse urls
	 *
	 * @access	protected
	 * @param	array		Matches from the regular expression
	 * @return	string		Converted text
	 */
	protected function _autoLinkUrls_CallBack( $matches )
	{
		$_extra		= '';

		/* Basic checking */
		if ( stristr( $matches[1], 'href' ) || $matches[1] == '>' )
		{
			return $matches[0];
		}
		
		if( strlen( $matches[2] ) < 12 )
		{
			return $matches[0];
		}

		if ( isset( $matches[4] ) AND stristr( $matches[4], '</a>' ) )
		{
			return $matches[0];
		}
		
		/* Check for XSS */
		if ( ! IPSText::xssCheckUrl( $matches[2] ) )
		{
			return $matches[0];
		}
		
		if( substr( $matches[2], -1 ) == ',' )
		{
			$matches[2]	= rtrim( $matches[2], ',' );
			$_extra		= ',';
		}
		
		if( substr( $matches[2], -15 ) == '~~~~~_____~~~~~' )
		{
			$matches[2]	= substr( $matches[2], 0, -15 );
			$_extra		= '~~~~~_____~~~~~';
		}
		
		/* Check for ! which is &#xx; at this point */
		if( preg_match( '/&#\d+?;$/', $matches[2], $_m ) )
		{
			$matches[2]	= str_replace( $_m[0], '', $matches[2] );
			$_extra		= $_m[0];
		}

		/* Is this a media URL? */
		if( $this->settings['bbcode_automatic_media'] and isset( $this->_bbcodes['media'] ) and ( $this->_bbcodes['media']['bbcode_sections'] == 'all' or in_array( parent::$Perms['parseArea'], explode( ',', $this->_bbcodes['media']['bbcode_sections'] ) ) ) )
		{
			$media		= $this->cache->getCache( 'mediatag' );
			
			/* Already converted? */
			if ( in_array( $matches[2], $this->_mediaUrlConverted ) )
			{
				return $matches[0];
			}
			
	       	if ( is_array( $media ) AND count( $media ) )
			{
				foreach( $media as $type => $r )
				{
					if ( preg_match( "#^" . $r['match'] . "$#is", $matches[2] ) )
					{
						$this->cache->updateCacheWithoutSaving( '_tmp_autoparse_media', 1 );
						$_result	= $this->_parseBBCode( $matches[1] . '[media]' . $matches[2] . '[/media]' . $_extra, 'html', array( 'media' ) );
						$this->cache->updateCacheWithoutSaving( '_tmp_autoparse_media', 0 );
						
						return $_result;
					}
				}
			}
		}
	
		/* It's not media - so we'll use [url] - check we're allowed first */
		if ( ! isset( $this->_bbcodes['url'] ) or ( $this->_bbcodes['url']['bbcode_sections'] != 'all' and ! in_array( parent::$Perms['parseArea'], explode( ',', $this->_bbcodes['url']['bbcode_sections'] ) ) ) )
		{
			// We're not allowed to use [url] here
			return $matches[0];
		}

		/* Ensure bbcode is stripped for the actual URL */
		/* @link http://community.invisionpower.com/tracker/issue-22580-bbcode-breaks-link-add-bold-formatting-to-part-of-link/ */
		if ( preg_match( '#\[\w#', $matches[2] ) )
		{
			$wFormatting = $matches[2];
			$matches[2]  = $this->stripAllTags( $matches[2] );

			return $this->_parseBBCode( $matches[1] . '[url="' . $matches[2] . '"]' . $wFormatting . '[/url]' . $_extra, 'html', array( 'url' ) );
		}
		else
		{
			/* Is option enforced? */
			if ( empty( $this->_bbcodes['url']['bbcode_optional_option'] ) )
			{
				return $this->_parseBBCode( $matches[1] . '[url="' . $matches[2] . '"]' . $matches[2] . '[/url]' . $_extra, 'html', array( 'url' ) );
			}
			else
			{
				return $this->_parseBBCode( $matches[1] . '[url]' . $matches[2] . '[/url]' . $_extra, 'html', array( 'url' ) );
			}
		}
	}

	
	/**
	 * Reset bbcode internal pointers
	 *
	 * @access	protected
	 * @return	@e void
	 */
	protected function _resetPointers()
	{
		$this->error			= '';
		$this->image_count		= 0;
		$this->emoticon_count	= 0;
	}
	
	/**
	 * Remove session keys from URLs
	 *
	 * @access	protected
	 * @param	array		Array of matches
	 * @return	string		Converted text
	 */
	protected function _bashSession( $matches=array() )
	{
		$start_tok	= str_replace( '&amp;', '&', $matches[1] );
		$end_tok	= str_replace( '&amp;', '&', $matches[3] );
	
		if ( ( $start_tok == '?' OR $start_tok == '&' ) and $end_tok == '')
		{
			return '';
		}
		else if ( $start_tok == '?' and $end_tok == '&' )
		{
			return '?';
		}
		else if ( $start_tok == '&' and $end_tok == '&' )
		{
			return "&";
		}
		else
		{
			return $start_tok . $end_tok;
		}
	}
	
	
}