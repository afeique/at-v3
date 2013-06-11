<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Editor Library: RTE (WYSIWYG) Class
 * Last Updated: $Date: 2013-05-21 20:51:17 -0400 (Tue, 21 May 2013) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		9th March 2005 11:03
 * @version		$Revision: 12263 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * In an effort to simplify all the bbcode routines:
 * 
 * This class will take CKEditor HTML, clean it up and pass it on.
 * This means [b] will be converted to <strong>, URLs auto-linked and so on.
 * App specific BBCode ([entry] [topic]) and complex BBCode (CODE, QUOTE, MEDIA) are not parsed.
 * 
 * Likewise, when showing the editor, you should supply it with data in the same format: that is, parsed HTML
 * with complex app specific BBCode unparsed. A good example of this would be:
 * <strong>I am strong!</strong><br />[quote]I am a quote![/quote]
 * 
 *  -- Legacy Issues --
 * If the setContent method detects legacy markup (that is, unparsed basic BBCode such as [b]) then it
 * will first convert it to the standard that it expects for input (HTML parsed, complex or app specific bbcode not parsed)
 * 
 * If setLegacyMode is true, this class will return BBCode mark-up as expected of 3.3.x and older.
 * This is recommended for code in transition as it's largely buggy and rather annoying.
 * 
 * 
 * 
 * 
 * Test input: < > ' " & ¦ $ \ ! &amp; &#39; &quot; &lt; &gt; &para;
 * Should be processed as: &lt; &gt; &#39; &quot; &amp; ¦ $ &#92; ! &amp;amp; &amp;#39; &amp;quot; &amp;lt; &amp;gt; &amp;para;
 */
/**
 * 
 * IP.Board PHP work to display an editor
 * @author Matt
 *
 * Example:
 * $editor = new classes_editor_composite();
 * $editor->setAllowBbcode( true );
 * $editor->setAllowHtml( false );
 * $editor->setContent( '[b]Hello, I am some text from the database[/b]' );
 * $html = $editor->show('post_content');
 */
class classes_editor_composite
{
	/**
	 * Legacy Mode enabled/disabled
	 * @var 	boolean
	 * @since	3.4
	 */
	private $_legacyMode = false;
	
	
	
	
	/**
	 * Use P for line breaks (std CKEditor mode)
	 */
	const IPS_P_MODE = true;
	
	/**
	 * Parsing array
	 *
	 * @access	public
	 * @var		array
	 */
	public $delimiters			= array( "'", '"' );
	
	/**
	 * Parsing array
	 *
	 * @access	public
	 * @var		array
	 */
	public $non_delimiters		= array( "=", ' ' );
	
	/**
	 * Start tags
	 *
	 * @access	public
	 * @var		array
	 */
	public $start_tags			= array();
	
	/**
	 * End tags
	 *
	 * @access	public
	 * @var		array
	 */
	public $end_tags			= array();

	/**
	 * Cached auto-save content.  Multiple editors on a page cause the auto-save query to be run once per editor unnecessarily.
	 *
	 * @var		array
	 */
	public $cachedAutosave		= array();
	
	/**#@+
	* Internal setting objects
	*
	* @var		boolean
	*/
	protected $isHtml			= null;
	protected $allowHtml		= null;
	protected $allowBbcode		= true;
	protected $bbcodeSection    = 'topics';
	protected $content			= null;
	protected $rteEnabled		= false;
	protected $allowSmilies		= true;
	protected $bypassHtmlPurify = false;
	protected $forceRte			= null;
	protected $_edCount			= 1;
	/**#@+
	* Registry objects
	*
	* @var		object
	*/	
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $memberData;
	protected $cache;
	protected $caches;
	protected $_parsingErrors = array();

	/**
	 * Constructor
	 *
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
		
		/* Grab the parser file */
		require_once( IPS_ROOT_PATH . 'sources/classes/text/parser.php');
		
		/* Grab HTMLPurifier */
		require_once( IPS_KERNEL_PATH . 'HTMLPurifier/HTMLPurifier.auto.php' );
		
		/* Auto set the RTE */
		$this->setRteEnabled( $this->_canWeRte() );
		
		/* Auto set legacy mode */
		$this->setLegacyMode( true );
		
