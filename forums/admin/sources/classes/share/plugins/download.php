<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Twitter plug in for share links library.
 * This is just the basic fallback twitter share, the front end has JS to do something more fancy
 *
 * Created by Matt Mecham
 * Last Updated: $Date: 2013-02-25 17:56:34 -0500 (Mon, 25 Feb 2013) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 12023 $
 *
 */

/* Class name must be in the format of:
   sl_{key}
   Where {key}, place with the value of: core_share_links.share_key
 */
class sl_download
{
	/**#@+
	* Registry Object Shortcuts
	*
	* @access	protected
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
	/**#@-*/
	
	/**
	 * Construct.
	 * @access	public
	 * @param	object		Registry
	 * @return	@e void
	 */
	public function __construct( $registry )
	{
		/* Make object */
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
	}
	
	/**
	 * Requires a permission check
	 *
	 * @access	public
	 * @param	array		Data array
	 * @return	boolean
	 */
	public function requiresPermissionCheck( $array )
	{
		return true;
	}
	
	/**
	 * Redirect to Print
	 * Exciting, isn't it.
	 *
	 * @access	private
	 * @param	string		Title
	 * @param	string		URL
	 */
	public function share( $title, $url )
	{
		$title = IPSText::convertCharsets( $title, IPS_DOC_CHAR_SET, 'utf-8' );
		
		$_qmCount	= substr_count( $url, '?' );
		$_count		= ( $this->settings['url_type'] == 'query_string' ) ? 1 : 0;
		
		if ( $_qmCount > $_count )
		{
			#?/furl?s=xxxx
			$url .= '&forceDownload=1';
		}
		else
		{
			$url .= '?forceDownload=1';
		}
		
		$url .= '&_k=' . $this->member->form_hash;

		$this->registry->output->silentRedirect( $url );
	}
}