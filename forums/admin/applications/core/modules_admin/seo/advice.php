<?php
/**
 * Invision Power Services
 * IP.SEO -  Dashboard
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2010-2011 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.SEO
 * @link		http://www.invisionpower.com
 * @version		$Revision: 10721 $
 */

class admin_core_seo_advice extends ipsCommand
{
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
		
		$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'seo_advice' );
		
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_seo' ) );
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_seo' );
		
		//-----------------------------------------
		// What are we doing?
		//-----------------------------------------
		
		switch($this->request['do'])
		{
			case 'ignore':
				$this->ignoreWarning();
			break;
			
			case 'clear_warnings':
				$this->clearWarnings();
			break;
			
			case 'download_sitemap':
				$this->downloadSitemap();
			break;
			
			default:
				$this->dashboard();
			break;
			
		}
		
		//-----------------------------------------
		// Display
		//-----------------------------------------
		
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Action: Download Sitemap
	 */
	public function downloadSitemap()
	{
		header( "Content-type: application/xml" );
		header( "Content-Disposition: attachment; filename=\"sitemap.xml\"" );
		exit;
	}
	
	/**
	 * Action: Show Dashboard
	 */
	public function dashboard()
	{
		$messages = array();
	
		//-----------------------------------------
		// Get Ignored Messages
		//-----------------------------------------

		$ignores  = ips_CacheRegistry::instance()->getCache('ipseo_ignore_messages');
		if(!$ignores) $ignores = array();
		
		//-----------------------------------------
		// Work out the setting group for SEO settings
		//-----------------------------------------
		
		$group = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'core_sys_settings_titles', 'where' => "conf_title_keyword='seo'" ) );
		
		//-----------------------------------------
		// Get meta tags for board index
		//-----------------------------------------
								
		$metaTags = $this->cache->getCache('meta_tags');
								
		//-----------------------------------------
		// Any messages?
		//-----------------------------------------
		
		/* Sitemap path not writable */
		$this->settings['sitemap_path'] = $this->settings['sitemap_path'] ? $this->settings['sitemap_path'] : DOC_IPS_ROOT_PATH;
		if ( !array_key_exists( 'sitemap_path', $ignores ) and ( !file_exists( $this->settings['sitemap_path'] . 'sitemap.xml' ) or !is_writable( $this->settings['sitemap_path'] . 'sitemap.xml' ) ) )
		{
			// Hang on, can we create one?
			@file_put_contents( $this->settings['sitemap_path'] . 'sitemap.xml', '' );
			@chmod( $this->settings['sitemap_path'] . 'sitemap.xml', 0777 );
				
			if ( !file_exists( $this->settings['sitemap_path'] . 'sitemap.xml' ) or !is_writable( $this->settings['sitemap_path'] . 'sitemap.xml' ) )
			{
				$messages[] = array( 'level' => 'bad', 'fix' => 'app=core&module=seo&section=advice&do=download_sitemap', 'key' => 'sitemap_path' );
			}
		}
		
		/* URL type not path info */
		if(!array_key_exists('url_type', $ignores) && $this->settings['url_type'] != 'path_info')
		{
			$messages[] = array('level' => 'bad', 'fix' => "app=core&module=settings&section=settings&do=setting_view&conf_group={$group['conf_title_id']}", 'key' => 'url_type');
		}
			
		/* No board index title */
		if(!array_key_exists('seo_index_title', $ignores) && !$this->settings['seo_index_title'] )
		{
			$messages[] = array('level' => 'bad', 'fix' => "app=core&module=settings&section=settings&do=setting_view&conf_group={$group['conf_title_id']}", 'key' => 'seo_index_title');
		}
		
		/* No board index description */
		if( !array_key_exists('seo_index_md', $ignores) && !$this->settings['seo_index_md'] )
		{
			$messages[] = array('level' => 'bad', 'fix' => "app=core&module=settings&section=settings&do=setting_view&conf_group={$group['conf_title_id']}", 'key' => 'seo_index_md');
		}		
		
		/* Not logging spider visits */
		if(!array_key_exists('spider_visit', $ignores) && !$this->settings['spider_visit'] )
		{
			$messages[] = array('level' => 'bad', 'fix' => "app=core&module=settings&section=settings&do=setting_view&conf_group={$group['conf_title_id']}", 'key' => 'spider_visit');
		}

		/* No Meta Tags */		
		if( !array_key_exists('ipseo_no_meta', $ignores) &&  empty( $metaTags ) )
		{
			$messages[] = array('level' => 'warn', 'fix' => 'app=core&module=templates&section=meta', 'key' => 'ipseo_no_meta');
		}
		
		/* Sitemap ping disabled */
		if(!array_key_exists('sitemap_ping', $ignores) && !$this->settings['sitemap_ping'])
		{
			$messages[] = array('level' => 'warn', 'fix' => 'app=core&module=settings&section=settings&&do=findsetting&key=sitemap', 'key' => 'sitemap_ping');
		}
		
		if(!array_key_exists('seo_r_on', $ignores) && !$this->settings['seo_r_on'])
		{
			$messages[] = array('level' => 'warn', 'fix' => "app=core&module=settings&section=settings&do=setting_view&conf_group={$group['conf_title_id']}", 'key' => 'seo_r_on');
		}
		
		if(!array_key_exists('htaccess_mod_rewrite', $ignores) && !$this->settings['htaccess_mod_rewrite'])
		{
			$messages[] = array('level' => 'warn', 'fix' => "app=core&module=settings&section=settings&do=setting_view&conf_group={$group['conf_title_id']}", 'key' => 'htaccess_mod_rewrite');
		}
		
		//-----------------------------------------
		// Display
		//-----------------------------------------
		
		$this->registry->output->html  = $this->html->dashboard( $messages, $ignores );
	}
	
	/**
	 * Action: Ignore Message
	 */
	public function ignoreWarning()
	{
		$ignores = ips_CacheRegistry::instance()->getCache('ipseo_ignore_messages');
		if( !$ignores )
		{
			$ignores = array();
		}

		$ignores[ $this->request['key'] ] = 1;
		
		ips_CacheRegistry::instance()->setCache( 'ipseo_ignore_messages', $ignores, array( 'array' => 1 ) );
		
		$this->registry->output->silentRedirect( ipsRegistry::$settings['base_url'] . "module=seo" );  
	}
	
	/**
	 * Action: Clear Warnings
	 */
	public function clearWarnings()
	{
		ips_CacheRegistry::instance()->setCache( 'ipseo_ignore_messages', array(), array( 'array' => 1 ) );
		
		$this->registry->output->silentRedirect( ipsRegistry::$settings['base_url'] . "module=seo" );  
	}
	
	/**
	 * Recache
	 */
	public function recache()
	{
		$current = ipsRegistry::instance()->cache()->getCache('ipseo_ignore_messages');
		
		if(!is_array($current))
		{
			$current = array();
		}
		
		ipsRegistry::instance()->cache()->setCache( 'ipseo_ignore_messages', $current,  array( 'array' => 1, 'donow' => 1 ) );
	}
	
}