		/* Set up default options */
		$this->setAllowBbcode( true );
		$this->setAllowSmilies( true );
		$this->setAllowHtml( false );
	}
	
	/**
	 * @return the $forceRte
	 */
	public function getForceRte()
	{
		return $this->forceRte;
	}
	
	/**
	 * Sometimes we want to force the RTE to on for other methods
	 * that require processing but may be triggered by mobiles, etc.
	 * @param boolean $forceRte
	 */
	public function setForceRte( $forceRte )
	{
		$this->forceRte = (boolean) $forceRte;
	}
	
	/**
	 * @return the $bypassHtmlPurify
	 */
	public function getBypassHtmlPurify()
	{
		return $this->bypassHtmlPurify;
	}
	
	/**
	 * @param boolean $bypassHtmlPurify
	 */
	public function setBypassHtmlPurify( $bypassHtmlPurify )
	{
		$this->bypassHtmlPurify = (boolean) $bypassHtmlPurify;
	}
	
	/**
	 * @return the $_parsingErrors
	 */
	public function getParsingErrors()
	{
		return $this->_parsingErrors;
	}
	
	/**
	 * @param field_type $_parsingErrors
	 */
	public function setParsingErrors( $_parsingErrors )
	{
		$this->_parsingErrors = $_parsingErrors;
	}
	
	/**
	 * Set legacy mode
	 * @since	3.4
	 * @param	boolean
	 */
	public function setLegacyMode( $boolean )
	{
		$this->_legacyMode = (bool) $boolean;
		
		if ( $this->_legacyMode === true )
		{
			if ( ! class_exists( 'class_text_parser_legacy' ) )
			{
				require_once( IPS_ROOT_PATH . 'sources/classes/text/parser/legacy.php');
			}
		}
	}
	
	/**
	 * Set legacy mode
	 * @since	3.4
	 * @param	boolean
	 */
	public function getLegacyMode()
	{
		return $this->_legacyMode;
	}
	
	/**
	 * @return the $bbcodeSection
	 */
	public function getBbcodeSection()
	{
		return $this->bbcodeSection;
	}
	
	/**
	 * @param string $bbcodeSection
	 */
	public function setBbcodeSection( $bbcodeSection )
	{
		$this->bbcodeSection = $bbcodeSection;
	}
	
	/**
	 * @return the $isHtml
	 */
	public function getIsHtml()
	{
		return $this->isHtml;
	}
	
	/**
	 * @return the $isHtml
	 */
	public function setIsHtml( $isHtml )
	{
		$this->isHtml = $isHtml ? true : false;
		
		if ( $isHtml )
		{
			$this->setAllowHtml( true );
			$this->setBypassHtmlPurify( true );
		}
	}
	
	/**
	 * @return the $allowSmilies
	 */
	public function getAllowSmilies()
	{
		return $this->allowSmilies;
	}

	/**
	 * @return the $allowHtml
	 */
	public function getAllowHtml()
	{
		return $this->allowHtml;
	}

	/**
	 * @return the $allowBbcode
	 */
	public function getAllowBbcode()
	{
		return $this->allowBbcode;
	}

	/**
	 * @return the $content
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * @return the $rteEnabled
	 */
	public function getRteEnabled()
	{
		return $this->rteEnabled;
	}
	
	/**
	 * @param boolean $allowHtml
	 */
	public function setAllowHtml( $allowHtml )
	{
		/* No mechanics in the mobile skin to deal with this */
		if ( $this->registry->output->getAsMobileSkin() )
		{
			$allowHtml = false;
		}
		
		$this->allowHtml = $allowHtml ? true : false;
	}

	/**
	 * @param boolean $allowBbcode
	 */
	public function setAllowBbcode( $allowBbcode )
	{
		$this->allowBbcode = $allowBbcode ? true : false;
	}

	/**
	 * @param string $content
	 * @param string BBCode parsing section
	 */
	public function setContent( $content, $section='topics' )
	{
		/* Legacy? So is probably BBCode? Ew @todo remove for IPS 4 */
		if ( $this->getLegacyMode() )
		{
			$parser = new class_text_parser_legacy();
			$this->passSettings( $parser );
			
			$content = $parser->preEditor( $content );
		}

		/* @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/url-blacklist-inconsistencies-r42298 */
		$content = $this->stripBlacklistedUrls( $content );

		/* Make {style_images_url} safe */
		$content = str_replace( '{style_images_url}', '&#123;style_images_url}', $content );
		
		$this->content = $content;
	}

	/**
	 * Strip blacklisted URLs
	 * This should really be in the parser (and in reality we shouldn't store the URL parsed and strip on display), but restructuring the code like that at this stage isn't safe
	 *
	 * @todo 4.0 - don't parse blacklisted URLs during save and then strip on display.  Just don't parse to begin with.
	 * @param	string	Text
	 * @return	string	Text
	 */
	protected function stripBlacklistedUrls( $txt )
	{
		$parser       			 = $this->_newParserObject();

		preg_match_all( '#<a\s+?(?:[^>]*?)href=["\']([^"\']+?)?["\']([^>]*?)?>(.+?)</a>#is', $txt, $urlMatches );

		/* Finish up URLs and such */
		for( $i = 0 ; $i < count( $urlMatches[0] ) ; $i++ )
		{
			$raw	= $urlMatches[0][ $i ];
			$url	= $urlMatches[1][ $i ];

			if( !$parser->isAllowedUrl( $url ) )
			{
				$txt = str_replace( $raw, $url, $txt );
			}
		}

		return $txt;
	}

	/**
	 * @param boolean $rteEnabled
	 */
	public function setRteEnabled( $rteEnabled )
	{
		$this->rteEnabled = $rteEnabled ? true : false;
	}
	
	/**
	 * @param boolean $allowSmilies
	 */
	public function setAllowSmilies( $allowSmilies )
	{
		$this->allowSmilies = $allowSmilies ? true : false;
	}
	
	/**
	 * Shows the editor
	 * print $editor->show( 'message', 'reply-topic-1244' );
	 * @param	string	Field
	 * @param	array   Options: Auto save key, a unique key for the page. If supplied, editor will auto-save at regular intervals. Works for logged in members only
	 * @param	string	Optional content
	 */
	public function show( $fieldName, $options=array(), $content='' )
	{
		$showEditor = TRUE;
	
		/* Have we forced RTE? */
		if ( ! empty( $this->request['isRte'] ) )
		{
			$options['isRte'] = intval( $this->request['isRte'] );
		}
		
		$_autoSaveKeyOrig		     = ( ! empty( $options['autoSaveKey'] ) ) ? $options['autoSaveKey'] : '';
		$options['editorName']       = ( ! empty( $options['editorName'] ) ) ? $options['editorName'] : $this->_fetchEditorName();
		$options['autoSaveKey']      = ( $_autoSaveKeyOrig && $this->memberData['member_id'] ) ? $this->_generateAutoSaveKey( $_autoSaveKeyOrig ) : '';
		$options['type']             = ( ! empty( $options['type'] ) && $options['type'] == 'mini' ) ? 'mini' : 'full';
		$options['minimize']	     = intval( $options['minimize'] );
		$options['height']	     	 = intval( $options['height'] );
		$options['isTypingCallBack'] = ( ! empty( $options['isTypingCallBack'] ) ) ? $options['isTypingCallBack'] : '';
		$options['noSmilies']		 = ( ! empty( $options['noSmilies'] ) ) ? true : false;
		$options['delayInit']		 = ( ! empty( $options['delayInit'] ) ) ? 1 : 0;
		$options['smilies']          = $this->fetchEmoticons();
		$options['bypassCKEditor']   = ( ! empty( $options['bypassCKEditor'] ) ) ? 1 : ( $this->getRteEnabled() ? 0 : 1 );
		$options['legacyMode']		 = ( ! empty( $options['legacyMode'] ) ) ? $options['legacyMode'] : 'on';
		$html         = '';

		/* Fetch disabled tags */
		$parser       			 = $this->_newParserObject();
		$options['disabledTags'] = $parser->getDisabledTags();
		
		$this->setLegacyMode( ( $options['legacyMode'] == 'on' ) ? true : false );

		if ( isset( $options['recover'] ) )
		{
			$content = $_POST['Post'];
		}
		
		/* Try and sniff out entered HTML */
		if ( IN_ACP AND empty( $options['isHtml'] ) )
		{
			$options['isHtml'] = intval( $this->_tryAndDetermineHtmlStatusTheHackyWay( $content ? $content : $this->getContent() ) );
		}
		
		if ( ! empty( $options['isHtml'] ) )
		{
			$this->setIsHtml( true );
			
			if ( IN_ACP )
			{
				$options['type'] = 'ipsacp';
			}
		}
		else if ( $this->getIsHtml() )
		{
			$options['isHtml'] = 1;
		}
		
		/* inline content */
		if ( $content )
		{
			$this->setContent( ( $this->getLegacyMode() ) ? str_replace( '\\\'', '\'', $content ) : $content );
		}
	
		/* Is this legacy bbcode?  If we are using RTE, we need to send HTML.
			@link http://community.invisionpower.com/resources/bugs.html/_/ip-board/old-style-image-links-do-not-parse-in-editor-r42078 */
		if( $parser->isBBCode( $this->getContent() ) )
		{
			$this->setContent( $parser->htmlToEditor( $this->getContent() ) );
		}

		/* Store last editor ID in case calling scripts need it */
		$this->settings['_lastEditorId']	= $options['editorName'];

		if ( IN_ACP )
  		{
  			$html = $this->registry->getClass('output')->global_template->editor( $fieldName, $this->getContent(), $options, $this->getAutoSavedContent( $_autoSaveKeyOrig ) );
		}
		else
		{
			$warningInfo = '';
			$acknowledge = FALSE;
			
			//-----------------------------------------
			// Warnings
			//-----------------------------------------
			
			if ( isset( $options['warnInfo'] ) and $this->memberData['member_id'] )
			{
				$message = '';
				
				/* Have they been restricted from posting? */
				if ( $this->memberData['restrict_post'] )
				{
					$data = IPSMember::processBanEntry( $this->memberData['restrict_post'] );
					if ( $data['date_end'] )
					{
						if ( time() >= $data['date_end'] )
						{
							IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'restrict_post' => 0 ) ) );
						}
						else
						{
							$message = sprintf( $this->lang->words['warnings_restrict_post_temp'], $this->lang->getDate( $data['date_end'], 'JOINED' ) );
						}
					}
					else
					{
						$message = $this->lang->words['warnings_restrict_post_perm'];
					}
					
					if ( $this->memberData['unacknowledged_warnings'] )
					{
						$warn = ipsRegistry::DB()->buildAndFetch( array( 'select' => '*', 'from' => 'members_warn_logs', 'where' => "wl_member={$this->memberData['member_id']} AND wl_rpa<>0", 'order' => 'wl_date DESC', 'limit' => 1 ) );
						if ( $warn['wl_id'] )
						{
							$moredetails = "<a href='javascript:void(0);' onclick='warningPopup( this, {$warn['wl_id']} )'>{$this->lang->words['warnings_moreinfo']}</a>";
						}
					}
					
					if ( $options['warnInfo'] == 'full' )
					{
						$this->registry->getClass('output')->showError( "{$message} {$moredetails}", 103126, null, null, 403 );
					}
					else
					{
						$showEditor = FALSE;
					}
				}
				
				/* Nope? - Requires a new if in case time restriction got just removed */
				if ( empty($message) )
				{
					/* Do they have any warnings they have to acknowledge? */
					if ( $this->memberData['unacknowledged_warnings'] )
					{
						$unAcknowledgedWarns = ipsRegistry::DB()->buildAndFetch( array( 'select' => '*', 'from' => 'members_warn_logs', 'where' => "wl_member={$this->memberData['member_id']} AND wl_acknowledged=0", 'order' => 'wl_date DESC', 'limit' => 1 ) );
						if ( $unAcknowledgedWarns['wl_id'] )
						{
							if ( $options['warnInfo'] == 'full' )
							{
								$this->registry->getClass('output')->silentRedirect( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=profile&amp;section=warnings&amp;do=acknowledge&amp;id={$unAcknowledgedWarns['wl_id']}" ) );
							}
							else
							{
								$this->lang->loadLanguageFile( 'public_profile', 'members' );
								$acknowledge = $unAcknowledgedWarns['wl_id'];
							}
						}
					}
					
					/* No? Are they on mod queue? */
					if ( $this->memberData['mod_posts'] )
					{
						$data = IPSMember::processBanEntry( $this->memberData['mod_posts'] );
						if ( $data['date_end'] )
						{
							if ( time() >= $data['date_end'] )
							{
								IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'mod_posts' => 0 ) ) );
							}
							else
							{
								$message = sprintf( $this->lang->words['warnings_modqueue_temp'], $this->lang->getDate( $data['date_end'], 'JOINED' ) );
							}
						}
						else
						{
							$message = $this->lang->words['warnings_modqueue_perm'];
						}
						
						if ( $message )
						{
							$warn = ipsRegistry::DB()->buildAndFetch( array( 'select' => '*', 'from' => 'members_warn_logs', 'where' => "wl_member={$this->memberData['member_id']} AND wl_mq<>0", 'order' => 'wl_date DESC', 'limit' => 1 ) );
							if ( $warn['wl_id'] )
							{
								if ( $this->registry->output->getAsMobileSkin() )
								{
									$moredetails = "<a href='{$this->registry->getClass('output')->buildUrl( "app=members&amp;module=profile&amp;section=warnings" )}'>{$this->lang->words['warnings_moreinfo']}</a>";
								}
								else 
								{
									$moredetails = "<a href='javascript:void(0);' onclick='warningPopup( this, {$warn['wl_id']} )'>{$this->lang->words['warnings_moreinfo']}</a>";
								}
							}
						}
					}
					
					/* How about our group? - Requires a new if in case mod queue restriction got just removed */
					if ( empty($message) && $this->memberData['g_mod_preview'] )
					{
						/* Do we only limit for x posts/days? */
						if ( $this->memberData['g_mod_post_unit'] )
						{
							if ( $this->memberData['gbw_mod_post_unit_type'] )
							{
								/* Days.. .*/
								if ( $this->memberData['joined'] > ( time() - ( 86400 * $this->memberData['g_mod_post_unit'] ) ) )
								{
									$message = sprintf( $this->lang->words['ms_mod_q'] . ' ' . $this->lang->words['ms_mod_q_until'], $this->lang->getDate( $this->memberData['joined'] + ( 86400 * $this->memberData['g_mod_post_unit'] ), 'long' ) );
								}
							}
							else
							{
								/* Posts */
								if ( $this->memberData['posts'] < $this->memberData['g_mod_post_unit'] )
								{
									$message = sprintf( $this->lang->words['ms_mod_q'] . ' ' . $this->lang->words['ms_mod_q_until_posts'], $this->memberData['g_mod_post_unit'] - $this->memberData['posts'] );
								}
							}
						}
						else
						{
							/* No limit, but still checking moderating */
							$message = $this->lang->words['ms_mod_q'];
						}
					}
					/* Or just everyone? */
					elseif ( $options['modAll'] and !$this->memberData['g_avoid_q'] )
					{
						$message = $this->lang->words['ms_mod_q'];
					}
				}

				if ( $message )
				{
					$warningInfo = "{$message} {$moredetails}";
				}
			}
			
			//-----------------------------------------
			// Show the editor
			//-----------------------------------------

			$parser = new class_text_parser_legacy();
			$this->passSettings( $parser );
			
			/* Mobile skin / app? */
			if ( $this->_canWeRte( true ) !== true || $this->registry->output->getAsMobileSkin() )
			{
				$content = $this->toPlainTextArea( $this->getContent() );
			}
			else
			{
				/* CKEditor decodes HTML entities */
				$content = str_replace( '&', '&amp;', $this->getContent() );
				
				/* Take a stab at fixing up manually entered CODE tag */
				//$content = $this->_fixManuallyEnteredCodeBoxesIntoRte( $content );
				
				/* Convert to BBCode for non JS peoples */
				$content = $parser->htmlToEditor( $content );
			}
			
			$bbcodeVersion = '';
			
			if ( $content )
			{
				$bbcodeVersion = $this->toPlainTextArea( $parser->postEditor( $content ) );
			}
			
			$html = $this->registry->getClass('output')->getTemplate('editors')->editor( $fieldName, $content, $options, $this->getAutoSavedContent( $_autoSaveKeyOrig ), $warningInfo, $acknowledge, $bbcodeVersion, $showEditor );
		}
		
		return $html;
	}
	
	/**
	 * Text passed from a plain text editor
	 * @param string $content
	 */
	public function fromPlainTextArea( $content )
	{
		/* PHP htmlspecialchars() method can return empty string, causing "You must enter a post" error erroneously */
		$content = IPSText::htmlspecialchars( $content );

		$parser = new class_text_parser_legacy();
		$this->passSettings( $parser );
			
		$content = $parser->preEditor( $content );
		
		/* When there is BBcode, it is processed and linebreaks turn to BR so watch for this */
		if ( ! preg_match( '#<br([^>]+?)?>#i', $content ) )
		{
			$content = nl2br( $content );
		}
		
		return $content;
	}

	/**
	 * We need to pass settings to the new object
	 *
	 * @param	object	Parser object
	 * @return	void
	 */
	protected function passSettings( $obj )
	{
		$obj->set(
				array(
					'parseBBCode'		=> $this->getAllowBbcode(),
					'parseHtml'			=> $this->getAllowHtml(),
					'parseEmoticons'	=> $this->getAllowSmilies()
					)
				);
	}
	
	/**
	 * Text passed to a plain text editor
	 * @param string $content
	 */
	public function toPlainTextArea( $content )
	{
		$parser = new class_text_parser_legacy();
		$this->passSettings( $parser );

		$content = $parser->postEditor( $content );
		
		// @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/encoded-text-is-replaced-when-quoting-on-mobile-devices-r40540
		//$content = str_replace( '&amp;', '&', $content );
		
		$content = IPSText::br2nl( $content );
		
		return $content;
	}
	
	/**
	 * Process contents of RTE into BBCode ready for storing
	 * @param  string	$content
	 * @return string	$content
	 */
	public function process( $content )
	{
		if ( IN_ACP AND ! $this->getIsHtml() )
		{
			$result = $this->_tryAndDetermineHtmlStatusTheHackyWay( $content );

			if ( $result === true )
			{
				$this->setIsHtml( true );
			}
		}
		
		/* Mobile skin / app? */
		if ( $this->_canWeRte() !== true || $this->registry->output->getAsMobileSkin() || $_REQUEST['noCKEditor'] )
		{
			/* Some areas of IPB add quotes as HTML so turn it into BBCode so fromPlainTextArea can convert it back instead of htmlizing the < > chars */
			if ( stristr( $content, '<blockquote' ) )
			{ 
				$content = $this->toPlainTextArea( $content );
			}
			
			$content = $this->fromPlainTextArea( $content );
		}
		
		/* Is this HTML? */
		if ( $this->getIsHtml() )
		{
			$content = $this->_htmlize( $content );
		}
		
		/* New object */
		$purifier = $this->_newPurifierObject();
		
		/* Prep it */
		$content = $this->_prePurify( $content );
		
		/* Clean it */
		if ( $this->getBypassHtmlPurify() !== true )
		{
			$cleanContent = $purifier->purify( $content );

			/* Remove negative margins */
			$cleanContent	= preg_replace( '/margin\-left:\s*?\-([^;$]+?)(;|$)/', "$2", $cleanContent );
		}
		else
		{
			$cleanContent = $content;
		}

		/* Tweak it */
		$cleanContent = $this->_postPurify( $cleanContent );

		/* Legacy? So expects BBCode? Ew @todo remove for IPS 4 */
		if ( $this->getLegacyMode() )
		{
			$parser = new class_text_parser_legacy();
			$this->passSettings( $parser );
			
			$cleanContent = $parser->postEditor( $cleanContent );
		}

		/* Test for errors */
		$parser = $this->_newParserObject();
		
		if ( $this->getIsHtml() !== true && $parser->testForParsingLimits( $content ) !== true )
		{
			$this->setParsingErrors( $parser->getErrors() );
		}

		$cleanContent = $this->protectMedia( $cleanContent );
		
		/* Like nice neat code? Look away now */
		if ( $this->getIsHtml() AND IN_ACP )
		{
			$cleanContent = '<!-- isHtml:1 -->' . $cleanContent;
		}
		
		/* Reset */
		$this->setIsHtml( false );
		
		/* Return it */
		return $cleanContent;
	}
	
	/**
	 * Runs HTMLPurify only
	 * @param  string	$content
	 * @return string	$content
	 */
	public function htmlPurify( $content )
	{
		/* New object */
		$purifier = $this->_newPurifierObject();
		
		/* Prep it */
		$content = $this->_prePurify( $content );
		
		
		$cleanContent = $purifier->purify( $content );

		/* Remove negative margins */
		$cleanContent	= preg_replace( '/margin\-left:\s*?\-([^;$]+?)(;|$)/', "$2", $cleanContent );
				
		/* Tweak it */
		$cleanContent = $this->_postPurify( $cleanContent );
				
		/* Return it */
		return $cleanContent;
	}
	
	/**
	 * Fetch emoticons as JSON for editors, etc
	 *
	 * @param	mixed		Number of emoticons to fetch (false to fetch all, or an int limit)
	 * @return	string		JSON
	 */
	public function fetchEmoticons( $fetchFirstX = false )
	{
		$emoDir = IPSText::getEmoticonDirectory();
		$emoString = '';
		$smilie_id = 0;
		$total = 0;
		
		foreach( ipsRegistry::cache()->getCache( 'emoticons' ) as $elmo )
		{
			if ( $elmo['emo_set'] != $emoDir )
			{
				continue;
			}
			
			$total ++;
			
			if ( $fetchFirstX !== false && ( $smilie_id + 1 > $fetchFirstX ) )
			{
				continue;
			}
			
			// -----------------------------------------
			// Make single quotes as URL's with html entites in them
			// are parsed by the browser, so ' causes JS error :o
			// -----------------------------------------
			
			if ( strstr( $elmo['typed'], "&#39;" ) )
			{
				$in_delim = '"';
			}
			else
			{
				$in_delim = "'";
			}
			
			$emoArray[$smilie_id] = array( 'src' => $elmo['image'], 'text' => addslashes( $elmo['typed'] ) );
			
			$smilie_id ++;
		}
		
		return array( 'total' => $total, 'count' => $smilie_id, 'emoticons' => $emoArray );
	}
	

	/**
	 * Fetches the saved content
	 * @param string $autoSaveKey
	 * @return array
	 */
	public function getAutoSavedContent( $autoSaveKey )
	{
		$autoSaveKey = $this->_generateAutoSaveKey( $autoSaveKey );

		if( isset($this->cachedAutosave[ $autoSaveKey ]) )
		{
			return $this->cachedAutosave[ $autoSaveKey ];
		}

		$return      = array();
		$parser      = $this->_newParserObject();
		
		/* fetch from the dee bee */
		$raw = $this->DB->buildAndFetch( array( 'select' => '*',
												'from'   => 'core_editor_autosave',
												'where'  => 'eas_key=\'' . $autoSaveKey . '\'' ) );
		
		/* Make sure no tomfoolery is occuring */
		if ( $raw['eas_key'] && ( $this->memberData['member_id'] == $raw['eas_member_id'] ) )
		{
			$return['key']         = $raw['eas_key'];
			$return['updated']     = $raw['eas_updated'];
			$return['raw']         = $raw['eas_content'];
			$return['updatedDate'] = $this->registry->getClass('class_localization')->getDate( $return['updated'], 'LONG' );
			
			/* Now figure out previewable content */
			$return['parsed']  = $parser->display( $raw['eas_content'] );
		}

		$this->cachedAutosave[ $autoSaveKey ]	= $return;
		
		return $return;
	}
	
	/**
	 * Remove auto saved content
	 * @param array $where options member_id = x , app = x, time = x 
	 */
	public function removeAutoSavedContent( $where=array() )
	{
		$_sql = array();
		
		if ( ! count( $where ) )
		{
			$_sql[] = 'eas_app=\'' . IPS_APP_COMPONENT . '\'';
		}
		
		if ( ! empty( $where['app'] ) )
		{
			$_sql[] = 'eas_app=\'' . $this->DB->addSlashes( $where['app'] ) . '\'';
		}
		
		if ( ! empty( $where['member_id'] ) )
		{
			$_sql[] = 'eas_member_id=' . intval( $where['member_id'] );
		}
		
		if ( ! empty( $where['autoSaveKey'] ) )
		{
			if ( strlen( $where['autoSaveKey'] ) != 32 )
			{
				$where['autoSaveKey'] = $this->_generateAutoSaveKey( $where['autoSaveKey'] );
			}
			
			$_sql[] = 'eas_key=\'' . trim( $where['autoSaveKey'] ) . '\'';
		}
		
		if ( ! empty( $where['time'] ) )
		{
			$_sql[] = 'eas_updated < ' . intval( $where['time'] );
		}
		
		$this->DB->delete( 'core_editor_autosave', implode( ' AND ', $_sql ) );
	}
	
	/**
	 * Sniff out RTE content
	 * @param string $content
	 * @return boolean
	 */
	public function contentIsRte( $content )
	{
		$content = trim( $content );
		
		if ( substr( $content, 0, 3 ) == '<p>' && substr( $content, -4 ) == '</p>' )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Auto save via ajax
	 * @param string $content
	 * @param string $autoSaveKey
	 */
	public function autoSave( $content, $autoSaveKey )
	{
		/* Convert the data so it is safe to store and preview */
		$parser  = $this->_newParserObject();
	
		/* Unconvert emoticons, etc */
		$this->setLegacyMode( false );
		$content = $this->process( $content );
		
		/* Pretty much just dump it in the DB */
		$this->DB->replace( 'core_editor_autosave', array( 'eas_key'       => $autoSaveKey,
														   'eas_member_id' => $this->memberData['member_id'],
														   'eas_app'	   => IPS_APP_COMPONENT,
														   'eas_section'   => $this->request['module'] . '.' . $this->request['section'],
														   'eas_updated'   => time(),
														   'eas_content'   => $content ), array( 'eas_key' ) );
		
		return true;
	}
	
	/**
	 * CKEditor will send posts with HTML entites (&lt;). If we have HTML enabled
	 * we need to make sure that these HTML entites are converted back into HTML
	 * and that <br> tags are converted into newlines
	 * @param string $conten.
	 */
	protected function _htmlize( $content )
	{
		return $content;
		
		$content = IPSText::br2nl( $content );
		$content = IPSText::UNhtmlspecialchars( $content );
	}

	/**
	 * If we link a youtube URL, it should remain a link.  Other code in the parser extracts URLs from links and auto-embeds, so
	 *	let's define a custom class here that we can later check for.
	 * @param	string HTML
	 * @return	string HTML
	 */
	protected function protectMedia( $content )
	{
		$content	= preg_replace( '#<a\s+?([^>]*?)href=["\']([^"\']+?)?["\']([^>]*?)?>(.+?)</a>#is', "<a data-ipb='nomediaparse' $1href='$2'$3>$4</a>" , $content );

		//print htmlspecialchars($content);exit;

		return $content;
	}
	
	/**
	 * Some characters aren't in ISO-8859-1 but browsers still
	 * show them. But HTMLPurifier gets confused so....
	 */
	protected function _cakeAndEatIt( $content )
	{
		/* Uncomment to find ASCII codes */
		/*$test = '';
		$test = preg_split( '//', $content );
		
		foreach( $test as $i )
		{
			print $i . ' = ' . ord( $i ) . "\n";
		}
		
		IPSDebug::plainPrint('');*/
		
		# Polish z
		$content = str_replace( chr(158), '&#382;', $content );
		
		# Polish Z
		$content = str_replace( chr(142), '&#381;', $content );
		
		return $content;
	}
	
	/**
	 * Prep content for purification
	 * @param string $content
	 * @return string
	 */
	protected function _prePurify( $content )
	{
		/* When pasting non char set characters, CKEditor adds a hidden body tag, this confuses HTMLPurifier */
		$content = preg_replace( '#<body(?:[^>]+?)>#i', '', $content );
		
		$parser  = $this->_newParserObject();
		
		/* Remove our special cite tags */
		$content = preg_replace( '/<cite class="ipb"(?:[^>]+?)?>.+?<\/cite>/', '', $content );
	
		/* Must come first: Ensures manual quote tags inside code boxes aren't converted */
		$content = $this->_makeCodeTagContentSafeForSaving( $content );
		
		/* Make CODE boxes safe so contents are cleaned by HTML Purifer. Has to come before editorToHtml as that method strips HTML tags */
		if ( $this->getAllowHtml() && $this->getIsHtml() )
		{
			$content = $parser->HtmlAllowedPreContents( $content, 'code', array( '[', ']' ) );
		}
		
		/* Next, convert manual code boxes into pre, quote into blockquote */
		$content = $parser->editorToHtml( $content );
		
		/* Now make sure manual IMG tags (that aren't inside of code boxes) are converted properly */
		$content = preg_replace( '#\[img\]([^\[]+?)\[/img\]#i', '<img src="\1" />', $content );
		$content = preg_replace( '#\[img=([^\[]+?)\]#i', '<img src="\1" />', $content );
		
		$content = $this->_denyLinkify( $content );
		
		$content = str_replace( array( "\r\n", "\r" ), "\n", $content );
			
		/* Fix up tags that don't want their contents running */
		/* Stop HTMLPurifier autolinking the closing media tag */
		$content = preg_replace( '#(://.+?(?=\[/(\w+?)\]))#i', '\1 ', $content );
		
		if ( IPS_IS_UTF8 !== true )
		{
			$content = $this->_cakeAndEatIt( $content );
		}
		
		/* Stop made-safe (r) (tm) (c) from converting */
		$content = preg_replace( '#&\#40;(tm|r|c)&\#41;#i', '&amp;#40;$1&amp;#41;', $content );
		
		/* Fix up weird alignment styles */
		$content = preg_replace( '#<(p|div)([^>]+?)dir=([\'"])RTL([\'"])#i', '<\1\2style="text-align:right"', $content );
		$content = preg_replace( '#<(p|div)([^>]+?)align=([\'"])right([\'"])#i', '<\1\2style="text-align:right"', $content );
		$content = preg_replace( '#<(p|div)([^>]+?)align=([\'"])center([\'"])#i', '<\1\2style="text-align:center"', $content );

		return $content;
	}
	
	/**
	 * Tweak content post purify
	 * @param string $content
	 * @return	string
	 */
	protected function _postPurify( $content )
	{
		/* HTML Purifier helpfuly reconverts HTML entities */
		$content = $this->_makeCodeTagContentSafeForSaving( $content );
		
		/* Next */
		$content = $this->_denyLinkify( $content, 2 );
		
		/* Code tags are made safe, so unscrew the embedded links */
		$content = preg_replace( '#(https|http|ftp)\-~~\-//#' , '\1&#58;//', $content );
		
		/* Fix embedded A > IMG tags */
		$content = preg_replace( '#<img src="<a href="([^"]+?)(?:%5D)?"([^>]+?)?>([^"]+?)"#i', '<img src="\1"', $content );
		
		/* @link http://community.invisionpower.com/resources/bugs.html/_/ip-board/img-tag-no-http-r40534 */
		preg_match_all( '#<img src=[\'"]([^\'"]+?)[\'"]([^>]+?)?>#i', $content, $matches, PREG_SET_ORDER );
		
		foreach( $matches as $val )
		{
			$all     = $val[0];
			$src     = $val[1];
			$rest    = $val[2];
				
			if ( substr( $src, 0, 4 ) != 'http' )
			{
				$content = str_replace( $all, '<img src="http://' . $src . '"' . $rest . '>', $content );
			}
		}
		
		if ( IPS_IS_UTF8 )
		{
			/* A control character sneaks in and ruins my life */
			$content = preg_replace( '#\xC2\xa0#', '&nbsp;', $content );
		}
		
		/* Filter specific classes in certain elements */
		if ( !$this->getAllowHtml() || !$this->getIsHtml() )
		{
			$content = $this->_stripNonAllowedClasses( $content, 'pre'       , array( '_prettyXprint', '_linenums*', '_lang*' ) );
			$content = $this->_stripNonAllowedClasses( $content, 'blockquote', array( 'ipsBlockquote' ) );
			$content = $this->_stripNonAllowedClasses( $content, 'ol'        , array( 'bbc', 'bbcol', 'decimal' ) );
			$content = $this->_stripNonAllowedClasses( $content, 'ul'        , array( 'bbc', 'bbcol', 'decimal' ) );
			$content = $this->_stripNonAllowedClasses( $content, 'a'         , array( 'bbc*' ) );
			$content = $this->_stripNonAllowedClasses( $content, 'p'         , array( 'bbc*' ) );
		}

		/* Stop HTMLPurifier autolinking the closing media tag */
		$content = preg_replace( '#(://.+?)(?=\s\[/(\w+?)\])\s\[/\w+?\]#i', '\1[/\2]', $content );
		
		/* Make {style_images_url} safe */
		$content = str_replace( '{style_images_url}', '&#123;style_images_url}', $content );
		$content = str_replace( '{style_image_url}', '&#123;style_image_url}', $content );

		/* Restore made-safe (r) (tm) (c) from converting */
		$content = preg_replace( '#&amp;\#40;(tm|r|c)&amp;\#41;#i', '&#40;$1&#41;', $content );
		
		/* http://community.invisionpower.com/resources/bugs.html/_/ip-board/rte-vs-std-spacing-r41660 */
		$content = preg_replace( '#<p>(\s{0,})<br([^>]+?)?>#is'   , '<p>'   , $content );
		$content = preg_replace( '#</pre>(\s{0,})<br([^>]+?)?>#is', '</pre>', $content );

		return $content;
	}
	
	/**
	 * Target specific tags to deny linkification
	 * @param array $matches
	 */
	protected function _denyLinkify( $content, $pass=1 )
	{
		$parser = $this->_newParserObject();
			
		foreach( array( 'media', 'img', 'url' ) as $tag )
		{
			/* CODE: Fetch paired opening and closing tags */
			$data = $parser->getTagPositions( $content, $tag, array( '[' , ']' ) );
		
			if ( is_array( $data['open'] ) )
			{
				foreach( $data['open'] as $id => $val )
				{
					if ( $tag == 'img' || $tag == 'url' )
					{
						$o  = $data['openWithTag'][ $id ];
						$c  = $data['closeWithTag'][ $id ] - $o;
						
						/* This format [img=..] then? */
						if ( ! $c )
						{
							$c = strpos( $content, ']', $o ) - $o;
						}
					}
					else
					{
						$o  = $data['open'][ $id ];
						$c  = $data['close'][ $id ] - $o;
					}
					
					$slice = substr( $content, $o, $c );
	
					/* Need to bump up lengths of opening and closing */
					$_origLength = strlen( $slice );
					
					if ( $pass == 1 )
					{
						/* Yes this is happening */
						$slice = preg_replace( '#(https|http|ftp)://#' , '\1--,,--//', $slice );
						
						if ( $tag == 'url' )
						{
							if ( stristr( $slice, '[url]' ) )
							{
								preg_match( '#^\[url\](.*)\[/url\]$#i', $slice, $matches );
							}
							else if ( preg_match( '#\[url=(?:"|&quot;)#i', $slice ) )
							{
								preg_match( '#^\[url=(?:"|&quot;)(.*)(?:"|&quot;)\](.*)\[/url\]$#i', $slice, $matches );
							}
							else if ( stristr( $slice, '[url=' ) )
							{
								preg_match( '#^\[url=(.*)\](.*)\[/url\]$#i', $slice, $matches );
							}
							
							/* Attempt to make ?[var]=1 safe */
							if ( $matches[1] )
							{
								/* Have we captured BBCode: http--,,--//www.example.com][b]RTE Bug[/b */
								$openBracket  = strpos( $matches[1], '[' );
								$closeBracket = strpos( $matches[1], ']' );
								
								while ( ( $openBracket ) AND ( $openBracket > $closeBracket ) )
								{
									/* Strip off beginning to last open bracket */
									$matches[1]   = substr( $matches[1], 0, strrpos( $matches[1], '[' ) );

									/* Retest */
									$openBracket  = strpos( $matches[1], '[' );
									$closeBracket = strpos( $matches[1], ']' );
								}
								
								/* Final clean up: http--,,--//www.example.com] */
								if ( $closeBracket AND ! $openBracket )
								{
									$matches[1]   = substr( $matches[1], 0, strrpos( $matches[1], ']' ) );
								}
								
								$url = $matches[1];
								$url = str_replace( '[', '%5B', $url );
								$url = str_replace( ']', '%5D', $url );
								
								$slice = str_replace( $matches[1], $url, $slice );
							}
						}
					}
					else
					{
						$slice = preg_replace( '#(https|http|ftp)--,,--//#' , '\1://', $slice );
					}
					
					$_newLength  = strlen( $slice );
		
					$content = substr_replace( $content, $slice, $o, $c );
		
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
								$data['openWithTag'][ $_id ]  += ( $_newLength - $_origLength );
								$data['closeWithTag'][ $_id ] += ( $_newLength - $_origLength );
							}
						}
					}
				}
			}
		}
		//IPSDebug::plainPrint ( $content );
		/* Prevent media linkify by defaultify okify? */
		if ( $pass == 1 )
		{
			$content = preg_replace_callback( '#(^|\s|\)|\(|\{|\}|>|\]|\[|;|href=\S)((http|https|news|ftp)://(?:[^<>\)\[\"\s]+|[a-zA-Z0-9/\._\-!&\#;,%\+\?:=]+))(</a>)?#is', array( $this, '_denyMediaLinkify_CallBack' ), $content );
		}
		
		if ( $pass == 2 )
		{
			$content = preg_replace( '#(https|http|ftp)--,,--//#' , '\1://', $content );
		}
		
		return $content;
	}
	
	/**
	 * Callback to prevent media tags from being auto linkified
	 *
	 * @access	protected
	 * @param	array		Matches from the regular expression
	 * @return	string		Converted text
	 */
	protected function _denyMediaLinkify_CallBack( $matches )
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
		if ( $this->settings['bbcode_automatic_media'] )
		{
			$media = $this->cache->getCache('mediatag');
			
			if ( is_array( $media ) AND count( $media ) )
			{
				foreach( $media as $type => $r )
				{
					if ( preg_match( "#^" . $r['match'] . "$#is", $matches[2] ) )
					{
						$matches[2] = preg_replace( '#(https|http)://#' , '\1--,,--//', $matches[2] );
					}
				}
			}
		}
		
		return $matches[1] . $matches[2] . $_extra;
	}
	
	/**
	 * Fix code tags manually entered
	 * @param array $matches
	 */
	protected function _fixManuallyEnteredCodeBoxesIntoRte( $content )
	{
		$parser = $this->_newParserObject();
		
		/* CODE: Fetch paired opening and closing tags */
		$data = $parser->getTagPositions( $content, 'code', array( '[' , ']' ) );
	
		if ( is_array( $data['open'] ) )
		{
			foreach( $data['open'] as $id => $val )
			{
				$o = $data['open'][ $id ];
				$c = $data['close'][ $id ] - $o;
	
				$slice = substr( $content, $o, $c );
	
				/* Need to bump up lengths of opening and closing */
				$_origLength = strlen( $slice );
	
				/* Extra conversion for BBCODE>HTML mode */
				$slice = preg_replace( '#(https|http|ftp)://#' , '\1&#58;//', $slice );
				$slice = str_replace( '<p>'   , '', $slice );
				$slice = str_replace( "<br />\n", "\n", $slice );
				$slice = trim( str_replace( "</p>\n", "\n", $slice ) );
				
				/* Stop (r) (tm) and (c) from switching out */
				$slice = preg_replace( '#\((tm|r|c)\)#i', '&#40;$1&#41;', $slice );
				
				$slice = IPSText::stripTags( $slice, 'pre' );
				
				/* Turn code tags into PRE */
				preg_match_all( '#\[code([^\]]+?)?\]#i', $content, $matches, PREG_SET_ORDER );
				
				foreach( $matches as $val )
				{
					$all     = $val[0];
					$option  = $val[1];
					$lineNums = 0;
					$langAdd  = '';
						
					if ( $option )
					{
						list( $lang, $lineNums ) = explode( ':', $option );
					}
						
					if ( $lang )
					{
						$langAdd = ' _lang-' . trim( $lang );
					}
						
					/* We use underscores so the code is not highlighted when using CKEditor */
					$content = str_replace( $all, "<pre class='_prettyXprint"  . $langAdd . " _linenums:" . trim( intval( $lineNums ) ) . "'>", $content );
				}
				
				$content = str_ireplace( '[/code]', '</pre>', $content );
				
				$_newLength  = strlen( $slice );
	
				$content = substr_replace( $content, $slice, $o, $c );
	
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
	
		return $content;
	}
	
	/**
	 * Check and make safe embedded codes
	 * @param array $matches
	 */
	protected function _makeCodeTagContentSafeForSaving( $content )
	{
		$parser = $this->_newParserObject();
	
		/* Fetch paired opening and closing tags */
		$data = $parser->getTagPositions( $content, 'pre', array( '<', '>') );
	
		if ( is_array( $data ) && count( $data ) )
		{
			foreach( $data['open'] as $id => $val )
			{
				$o = $data['open'][ $id ];
				$c = $data['close'][ $id ] - $o;
				
				$slice = substr( $content, $o, $c );
			
				$_origLen = strlen( $slice );
	
				$slice = str_replace( '[', '&#91;', $slice );
				$slice = preg_replace( '/\/(\w+?)\]/', '/\1&#93;', $slice );
				$slice = str_replace( "{parse", "&#123;parse", $slice );
				$slice = preg_replace( '#(https|http|ftp)://#' , '\1-~~-//', $slice );
				
				/* Stop (r) (tm) and (c) from switching out */
				$slice = preg_replace( '#\((tm|r|c)\)#i', '&#40;$1&#41;', $slice );
				
				$slice = IPSText::stripTags( $slice, 'pre' );
				
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

		return $content;
	}
	
	/**
	 * Generates unique editor ID
	 */
	protected function _fetchEditorName()
	{
		return 'editor_' . uniqid();
	}
	
	/**
	 * Generates a full autosave key
	 * @param string $autoSaveKey
	 * @return string
	 */
	protected function _generateAutoSaveKey( $autoSaveKey )
	{
		return md5( IPS_APP_COMPONENT . '-' . trim( $autoSaveKey ) . '-' . intval( $this->memberData['member_id'] ) );
	}
	
	/**
	 * Create a purifier object
	 * @return	Purifier object
	 */
	private function _newPurifierObject()
	{
		/* Fetch disabled tags */
		$parser       = $this->_newParserObject();
		$disabledTags = $parser->getDisabledTags();
		
		$disabledTagMap = array( 'b'   => array('b', 'strong'),
								 'i'   => array('em'),
						    	 's'   => array('strike'),
								 'sup' => array('sup'),
								 'sub' => array('sub') );
		
		$allowedCssProps = array( 'color', 'font-family', 'font-size', 'background-color', 'font-weight', 'font-style', 'text-align', 'margin', 'margin-left' );
		
		/* Not used v */
		$noTags          = array( 'html', 'body', 'head', 'meta', 'title', 'script', 'style', 'frame', 'frameset', 'base', 'basefont', 'applet' );
		
				/* P, DIV, PRE, BLOCQUOTE */
		$tags = 'p[style|class],div[style],pre[class]' /*,cite[class|contenteditable]*/
				. ',blockquote[class|data-author|data-cid|data-date|data-time|data-collapsed],'
				/* Span */
			  . 'span[style],br,'
			  	/* B, I, U, S, s, s */
			  . 'b,strong,i,em,u,strike,del,sup,sub,'
			    /* LISTS */
			  . 'ol[type|start|class],ul[type|class],li,'
			  	/* IMG, A */	
			  . 'img[src|width|height|title],a[href|target|title|class|rel]';
		
		if ( count( $disabledTags ) )
		{
			$workingProps = array_combine( $allowedCssProps, $allowedCssProps );
			
			foreach( $disabledTags as $tag )
			{
				if ( in_array( $tag, array_keys( $disabledTagMap ) ) )
				{
					foreach( $disabledTagMap[ $tag ] as $_t )
					{
						$tags = str_replace( ',' . $_t . ',', ',', $tags );
					}
				}
				else
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
			
			$allowedCssProps = array_values( $workingProps );
		}
		
		$config = HTMLPurifier_Config::createDefault();
		
		/* Cache path (Please put first)*/
		$config->set('Cache.SerializerPath', HTML_PURIFIER_PATH . 'cache/tmp' );
		
		/* Allow data- attributes */
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
	
		if ( IN_DEV )
		{
			$config->set('Cache.DefinitionImpl', null);
		}
		
		$config->set('AutoFormat.Linkify', true );
		$config->set('Core.Encoding', IPS_DOC_CHAR_SET );
	
		if ( IPS_IS_UTF8 !== true )
		{
			$config->set('Core.EscapeNonASCIICharacters', true );
		}
		
		/* Allow UTF-8 domain names in URLs */
		@set_include_path( IPS_KERNEL_PATH . 'PEAR/Net_IDNA2/' );
		require_once( IPS_KERNEL_PATH . 'PEAR/Net_IDNA2/Net/IDNA2.php' );
		$config->set('Core.EnableIDNA', true );
		
		/* If HTML mode... as of 3.4.2, HTML enabled content bypasses this method */
		if ( $this->getAllowHtml() && $this->getIsHtml() )
		{
			/* Good luck, all */
			$config->set('HTML.Trusted', true );
		}
		else
		{
			/* Whitelist just the basics we need */
			$config->set( 'HTML.Allowed', $tags );
			$config->set( 'Core.EscapeInvalidTags', false );
				
			/* Forbid *most* CSS */
			$config->set( 'CSS.AllowedProperties', $allowedCssProps );
		}
		
		/* Limit CSS Damage */
		$config->set( 'CSS.AllowImportant', false );
		$config->set( 'CSS.AllowTricky'   , false );
		$config->set( 'CSS.Trusted'       , false );
		
		/* Add custom data attributes */
		$def = $config->getHTMLDefinition(true);
		
		$def->addAttribute( 'blockquote', 'data-author'   , 'Text' );
		$def->addAttribute( 'blockquote', 'data-cid'      , 'Text' );
		$def->addAttribute( 'blockquote', 'data-time'     , 'Text' );
		$def->addAttribute( 'blockquote', 'data-date'     , 'Text' );
		$def->addAttribute( 'blockquote', 'data-collapsed', 'Text' );
				
		return new HTMLPurifier( $config );
	}
	
	/**
	 * Create a new parser object
	 * @return object
	 */
	private function _newParserObject()
	{	
		/* Load parser */
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/text/parser.php', 'classes_text_parser' );
		$parser = new $classToLoad();
		
		$parser->set( array( 'parseArea' => $this->getBbcodeSection(), 'memberData' => $this->memberData, 'parseBBCode' => $this->getAllowBbcode(), 'parseHtml' => ( $this->getAllowHtml() && $this->getIsHtml() ), 'parseEmoticons' => $this->getAllowSmilies() ) );
		
		return $parser;
	}
	
	/**
	 * Strip non-allowed class names from items
	 * @param string	Text
	 * @param string	tag name
	 * @param array		Allowed classes
	 */
	protected function _stripNonAllowedClasses( $text, $tag, array $allowedClassNames )
	{
		preg_match_all( '#<' . $tag . ' ([^>]+?)?class=([\'"])([^\'"]+?)([\'"])#i', $text, $matches, PREG_SET_ORDER );

		foreach( $matches as $val )
		{
			$all     = $val[0];
			$other   = $val[1];
			$open    = $val[2];
			$classes = trim( $val[3] );
			$close   = $val[4];
			
			if ( $classes )
			{
				$_names = explode( ' ', $classes );
				$_use   = array();
				
				foreach( $_names as $n )
				{
					$n = trim( $n );
					
					foreach( $allowedClassNames as $allowed )
					{
						if ( strstr( $allowed, '*' ) )
						{
							$allowed = str_replace( '*', '(?:.*)', $allowed );
						}
						
						if ( preg_match( '#^' . $allowed . '$#i', $n ) )
						{
							$_use[] = $n;
						}
					}
				}
				
				$text = str_replace( $all, '<' . $tag . ' ' . $other . ' class=' . $open . implode( ' ', $_use ) . $close, $text );
			}
		}
		
		return $text;
	}
	
	/**
	 * Determines whether or not we can use the RTE
	 * @return	boolean
	 */
	protected function _canWeRte( $ignoreRTECheck=false )
	{
		/* Have we specifically set RTE? */
		if ( $this->getForceRte() !== null )
		{
			return $this->getForceRte();
		}
		
		/* Sent inline */
		if ( $ignoreRTECheck === false && ( isset( $_REQUEST['isRte'] ) && $_REQUEST['isRte'] == 1 ) )
		{
			return true;
		}
		
		$return = FALSE;
	
		if ( $this->memberData['userAgentKey'] == 'explorer' AND $this->memberData['userAgentVersion'] >= 7 )
		{
			$return = TRUE;
		}
		else if ( $this->memberData['userAgentKey'] == 'opera' AND $this->memberData['userAgentVersion'] >= 9.00 )
		{
			$return = TRUE;
		}
		else if ( $this->memberData['userAgentKey'] == 'firefox' AND $this->memberData['userAgentVersion'] >= 3 )
		{
			$return = TRUE;
		}
		else if ( $this->memberData['userAgentKey'] == 'safari' AND $this->memberData['userAgentVersion'] >= 4 )
		{
			$return = TRUE;
		}
		else if ( $this->memberData['userAgentKey'] == 'chrome' AND $this->memberData['userAgentVersion'] >= 2 )
		{
			$return = TRUE;
		}
		else if ( $this->memberData['userAgentKey'] == 'camino' AND $this->memberData['userAgentVersion'] >= 2 )
		{
			$return = TRUE;
		}
		else if ( $this->memberData['userAgentKey'] == 'mozilla' AND $this->memberData['userAgentVersion'] >= 4 )
		{
			$return = TRUE;
		}
		else if ( $this->memberData['userAgentKey'] == 'aol' AND $this->memberData['userAgentVersion'] >= 9 )
		{
			$return = TRUE;
		}
		else if ( $this->memberData['userAgentKey'] == 'iphone' )
		{
			$return = false;
		}
		else if ( $this->memberData['userAgentKey'] == 'iPad' )
		{
			$return = false;
		}
		
		return $return;
	}
	
	/**
	 * Previous to 3.4, we allowed HTML mixed in with BBCode almost by accident. Now that we need HTML mode set via a checkbox, these
	 * areas no longer work in the same way that they did in <= 3.3.x
	 *
	 * NOTE: This method does NOT attempt to verify if the user has HTML posting permissions or not. Use in conjunction with
	 * proper permission checks in your own code.
	 * Or better, pretend this method doesn't exist.
	 * This is not a kitten: http://3.bp.blogspot.com/-pljPk5xFiK8/UC5Wi-5u5TI/AAAAAAAAA3A/Ojk4aFYyCM4/s1600/11059_sheldon-cooper-en-big-bang+copy.jpg
	 */
	protected function _tryAndDetermineHtmlStatusTheHackyWay( $content )
	{
		if ( stristr( $content, '<!-- isHtml:1 -->' ) )
		{
			return true;
		}
		else
		{
			/* Ok, look for <param, <object, <table, <script as these are commonly added HTML elements */
			foreach( array( 'param', 'object', 'table', 'script' ) as $tag )
			{
				/* Existing posts will be stored in &lt;tag format */
				if ( stristr( $content, '<' . $tag ) || stristr( $content, '&lt;' . $tag ) )
				{
					return true;
				} 
			}
		}
		
		return false;
	}
}