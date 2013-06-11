<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * API: Core
 * Last Updated: $Date: 2013-05-22 09:12:58 -0400 (Wed, 22 May 2013) $
 * </pre>
 *
 * @author 		$Author: mmecham $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 12264 $
 */
 
if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class hanEmail
{
	/**
	 * Emailer object reference
	 *
	 * @var		object
	 */
	public $emailer;
	
	/**
	 * Email header
	 *
	 * @var		string
	 */
	public $header;
	
	/**
	 * Email footer
	 *
	 * @var		string
	 */
	public $footer;
	
	/**
	 * Email from
	 *
	 * @var		string
	 */
	public $from;
	
	/**
	 * Email to
	 *
	 * @var		string
	 */
	public $to;
	
	/**
	 * Email cc's
	 *
	 * @var	array
	 */
	public $cc		= array();
	
	/**
	 * Email bcc's
	 *
	 * @var	array
	 */
	public $bcc		= array();
	
	/**
	 * Email subject
	 *
	 * @var		string
	 */
	public $subject;
	
	/**
	 * Email body
	 *
	 * @var		string
	 */
	public $message;
	
	/**
	 * HTML Mode
	 *
	 * @var		bool
	 */
	public $html_email = FALSE;
	
	/**
	 * Include unsubscribe link?
	 *
	 * @var		bool
	 */
	public $unsubscribe = FALSE;
		
	/**
	 * Temp word swapping array
	 *
	 * @var		array
	 */
	protected $_words;
	
	/**
	 * Headers to pass to email lib
	 *
	 * @var		array
	 */
	protected $temp_headers		= array();
	
	protected $_attachments       = array();
	protected $editor;
	protected $plainTextTemplate  = '';
	protected $htmlTemplate		  = '';
	protected $htmlWrapper		  = '';
	protected $_loadedHtmlTemplateClass = '';
	
	/**#@+
	 * Registry Object Shortcuts
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
	/**#@-*/
	
	/**
	 * Construct
	 *
	 * @param	object		ipsRegistry reference
	 * @return	@e void
	 */
	public function __construct( ipsRegistry $registry )
	{
		/* Make object */
		$this->registry = $registry;
		$this->DB       = $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang     = $this->registry->getClass('class_localization');
		$this->member   = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		
		/* Set up default handler */
		$this->setHtmlEmail( $this->settings['email_use_html'] );
	}
	
	/**
	 * Sets whether or not this is a HTML email
	 * @param boolean duh $boolean
	 */
	public function setHtmlEmail( $boolean=null )
	{
		$this->html_email = ( $boolean ) ? true : false;
		
		if ( is_object( $this->emailer ) )
		{
			$this->emailer->setHtmlEmail( $boolean );
		}
	}
	
	/**
	 * Init method (setup stuff)
	 *
	 * @return	@e void
	 */
    public function init()
    {
		$this->header   = $this->settings['email_header'] ? $this->settings['email_header'] : '';
		$this->footer   = $this->settings['email_footer'] ? $this->settings['email_footer'] : '';
		
		$classToLoad = IPSLib::loadLibrary( IPS_KERNEL_PATH . 'classEmail.php', 'classEmail' );

		$this->emailer = new $classToLoad( array( 'debug'			=> $this->settings['fake_mail'] ? $this->settings['fake_mail'] : '0',
										 		  'debug_path'		=> DOC_IPS_ROOT_PATH . '_mail',
										 		  'smtp_host'		=> $this->settings['smtp_host'] ? $this->settings['smtp_host'] : 'localhost',
										 		  'smtp_port'		=> intval($this->settings['smtp_port']) ? intval($this->settings['smtp_port']) : 25,
										 		  'smtp_user'		=> $this->settings['smtp_user'],
										 		  'smtp_pass'		=> $this->settings['smtp_pass'],
										 		  'smtp_helo'		=> $this->settings['smtp_helo'],
										 		  'method'			=> $this->settings['mail_method'],
										 		  'wrap_brackets'	=> $this->settings['mail_wrap_brackets'],
										 		  'extra_opts'		=> $this->settings['php_mail_extra'],
										 		  'charset'			=> IPS_DOC_CHAR_SET,
										 		  'html'			=> $this->html_email ) );
    }
    
    /**
     * Clear out any temporary headers
     *
     * @return	@e void
     */
    public function clearHeaders()
    {
    	$this->temp_headers	= array();
    }
    
    /**
     * Manually set an email header
     *
     * @param	string	Header key
     * @param	string	Header value
     * @return	@e void
     */
    public function setHeader( $key, $value )
    {
    	$this->temp_headers[ $key ]	= $value;
    }
    
	/**
	 * Send an email
	 *
	 * @return	boolean		Email sent successfully
	 */
	public function sendMail()
	{	
		$this->init();
		
		if( $this->emailer->error )
		{
			return $this->fatalError( $this->emailer->error_msg, $this->emailer->error_help );
		}
		
		/* Add attachments if any */
		if ( count( $this->_attachments ) )
		{
			foreach( $this->_attachments as $a )
			{
				$this->emailer->addAttachment( $a[0], $a[1], $a[2] );
			}
		}
		
		$this->settings['board_name'] = $this->cleanMessage($this->settings['board_name']);
		
		$this->emailer->setFrom( $this->from ? $this->from : $this->settings['email_out'], $this->settings['board_name'] );
		$this->emailer->setTo( $this->to );
		
		foreach( $this->cc as $cc )
		{
			$this->emailer->addCC( $cc );
		}
		foreach( $this->bcc as $bcc )
		{
			$this->emailer->addBCC( $bcc );
		}
		
		if( count($this->temp_headers) )
		{
			foreach( $this->temp_headers as $k => $v )
			{
				$this->emailer->setHeader( $k, $v );
			}
		}

		//-----------------------------------------
		// Added strip_tags for the subject 4.16.2010
		// so we can have links in subject for inline
		// notifications and still use the subject
		//-----------------------------------------
		
		$this->emailer->setSubject( $this->_cleanSubject($this->subject) );
		
		/* If we're sending a HTML email, we need to manually send the plain text and HTML versions */
		if ( $this->html_email and $this->htmlTemplate )
		{
			/* Dynamically replace subject in template */
			$this->htmlTemplate = str_ireplace( array('<#subject#>'), IPSText::utf8ToEntities( $this->_cleanSubject($this->subject) ), $this->htmlTemplate );
			$this->emailer->setPlainTextContent( $this->plainTextTemplate );
			$this->emailer->setHtmlContent( $this->htmlTemplate );
		}
		else if ( $this->message && ! $this->plainTextTemplate )
		{
			/* Older methods pass message directly */
			$this->emailer->setBody( $this->message );
		}
		else
		{
			$this->emailer->setBody( $this->plainTextTemplate );
		}
		
		$this->emailer->sendMail();
		
		/* Clear out stuffs */
		$this->clearContent();
		
		// Unset HTML setting to remain backwards compatibility
		//$this->html_email = FALSE;
		
		if( $this->emailer->error )
		{
			return $this->fatalError( $this->emailer->error_msg, $this->emailer->error_help );
		}
		
		return true;
	}
	
	/**
	 * Set the plain text template
	 * @param string $string
	 */
	public function setPlainTextTemplate( $string, $unsub=false )
	{
		/* Reset message too */
		$this->message           = '';
		
		/* Add unsubscribe link */
		$this->plainTextTemplate = str_replace( '&nbsp;', ' ', $string );
		$this->plainTextTemplate = str_replace( '&#160;', ' ', $string );
		if ( $unsub )
		{
			$this->plainTextTemplate .= "\n\n\n\n<#UNSUBSCRIBE#>";
		}
		
		/*
		 * Quite often the plain text template is populated but the HTML isn't -but then plainTextTemplate is modified by buildPlainTextContent
		 * so when we then run buildHtmlContent, it uses the already processed plainTextTemplate which has substituted <#POST#> into a stripped version.
		 */
		if ( ! $this->htmlTemplate )
		{
			/* Need to exchange BRs? */
			if ( ! stristr( $this->plainTextTemplate, '<br' ) )
			{
				$this->htmlTemplate = nl2br( $this->plainTextTemplate );
			}
			else
			{
				$this->htmlTemplate = $this->plainTextTemplate;
			}
		}
	}
	
	/**
	 * Set the HTML template
	 * @param string $string
	 */
	public function setHtmlTemplate( $string )
	{
		/* Reset message too */
		$this->message           = '';
		
		$this->htmlTemplate = $string;
	}
	
	/**
	 * Return plain text content
	 * @return string
	 */
	public function getPlainTextContent()
	{
		return $this->plainTextTemplate;
	}
	
	/**
	 * Return HTML content
	 * @return string
	 */
	public function getHtmlContent()
	{
		return $this->htmlTemplate;
	}
	
	/**
	 * Removes all current stored messages, templates, etc.
	 */
	public function clearContent()
	{
		$this->setHtmlTemplate('');
		$this->setPlainTextTemplate('');
		$this->message  = '';
		$this->template = '';
		$this->_attachments = array();
	}
	
	/**
	 * Send custom html wrapper - HTML wrapper with <#content#> tag where content will be
	 * @param string $string
	 */
	public function setHtmlWrapper( $string )
	{
		$this->htmlWrapper = $string . "<br /><br /><br /><br /><#UNSUBSCRIBE#>";
	}
	
	/**
	 * Retrieve an email template
	 *
	 * @param	string		Template key
	 * @param	string		Language to use
	 * @param	string		Language file to load
	 * @param	string		Application of language file
	 * @return	@e void
	 */
	public function getTemplate( $name, $language="", $lang_file='public_email_content', $app='core' )
	{
		/* Reset $this->message as legacy methods use $this->message when sending notifications, etc */
		$this->clearContent();
		
		/* Sometimes the lang_file & app can end up being empty - reset them */
		$lang_file	= empty($lang_file) ? 'public_email_content' : $lang_file;
		$app		= empty($app) ? 'core' : $app;
		
		//-----------------------------------------
		// Check..
		//-----------------------------------------
		
		if( $name == "" )
		{
			$this->error++;
			$this->fatalError( "A valid email template ID was not passed to the email library during template parsing", "" );
		}
		
		//-----------------------------------------
		// Default?
		//-----------------------------------------

		if( ! $language )
		{
			$language = IPSLib::getDefaultLanguage();
		}
		
		$this->language = $language;
		
		//-----------------------------------------
		// Check and get
		//-----------------------------------------
		
		$this->registry->class_localization->loadLanguageFile( array( $lang_file ), $app, $language, TRUE );
		
		//-----------------------------------------
		// Stored KEY?
		//-----------------------------------------
		
		if ( ! isset($this->lang->words[ $name ]) )
		{
			if ( $language == IPSLib::getDefaultLanguage() )
			{
				$this->fatalError( "Could not find an email template with an ID of '{$name}'", "" );
			}
			else
			{
				$this->registry->class_localization->loadLanguageFile( array( $lang_file ), $app, IPSLib::getDefaultLanguage() );
				
				if ( ! isset($this->lang->words[ $name ]) )
				{
					$this->fatalError( "Could not find an email template with an ID of '{$name}'", "" );
				}
			}
		}
		
		//-----------------------------------------
		// Subject?
		//-----------------------------------------
		
		if ( isset( $this->lang->words[ 'subject__'. $name ] ) )
		{
			$this->subject = stripslashes( $this->lang->words[ 'subject__'. $name ] );
		}
		
		//-----------------------------------------
		// Return
		//-----------------------------------------
		
		$this->template = stripslashes($this->lang->words[ $name ]) . stripslashes($this->lang->words['email_footer']);
		
		/* Returns it if called via setPlainTextTemplate */
		return $this->template;
	}
		
	/**
	 * Legacy, generic method: builds an email from a template, replacing variables
	 *
	 * @deprecated
	 * @param	array		Replacement keys to values
	 * @param	bool		Do not "clean"
	 * @param	bool		Raw HTML
	 * @param	array		Member data - necessary if including unsubscribe link
	 * @return	@e void
	 */
	public function buildMessage( $words, $noClean=false, $rawHtml=FALSE, $memberData=array() )
	{
		/* Init */
		$ptWords   = array();
		$htmlWords = array();
		$subjWords = array();
				
		/* Try this first */
		if ( ! $this->plainTextTemplate && ! $this->htmlTemplate && $this->message )
		{
			$this->setPlainTextTemplate( $this->message, true );
		}
		
		/* need to converge some stuff here */
		if ( ! $this->plainTextTemplate && $this->template )
		{
			/* Sniff, sniff */
			if ( stristr( $this->template, '<br' ) )
			{
				if ( ! $this->htmlTemplate )
				{
					$this->setHtmlTemplate( $this->template );
				}
				
				$this->setPlainTextTemplate( IPSText::br2nl( $this->template ) );
			}
			else
			{
				if ( ! $this->htmlTemplate )
				{
					$this->setHtmlTemplate( nl2br( $this->template ) );
				}
				
				$this->setPlainTextTemplate( $this->template );
			}
		}
		
		/* HTML enabled but no specific template: Auto convert */
		if ( $this->html_email && ! $this->htmlTemplate )
		{
			/* It will be dynamically updated at the end */
			$this->setHtmlTemplate( $this->plainTextTemplate );
		}
		
		/* HTML email with HTML template but no plain text version */
		if ( $this->htmlTemplate && ! $this->plainTextTemplate )
		{
			$msg = $this->htmlTemplate;
			$msg = preg_replace( '/<#(.+?)#>/', '{{{-\1-}}}', $msg );
			$msg = str_replace( "<br />", "\n", $msg );
			$msg = str_replace( "<br>"  , "\n", $msg );
			$msg = IPSText::stripTags( $msg );
			
			$msg = html_entity_decode( $msg, ENT_QUOTES );
			$msg = str_replace( '&#092;', '\\', $msg );
			$msg = str_replace( '&#036;', '$', $msg );
			$msg = preg_replace( '/\{\{\{(.+?)\}\}\}/', '<#\1#>', $msg );
			
			$this->setPlainTextTemplate( $msg );
		}
		
		if ( $this->plainTextTemplate && ! $this->template && ( $this->html_email && ! $this->htmlTemplate ) )
		{
			$this->error++;
			$this->fatalError( "Could not build the email message, no template assigned", "Make sure a template is assigned first." );
		}
		
		/* Bit more clean up */
		$this->plainTextTemplate = str_replace( array( "\r\n", "\r", "\n" ), "\n", $this->plainTextTemplate );
		$this->htmlTemplate      = str_replace( array( "\r\n", "\r", "\n" ), "\n", $this->htmlTemplate );
		
		/* Apply HTML wrapper */
		$this->htmlTemplate      = $this->applyHtmlWrapper( $this->subject, ( $rawHtml ? $this->htmlTemplate : $this->convertTextEmailToHtmlEmail( $this->htmlTemplate, $rawHtml ) ) );
		
		/* Add unsubscribe link */
		if ( $this->unsubscribe and !empty( $memberData ) )
		{
			$this->registry->class_localization->loadLanguageFile( array( 'public_global' ), 'core', $this->language ? $this->language : IPSLib::getDefaultLanguage(), TRUE );
			$key = md5( $memberData['email'] . ':' . $memberData['members_pass_hash'] );
			$link = $this->registry->output->buildUrl( "app=core&amp;module=global&amp;section=unsubscribe&amp;member={$memberData['member_id']}&amp;key={$key}", 'publicNoSession' );
			
			$this->plainTextTemplate = str_replace( "<#UNSUBSCRIBE#>", "{$this->registry->class_localization->words['email_unsubscribe']}: {$link}", $this->plainTextTemplate );
			$this->htmlTemplate = str_replace( "<#UNSUBSCRIBE#>", "<a href='{$link}'>" . $this->registry->class_localization->words['email_unsubscribe'] . '</a>', $this->htmlTemplate );
		}
		
		/* Add some default words */
		$words['BOARD_ADDRESS'] = $this->settings['board_url'] . '/index.' . $this->settings['php_ext'];
		$words['WEB_ADDRESS']   = $this->settings['home_url'];
		$words['BOARD_NAME']    = $this->settings['board_name'];
		$words['SIGNATURE']     = $this->settings['signature'] ? $this->settings['signature'] : '';
		
		/* Swap the words: 10.7.08 - Added replacements in subject */
		foreach( $words as $k => $v )
		{
			if ( ! $noClean )
			{
				$ptWords[ $k ] = $this->cleanMessage( $v );
			}
			
			$subjWords[ $k ] = $this->cleanMessage( $v, false, false );

			/* Convert over words too so links are linkified */
			$htmlWords[ $k ] = ( $rawHtml ? $v : $this->convertTextEmailToHtmlEmail( $v, $rawHtml ) );
		}

		$this->_words = $ptWords;
		
		$this->plainTextTemplate = preg_replace_callback( "/<#(.+?)#>/", array( &$this, '_swapWords' ), $this->plainTextTemplate );
		
		$this->_words = $htmlWords;
		$this->htmlTemplate      = preg_replace_callback( "/<#(.+?)#>/", array( &$this, '_swapWords' ), str_replace( array( '&lt;#', '#&gt;' ), array( '<#', '#>' ), $this->htmlTemplate ) );

		$this->_words = $subjWords;
		$this->subject           = preg_replace_callback( "/<#(.+?)#>/", array( &$this, '_swapWords' ), $this->subject );
				
		$this->_words            = array();
				
		/* Final touches */
		$this->htmlTemplate      = preg_replace( '#<!--hook\.([^\>]+?)-->#', '', $this->htmlTemplate );
		$this->htmlTemplate		 = $this->registry->getClass('output')->parseIPSTags( $this->htmlTemplate );
		
		/* strip all tags if not HTML */
		if ( ! $this->settings['email_use_html'] )
		{
			$this->plainTextTemplate = IPSText::stripTags( $this->plainTextTemplate );
		}
		
		$this->plainTextTemplate = IPSText::stripTags( stripslashes($this->lang->words['email_header']) ) . $this->plainTextTemplate . IPSText::stripTags( stripslashes($this->lang->words['email_footer']) );
	
		/* Some older apps use $this->message, so give them plaintext */
		$this->message = $this->plainTextTemplate;
	}
	
	/**
	 * Legacy, generic method: builds an email from a template, replacing variables
	 *
	 * @param	array		Replacement keys to values
	 * @param	boolean		Raw HTML mode?
	 * @return	@e void
	 */
	public function buildHtmlContent( $words=array(), $rawHtml=false )
	{
		/* Init */
		$htmlWords = array();
		
		/* Did we set a plainText template but not bother with HTML ? */
		if ( ! $this->htmlTemplate && $this->plainTextTemplate )
		{
			/* Need to exchange BRs? */
			if ( ! stristr( $this->plainTextTemplate, '<br' ) )
			{
				$this->plainTextTemplate = nl2br( $this->plainTextTemplate );
			}
			
			/* Sniff, sniff */
			$this->setHtmlTemplate( $this->plainTextTemplate );
		}
	
		/* HTML enabled but no specific template: Auto convert */
		if ( $this->html_email && ! $this->htmlTemplate )
		{
			/* It will be dynamically updated at the end */
			$this->setHtmlTemplate( $this->plainTextTemplate );
		}
	
		/* Bit more clean up */
		$this->htmlTemplate      = str_replace( array( "\r\n", "\r", "\n" ), "\n", $this->htmlTemplate );
	
		/* Add some default words */
		$words['BOARD_ADDRESS'] = $this->settings['board_url'] . '/index.' . $this->settings['php_ext'];
		$words['WEB_ADDRESS']   = $this->settings['home_url'];
		$words['BOARD_NAME']    = $this->settings['board_name'];
		$words['SIGNATURE']     = $this->settings['signature'] ? $this->settings['signature'] : '';
	
		/* Swap the words: 10.7.08 - Added replacements in subject */
		foreach( $words as $k => $v )
		{
			$htmlWords[ $k ] = $v;
		}
		
		$this->_words            = $htmlWords;
		$this->htmlTemplate      = preg_replace_callback( "/<#(.+?)#>/", array( &$this, '_swapWords' ), str_replace( array( '&lt;#', '#&gt;' ), array( '<#', '#>' ), $this->htmlTemplate ) );
		$this->subject           = preg_replace_callback( "/<#(.+?)#>/", array( &$this, '_swapWords' ), $this->subject );
		$this->_words            = array();
		
		/* Final touches */
		$this->htmlTemplate		 = IPSText::stripAttachTag( $this->htmlTemplate );
		$this->htmlTemplate      = preg_replace( '#<blockquote(?:[^>]+?)?>(.+?)</blockquote>#s', '<br /><div class="eQuote">\\1</div><br />', $this->htmlTemplate );
		$this->htmlTemplate      = $this->applyHtmlWrapper( $this->subject, $this->convertTextEmailToHtmlEmail( $this->htmlTemplate, $rawHtml ) );
		$this->htmlTemplate      = preg_replace( '#<!--hook\.([^\>]+?)-->#', '', $this->htmlTemplate );
		$this->htmlTemplate		 = $this->registry->getClass('output')->parseIPSTags( $this->htmlTemplate );
	
		/* For those who need it */
		return $this->htmlTemplate;
	}
	
	/**
	 * New: build HTML content
	 *
	 * @param	array		Replacement keys to values
	 * @return	@e void
	 */
	public function buildPlainTextContent( $words=array() )
	{
		/* Init */
		$ptWords   = array();
		$subjWords = array();

		/* Try this first */
		if ( ! $this->plainTextTemplate )
		{
			if ( $this->message )
			{
				$this->setPlainTextTemplate( $this->message );
			}
			else if ( $this->template )
			{
				$this->setPlainTextTemplate( $this->template );
			}
		}
		
		/* Bit more clean up */
		$this->plainTextTemplate = str_replace( array( "\r\n", "\r", "\n" ), "\n", $this->plainTextTemplate );
		
		/* Strip out HTML mark-up designed for the HTML template. If $words is false, it's a second pass, so don't strip tags again! */
		if ( $words !== false )
		{
			$this->plainTextTemplate = preg_replace( '/<#(.+?)#>/', '{{{-\1-}}}', $this->plainTextTemplate );
			
			$this->plainTextTemplate = IPSText::stripTags( $this->plainTextTemplate );
			
			$this->plainTextTemplate = preg_replace( '/\{\{\{-(.+?)-\}\}\}/', '<#\1#>', $this->plainTextTemplate );
		}
	
		/* Bit more clean up */
		$this->plainTextTemplate = str_replace( array( "\r\n", "\r", "\n" ), "\n", $this->plainTextTemplate );
		
		/* Add some default words */
		$words['BOARD_ADDRESS'] = $this->settings['board_url'] . '/index.' . $this->settings['php_ext'];
		$words['WEB_ADDRESS']   = $this->settings['home_url'];
		$words['BOARD_NAME']    = $this->settings['board_name'];
		$words['SIGNATURE']     = $this->settings['signature'] ? $this->settings['signature'] : '';
	
		/* Swap the words: 10.7.08 - Added replacements in subject */
		foreach( $words as $k => $v )
		{
			$ptWords[ $k ] 	 = $this->cleanMessage( $v );
			$subjWords[ $k ] = $this->cleanMessage( $v, true, false );
		}
	
		$this->_words 			 = $ptWords;
		$this->plainTextTemplate = preg_replace_callback( "/<#(.+?)#>/", array( &$this, '_swapWords' ), $this->plainTextTemplate );
		$this->_words 			 = $subjWords;
		$this->subject           = preg_replace_callback( "/<#(.+?)#>/", array( &$this, '_swapWords' ), $this->subject );
		$this->_words            = array();
	
		/* Strip All tags if not HTML */
		$this->plainTextTemplate = IPSText::stripTags( stripslashes($this->lang->words['email_header']) ) . $this->plainTextTemplate . IPSText::stripTags( stripslashes($this->lang->words['email_footer']) );
		
		/* For those who need it */
		return $this->plainTextTemplate;
	}
	
	/**
	 * Convert text email to HTML
	 * Pretty nifty method name too.
	 * 
	 * @param	string	Plain text email ready to go
	 * @param	boolean	Raw HTML mode?
	 * @return	string	We're all HTML'd up in here.
	 */
	public function convertTextEmailToHtmlEmail( $content, $rawHtml=false )
	{
		$content = str_replace( array( "\r\n", "\r" ), "\n", $content );
		$content = trim( $content, "\n" );
		
		/* It's probably HITMAL! */
		if ( ! is_object( $this->parser ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/text/parser.php', 'classes_text_parser' );
			$this->parser = new $classToLoad();
			
			/* Reset HTML flags */
			$this->parser->set( array( 'parseArea'      => 'emails',
								 	   'memberData'     => $this->memberData,
								 	   'parseBBCode'    => true,
								 	   'parseHtml' 	    => false,
								 	   'parseEmoticons' => true ) );
		}
		
		/* Is this a HTML post? */
		$isHtmlContent = ( stristr( $content, '<table' ) AND stristr( $content, '</table>' ) ) ? true : false;

		/* Do not truncate URLs */
		if ( (boolean)$rawHtml !== true )
		{
			if ( $isHtmlContent === false )
			{
				$_tmp = $this->settings['__noTruncateUrl'];
				$this->settings['__noTruncateUrl'] = true;
			
				/* Display */
				$content = $this->parser->display( $content );		
				
				/* Remove emoticons */
				$content  = $this->parser->emoticonImgtoCode( $content );
				
				/* Remove images */
				$content  = $this->parser->stripImages( $content );
			
				$this->settings['__noTruncateUrl'] = $_tmp;
				
				/* Purify pass to linkify */
				$purifier = $this->_newPurifierObject();
				
				/* Run it */
				$content = $purifier->purify( $content );
			}
			
			/* Other stuffs */
			$content = preg_replace( '#(\-{10,120})#', '<hr>', $content );
			$content = preg_replace( '#(\={10,120})#', '<hr>', $content );
			$content = preg_replace( '#(?:\n{1,})<hr>#', "<hr>", $content );
			$content = preg_replace( '#<hr>(?:\n{1,})#', "<hr>", $content );
			$content = str_replace( '&nbsp;', ' ', $content );
	
			/* Fix stupid &sect - might want to consider fixing all direct & to &amp; at some point? */
			$content = preg_replace( "#&sect(?!;)#", '&amp;sect', $content );
			
			/* remove double brs */
			$content = preg_replace( '#<br(?:[^>]+?)?><hr><br(?:[^>]+?)?>#i', "<hr>", $content );
			
			/* Empty paragraphs for spacing */
			$content = preg_replace( '#<p([^>]+?)?>(\s+?)</p>#i', '<p\1>&nbsp;</p>', $content );
		}
		
		return wordwrap( $content, 990, "\r\n" );
	}
	
	/**
	 * Add <html> tags and such if it doesn't have any already
	 * 
	 * @param	string	Subject
	 * @param	string	HTML email
	 * @return	string	VERY HTML email
	 */
	public function applyHtmlWrapper( $subject, $content )
	{
		$unsubscribe = '';
		
		if ( $this->unsubscribe )
		{
			$unsubscribe = " &middot; <#UNSUBSCRIBE#>";
		}
	
		/* Due to some legacy methods with mail queue, buildMessage can be called twice
		 * for good reason, but it can then re-apply the wrapper. So we add a unique comment
		 * and then check for this.
		 */
		if ( ! stristr( $content, '<!--::ipb.wrapper.added::-->' ) )
		{
			$content = '<!--::ipb.wrapper.added::-->' . $content;
			
			/* Inline wrapper */
			if ( $this->htmlWrapper )
			{
				return str_ireplace( '<#content#>', $content, $this->htmlWrapper );
			}
			
			/* Attempt to load external wrapper */
			if ( ! is_object( $this->_loadedHtmlTemplateClass ) )
			{
				if ( ! is_file( IPS_CACHE_PATH . 'cache/skin_cache/system/emailWrapper.php' ) )
				{
					require_once( IPS_ROOT_PATH . 'sources/classes/output/systemTemplates.php' );
					$systemTemplates = new systemTemplates();
					$systemTemplates->writeDefaults();
				}
				
				/* Still nothing? */
				if ( ! is_file( IPS_CACHE_PATH . 'cache/skin_cache/system/emailWrapper.php' ) )
				{
					/* Still here? Fail safe */
					return $this->parseWithDefaultHtmlWrapper( $content );
				}
				
				$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/output/systemTemplates.php', 'systemTemplates' );
				$systemTemplates = new $classToLoad();
				$this->_loadedHtmlTemplateClass = $systemTemplates->getClass('emailWrapper');
			}
			
			return $this->_loadedHtmlTemplateClass->getTemplate( $content, $unsubscribe, ipsRegistry::$settings );
		}
		
		return $content;
	}
	
	/**
	 * Replaces key with value
	 *
	 * @param	string		Key
	 * @return	string		Replaced variable
	 */
	protected function _swapWords( $matches )
	{
		return $this->_words[ $matches[1] ];
	}
	
	/**
	 * Cleans the email subject
	 *
	 * @param	string		In text
	 * @return	string		Out text
	 */
	protected function _cleanSubject( $subject )
	{
		$subject = strip_tags( $subject );
		
		$subject = str_replace( "&#036;", "\$", $subject );
		$subject = str_replace( "&#33;" , "!" , $subject );
		$subject = str_replace( "&#34;" , '"' , $subject );
		$subject = str_replace( "&#39;" , "'" , $subject );
		$subject = str_replace( "&#124;", '|' , $subject );
		$subject = str_replace( "&#38;" , '&' , $subject );
		$subject = str_replace( "&#58;" , ":" , $subject );
		$subject = str_replace( "&#91;" , "[" , $subject );
		$subject = str_replace( "&#93;" , "]" , $subject );
		$subject = str_replace( "&#064;", '@' , $subject );
		$subject = str_replace( "&nbsp;", ' ' , $subject );
		$subject = str_replace( "&amp;" , '&' , $subject );
		$subject = str_replace( "&#60;" , '[' , $subject );
		$subject = str_replace( "&#62;" , ']' , $subject );
		
		return $subject;
	}
		
	/**
	 * Cleans an email message
	 *
	 * @param	string		Email content
	 * @param	bool		Skip converting < and >
	 * @param	bool		Fix up plain text links
	 * @return	string		Cleaned email content
	 */
	public function cleanMessage( $message = "", $skipAngleBrackets=false, $fixPlainTextLinks=true ) 
	{
		if ( ! $this->html_email )
		{
			$message = preg_replace_callback( '#\[url=(.+?)\](.+?)\[/url\]#', array( $this, "_formatUrl" ), $message );
		}

		//-----------------------------------------
		// Unconvert smilies 'cos at this point they are img tags
		//-----------------------------------------
		
		$message = IPSText::unconvertSmilies( $message );
		
		//-----------------------------------------
		// We may want to adjust this later, but for
		// now just strip any other html
		//-----------------------------------------
	
		$message = preg_replace( '#</p>(\s+?)?<p([^>]+?)?>#is', '<br />', $message );

		/* We need to fix links in plaintext templates so people don't get "http://some..ing" instead of "http://something" */
		if ( $fixPlainTextLinks )
		{
			$message	= $this->fixPlaintextLinks( $message );
		}
		
		$message = IPSText::stripTags( $message, '<br>,<blockquote>' );

		IPSText::getTextClass( 'bbcode' )->parse_html		= 0;
		IPSText::getTextClass( 'bbcode' )->parse_nl2br		= 1;
		IPSText::getTextClass( 'bbcode' )->parse_bbcode		= 0;
		
		/* Textual representations of certain bbcodes to attempt to keep context */
		
		$plainText = '<br /><br />------------ QUOTE ----------<br />\\1<br />-----------------------------<br /><br />';
		
		$message = preg_replace( '#\[quote(?:[^\]]+?)?\](.+?)\[/quote\]#s',  $plainText, $message );
		$message = preg_replace( '#<blockquote(?:[^>]+?)?>(.+?)</blockquote>#s',  $plainText, $message );

		$plainTextCode = '*CODE* \\1 */CODE*';
		
		$message = preg_replace( '#\[code(?:[^\]]+?)?\](.+?)\[/code\]#s',  $plainTextCode, $message );
		
		$message = preg_replace( '#\[member=(.+?)\]#s', "\\1", $message );
		
		$message = IPSText::getTextClass('bbcode')->stripAllTags( $message, true );
		
		//-----------------------------------------
		// Bear with me...
		//-----------------------------------------
		
		$message = str_replace( "\n"			, "\r\n", $message );
		$message = str_replace( "\r"			, ""	, $message );
		$message = str_replace( "<br>"			, "\r\n", $message );
		$message = str_replace( "<br />"		, "\r\n", $message );
		$message = str_replace( "\r\n\r\n"		, "\r\n", $message );
		
		$message = str_replace( "&quot;", '"' , $message );
		$message = str_replace( "&#092;", "\\", $message );
		$message = str_replace( "&#036;", "\$", $message );
		$message = str_replace( "&#33;" , "!" , $message );
		$message = str_replace( "&#34;" , '"' , $message );
		$message = str_replace( "&#39;" , "'" , $message );
		$message = str_replace( "&#40;" , "(" , $message );
		$message = str_replace( "&#41;" , ")" , $message );
		$message = str_replace( "&lt;"  , "<" , $message );
		$message = str_replace( "&gt;"  , ">" , $message );
		$message = str_replace( "&#124;", '|' , $message );
		$message = str_replace( "&amp;" , "&" , $message );
		$message = str_replace( "&#38;" , '&' , $message );
		$message = str_replace( "&#58;" , ":" , $message );
		$message = str_replace( "&#91;" , "[" , $message );
		$message = str_replace( "&#93;" , "]" , $message );
		$message = str_replace( "&#064;", '@' , $message );
		$message = str_replace( "&nbsp;" , ' ', $message );

		if( !$skipAngleBrackets )
		{
			$message = str_replace( "&#60;" , '<' , $message );
			$message = str_replace( "&#62;" , '>' , $message );
		}

		return $message;
	}

	/**
	 * Fix links for plaintext template.  Changes <a href='http://...'>something</a> to just the URL, which is necessary because parser engine makes links like http://som...jpg
	 *
	 * @param	string		Raw text
	 * @param	string		Raw text for plaintext template
	 * @return	@e void
	 */
	public function fixPlaintextLinks( $message )
	{
		if( !$message )
		{
			return $message;
		}

		preg_match_all( '#<a(?:.+?)href=["\']([^"\']+?)?["\']([^>]+?)?>(.+?)</a>#is', $message, $urlMatches );
		
		/* Finish up URLs and such */
		for( $i = 0 ; $i < count( $urlMatches[0] ) ; $i++ )
		{
			$raw  = $urlMatches[0][ $i ];
			$url  = $urlMatches[1][ $i ];
			$attr = $urlMatches[2][ $i ];
			$text = $urlMatches[3][ $i ];

			/* This will show the direct URL if the text was also part of the URL, or it will show "text (url)" otherwise */
			if( strpos( $text, 'http://' ) === 0 )
			{
				$message = str_replace( $raw, $url, $message );
			}
			else
			{
				$message = str_replace( $raw, $text . ' (' . $url . ')', $message );
			}
		}

		return $message;
	}

	/**
	 * Format url for email
	 *
	 * @param	array		preg_replace matches
	 * @return	string		Formatted url
	 */
	public function _formatUrl( $matches ) 
	{
		$matches[1]	= str_replace( array( '"', "'", '&quot;', '&#039;', '&#39;' ), '', $matches[1] );
		
		return $matches[2] . ' (' . $matches[1] . ')';
	}
	
	/**
	 * Add an attachment to the current email
	 *
	 * @param	string	File data
	 * @param	string	File name
	 * @param	string	File type (MIME)
	 * @return	@e void
	 */
	public function addAttachment( $data="", $name="", $ctype='application/octet-stream' )
	{
		$this->_attachments[] = array( $data, $name, $ctype );
	}
	
	/**
	 * Log a fatal error
	 *
	 * @param	string		Message
	 * @param	string		Help key (deprecated)
	 * @return	bool
	 */
	protected function fatalError( $msg, $help="" )
	{
		$this->DB->insert( 'mail_error_logs',
										array(
												'mlog_date'     => time(),
												'mlog_to'       => $this->to,
												'mlog_from'     => $this->from,
												'mlog_subject'  => $this->subject,
												'mlog_content'  => substr( $this->message, 0, 200 ),
												'mlog_msg'      => $msg,
												'mlog_code'     => $this->emailer->smtp_code,
												'mlog_smtp_msg' => $this->emailer->smtp_msg
											 )
									  );
		
		return false;
	}

	/**
	 * Create a purifier object
	 * @return	Purifier object
	 */
	private function _newPurifierObject()
	{
		/* Grab HTMLPurifier */
		require_once( IPS_KERNEL_PATH . 'HTMLPurifier/HTMLPurifier.auto.php' );
		
		$config = HTMLPurifier_Config::createDefault();
	
		/* Cache path (Please put first)*/
		$config->set('Cache.SerializerPath', HTML_PURIFIER_PATH . 'cache/tmp' );
	
		/* Allow data- attributes */
		$config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$config->set('HTML.TidyLevel', 'none');
	
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
	
		/* If HTML mode... */
		$config->set( 'Core.EscapeInvalidTags', false );
	
		/* Allow CSS Damage */
		$config->set( 'CSS.AllowImportant', true );
		$config->set( 'CSS.AllowTricky'   , true );
		$config->set( 'CSS.Trusted'       , true );
	
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
	 * Default wrapper for HTML emails - edit the one in '/cache/skin_cache/emailWrapper.php'
	 * 
	 * @param	string HTML content
	 * @return	string	HTML email done
	 */
	protected function parseWithDefaultHtmlWrapper( $content )
	{
		$doc =IPS_DOC_CHAR_SET;
		
$email = <<<HTML
	<html>
		<head>
			<meta content="text/html; charset={$doc}" http-equiv="Content-Type">
			<title><#subject#></title>
			<style type="text/css">
			* {
				font-family: Arial;
				font-size: 14px;
				color: #000;
				background: #fff;
				line-height: 140%;
			 }
			</style>
		</head>
		<body>
			{$content}
		</body>
	</head>
  </html>
HTML;

		return $email;
	}
}