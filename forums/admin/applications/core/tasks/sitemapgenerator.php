<?php
/**
 * @file		sitemapgenerator.php 	Sitemap Generator
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mark $
 * @since		-
 * $LastChangedDate: 2012-02-22 17:07:55 +0000 (Wed, 22 Feb 2012) $
 * @version		v3.4.5
 * $Revision: 10349 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 *
 * @class		Sitemap
 * @brief		This is the object which actually holds a sitemap for a plugin
 *
 */
class Sitemap
{
	protected $app      = null;
	protected $plugin   = null;
	protected $fileName = null;
	protected $count    = 0;
	protected $total    = 0;
	protected $sitemap  = null;
	protected $sitemaps = array();
	
	/**
	 * Constructor - Inits the sitemap
	 *
	 * @param	string	App key
	 * @param	string	Plugin key
	 */
	public function __construct($app, $plugin)
	{
		$this->app      = $app;
		$this->plugin   = $plugin;
		$this->count    = 0;
		$this->sitemaps = array();
		$this->sitemap  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		$this->sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
	}
	
	/**
	 * Add a URL to the sitemap
	 *
	 * @param	string	URL
	 * @param	int		Last modified timestamp
	 * @param	int		Priority
	 * @param	int		Change Frequency
	 */
	public function addURL($url, $lastModified = null, $priority = null, $changeFrequency = null)
	{
		if( $this->count >= 10000 )
		{
			$this->storeCurrentSitemap();
		}
		
		// Prepare last modified string:
		if(!is_null($lastModified) && $lastModified != 0)
		{
			$lastModified = date('Y-m-d', $lastModified);
			$lastModified = "\t\t<lastmod>{$lastModified}</lastmod>\r\n";
		}
		else
		{
			$lastModified = '';
		}
		
		// Prepare priority string:
		if(!is_null($priority))
		{
			$priority = str_replace(',', '.', (string)$priority);
			$priority = "\t\t<priority>{$priority}</priority>\r\n";
		}
		else
		{
			$priority = '';
		}
		
		// Prepare change frequency string:
		if(!is_null($changeFrequency))
		{
			$changeFrequency = "\t\t<changefreq>{$changeFrequency}</changefreq>\r\n";
		}
		else
		{
			$changeFrequency = '';
		}
		
		// Prepare URL:
		$url = htmlspecialchars($url);
		
		
		$url = "\t<url>\r\n\t\t<loc>{$url}</loc>\r\n{$lastModified}{$priority}{$changeFrequency}\t</url>\r\n";
		
		$this->sitemap .= $url;
		
		$this->count++;
		$this->total++;
		return $this->total;
	}
	
	/**
	 * Store the current sitemap
	 */	
	protected function storeCurrentSitemap()
	{
		$this->sitemap .= '</urlset>';

		if(count($this->sitemaps))
		{
			$_num = '_' . (count($this->sitemaps) + 1);
		}
		else
		{
			$_num = '';
		}

		$file = 'sitemap_' . $this->app . '_' . $this->plugin . $_num . '.xml';

		// GZip sitemap:
		if(function_exists('gzencode'))
		{
			$file          .= '.gz';
			$this->sitemap  = gzencode($this->sitemap);
		}

		// Remove sitemap if no URLs were added:
		if($this->count == 0)
		{
			@unlink( IPS_CACHE_PATH . 'cache/' . $file);
		}
		else
		{
			$this->sitemaps[] = array('file' => $file, 'modified' => time(), 'count' => $this->count, 'changed' => true);
			
			if(!@file_put_contents( IPS_CACHE_PATH . 'cache/' . $file, $this->sitemap))
			{
				throw new ipSeo_Sitemap_File_Exception( $this->lang->words['smt_nowrite'] . $file );
			}
			
			@chmod( IPS_CACHE_PATH . 'cache/' . $file, IPS_FILE_PERMISSION );
		}
		
		$this->count    = 0;
		$this->sitemap  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		$this->sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
	}
	
	/**
	 * Save the final sitemap
	 *
	 * @return	array	Sitemaps
	 */
	public function save()
	{
		$this->storeCurrentSitemap();
		return $this->sitemaps;	
	}
}

/**
 *
 * @class		ipSeo_Sitemap_File_Exception
 * @brief		Simple Exception class which the plugins use (we really could just use Exception, but we'll blame Dan for this)
 *
 */
class ipSeo_Sitemap_File_Exception extends Exception{}

/**
 *
 * @interface	iSitemapGeneratorPlugin
 * @brief		Interface that our plugins will use
 *
 */
interface iSitemapGeneratorPlugin
{
	public function __construct(ipsRegistry $registry, Sitemap $sitemap);
	public function generate();
}

/**
 *
 * @class		ipseoSitemapPlugin
 * @brief		Abstract class that the plugins will extend
 *
 */
abstract class ipseoSitemapPlugin implements iSitemapGeneratorPlugin
{
	protected $registry		= null;
	protected $DB			= null;
	protected $settings		= null;
	protected $request		= null;
	protected $lang			= null;
	protected $member		= null;
	protected $memberData	= null;
	protected $cache		= null;
	protected $caches		= null;
	protected $sitemap		= null;
	
	/**
	 * Constructor - Initialises the sitemap plugin class with core IP.Board classes and the sitemap class
	 *
	 * @param	ipsRegistry
	 * @param	Sitemap
	 */
	public function __construct(ipsRegistry $registry, Sitemap $sitemap)
	{
		$this->registry = ipsRegistry::instance();
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
		
		$this->sitemap = $sitemap;
	}
	
	/**
	* Generate your sitemap and pass the data to the sitemap class to generate the file.
	*/
	public function generate() {}
}

/**
 *
 * @class		ipSeo_SitemapGenerator
 * @brief		Some utility classes
 *
 */
class ipSeo_SitemapGenerator
{
	protected $registry		= null;
	protected $DB			= null;
	protected $settings		= null;
	protected $request		= null;
	protected $lang			= null;
	protected $member		= null;
	protected $memberData	= null;
	protected $cache		= null;
	protected $caches		= null;
	protected $sitemapIndex = null;
	protected $hadErrors    = false;
	
	/**
	 * Are we running this as a cron?
	 *
	 * @return	bool
	 */
	public static function isCronJob()
	{
		return strpos( $_SERVER['argv'][0], 'task.php' ) !== FALSE;
	}
	
	/**
	* Constructor - Initialises the sitemap plugin class with core IP.Board classes and the sitemap class.
	*/
	public function __construct(ipsRegistry $registry)
	{
		// Standard IP.Board classes:
		$this->registry = ipsRegistry::instance();
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
		
		// Sitemap generator:
		$this->sitemapIndex = array();
	}
	
}

/**
 *
 * @class		ipSeo_FURL
 * @brief		Class to generate FURL links
 * @todo		Remove this and do it properly
 *
 */
class ipSeo_FURL
{
	protected static $_seoTemplates = null;
	
	protected static function init()
	{
		if ( is_file( FURL_CACHE_PATH ) )
		{
			$templates = array();
			require( FURL_CACHE_PATH );/*noLibHook*/
			self::$_seoTemplates = $templates;
		}
		else
		{
			/* Attempt to write it */
			self::$_seoTemplates = IPSLib::buildFurlTemplates();
			
			try
			{
				IPSLib::cacheFurlTemplates();
			}
			catch( Exception $e )
			{
			}
		}
	}
	
	public static function build($url, $urlType = 'public', $seoTitle = '', $seoTemplate = '')
	{
		if(is_null(self::$_seoTemplates))
		{
			self::init();
		}
		
		return self::format(self::prepare($url, $urlType), $seoTitle, $seoTemplate);
	}
	
	protected static function format($url, $seoTitle = '', $seoTemplate = '')
	{
		if(!ipsRegistry::$settings['use_friendly_urls'])
 		{
 			return $url;
 		}
 		
		$_template	= false;
		$seoTitle	= ( ! empty( $seoTitle ) && ! is_array( $seoTitle ) ) ? array( $seoTitle ) : $seoTitle;
	
		if ( ipsRegistry::$settings['use_friendly_urls'] AND is_array( $seoTitle ) && count( $seoTitle ) )
		{
			/* SEO Tweak - if default app is forums then don't bother with act=idx nonsense */
			if ( IPS_DEFAULT_APP == 'forums' )
			{
				if ( stristr( $url, 'act=idx' ) )
				{
					$url = str_ireplace( array( 'index.php?act=idx', '?act=idx', 'act=idx' ), '', $url );
				}
			}
			
			if ( $seoTemplate AND isset(self::$_seoTemplates[ $seoTemplate ]) )
			{
				$_template = $seoTemplate;
			}

			/* Need to search for one - fast? */
			if ( $_template === FALSE )
			{
				/* Search for one, then. Possibly a bit slower than we'd like! */
				foreach( self::$_seoTemplates as $key => $data )
				{
					if ( stristr( str_replace( ipsRegistry::$settings['board_url'], '', $url ), $key ) )
					{ 
						$_template = $key;
						break;
					}
				}
			}

			/* Got one to work with? */
			if ( $_template !== FALSE )
			{
				if ( count( $seoTitle ) == 1 && ( substr( $seoTitle[0], 0, 2 ) == '%%' AND substr( $seoTitle[0], -2 ) == '%%' ) )
				{
					$seoTitle[0] = IPSText::makeSeoTitle( substr( $seoTitle[0], 2, -2 ) );
				}
				
				/* Do we need to encode? */
				if ( IPS_DOC_CHAR_SET != 'UTF-8' )
				{
					foreach( $seoTitle as $id => $item )
					{
						$seoTitle[ $id ] = urlencode( $item );
					}
				}
				
				if ( count( $seoTitle ) == 1 )
				{
					$replace = str_replace( '#{__title__}', IPSText::convertUnicode( $seoTitle[0] ), self::$_seoTemplates[ $_template ]['out'][1] ); // See http://community.invisionpower.com/resources/bugs.html/_/ip-board/transliteration-r37146
				}
				else
				{
					$replace = self::$_seoTemplates[ $_template ]['out'][1];
					
					foreach( $seoTitle as $id => $item )
					{
						$replace = str_replace( '#{__title-' . $id . '__}', IPSText::convertUnicode( $item ), $replace ); // See http://community.invisionpower.com/resources/bugs.html/_/ip-board/transliteration-r37146
					}
				}
				
				$url     = preg_replace( self::$_seoTemplates[ $_template ]['out'][0], $replace, $url );
				$_anchor = '';
				$__url   = $url;

				/* Protect html entities */
				$url = preg_replace( '/&#(\d)/', "~|~\\1", $url );

				if ( strstr( $url, '&' ) )
				{
					$restUrl = substr( $url, strpos( $url, '&' ) );

					$url     = substr( $url, 0, strpos( $url, '&' ) );
				}
				else
				{
					$restUrl = '';
				}

				/* Anchor */
				if ( strstr( $restUrl, '#' ) )
				{
					$_anchor = substr( $restUrl, strpos( $restUrl, '#' ) );
					$restUrl = substr( $restUrl, 0, strpos( $restUrl, '#' ) );
				}

				switch ( ipsRegistry::$settings['url_type'] )
				{
					case 'path_info':
						if ( ipsRegistry::$settings['htaccess_mod_rewrite'] )
						{
							$url = str_replace( IPS_PUBLIC_SCRIPT . '?', '', $url );
						}
						else
						{
							$url = str_replace( IPS_PUBLIC_SCRIPT . '?', IPS_PUBLIC_SCRIPT . '/', $url );
						}
					break;
					default:
					case 'query_string':
						$url = str_replace( IPS_PUBLIC_SCRIPT . '?', IPS_PUBLIC_SCRIPT . '?/', $url );
					break;
				}

				/* Ensure that if the seoTitle is missing there is no double slash */
				# http://localhost/invisionboard3/user/1//
				# http://localhost/invisionboard3/user/1/mattm/
				if ( substr( $url, -2 ) == '//' )
				{
					$url = substr( $url, 0, -1 );
				}

				/* Others... */
				if ( $restUrl )
				{
					$_url  = str_replace( '&amp;', '&', str_replace( '?', '', $restUrl ) );
					$_data = explode( "&", $_url );
					$_add  = array();
					$_page = '';
				
					foreach( $_data as $k )
					{
						if ( strstr( $k, '=' ) )
						{
							list( $kk, $vv ) = explode( '=', $k );

							/* Catch page */
							if ( self::$_seoTemplates[ $_template ]['isPagesMode'] && $kk == 'page' )
							{
								$_page .= self::$_seoTemplates['__data__']['varPage'] . $vv;
							}
							else if ( $kk and $vv )
							{
								$_add[] = $kk . self::$_seoTemplates['__data__']['varSep'] . $vv;
							}
						}
					} 
						
					/* Got anything to add?... */
					if ( count( $_add ) OR $_page )
					{
						if ( $_page )
						{
							if ( strrpos( $url, self::$_seoTemplates['__data__']['end'] ) + strlen( self::$_seoTemplates['__data__']['end'] ) == strlen( $url ) )
							{
								$url = substr( $url, 0, -1 );
							}
							
							$url .= self::$_seoTemplates['__data__']['end'] . $_page;
						}

						if ( count( $_add ) )
						{
							$url .= self::$_seoTemplates['__data__']['varBlock'] . implode( self::$_seoTemplates['__data__']['varSep'], $_add );
						}
					}
				}

				/* anchor? */
				if ( $_anchor )
				{
					$url .= $_anchor;
				}

				/* Protect html entities */
				$url = str_replace( '~|~', '&#', $url );

				return $url;
			} # / template
		}
			
		return $url;
	}
	
	protected static function prepare($url, $urlBase)
	{
		$base = '';

		if($urlBase)
		{
			switch($urlBase)
			{
				default:
				case 'none':
					$base = '';
				break;
				case 'public':
					if ( IN_ACP )
					{
						$base = ipsRegistry::$settings['public_url'];
					}
					else
					{
						$base = ipsRegistry::$settings['base_url'];
					}
				break;
				case 'publicWithApp':
					$base = ipsRegistry::$settings['base_url_with_app'];
				break;
				case 'publicNoSession':
					$base = ipsRegistry::$settings['_original_base_url'].'/index.'.ipsRegistry::$settings['php_ext'] . '?';
				break;
				case 'admin':
					$base = ipsRegistry::$settings['base_url'];
				break;
				case 'https':
					$base = str_replace( 'http://', 'https://', ipsRegistry::$settings['base_url'] );
				break;
			}
		}
		
		return $base . $url;
	}
}

/**
 *
 * @class		task_item
 * @brief		Sitemap Generator
 *
 */
class task_item
{
	/**
	 * Log Contents
	 *
	 * @var @string
	 */
	private $log = '';

	/**
	 * Initialize the sitemap generator task.
	 *
	 * @access    public
	 * @param     object        ipsRegistry reference
	 * @param     object        Parent task class
	 * @param    array         This task data
	 * @return    void
	 */
	public function __construct( ipsRegistry $registry, $class, $task )
	{	
		$this->registry  = $registry;
	    $this->class     = $class;
	    $this->task      = $task;
	    
	    $this->lang		 = $registry->getClass('class_localization');
	    $this->lang->loadLanguageFile( array( 'admin_seo' ), 'core' );
	}
	
	/** 
	 * Log Something
	 *
	 * @param	string	Message
	 */
	private function log( $message )
	{
		$this->log .= $message . "<br />";
	}

  	/**
	 * Run the sitemap generator task.
	 *
	 * @access    public
	 * @return    void
	 */
	public function runTask()
	{	
		
		//-----------------------------------------
		// Check the sitemap file is writable
		// before going through all the hoohah
		//-----------------------------------------
		
		$sitemapFile = ( ipsRegistry::$settings['sitemap_path'] ? ipsRegistry::$settings['sitemap_path'] : DOC_IPS_ROOT_PATH ) . 'sitemap.xml';
		if( !is_writable( $sitemapFile ) )
		{
			$this->log( $this->lang->words['task_path_not_write'] );
			return false;
		}
		
		//-----------------------------------------
		// Generate
		//-----------------------------------------
		
		/* Process as guest */	
		ipsRegistry::member()->sessionClass()->setMember( 0 );
		
		/* Init */
		$this->log( $this->lang->words['smt_start'] );
		
		/* Run Plugins */
		foreach ( IPSLib::getEnabledApplications() as $app )
		{
			/* Any Plugins? */
			if ( is_dir( IPSlib::getAppDir( $app['app_directory'] ) . '/extensions/sitemapPlugins' ) )
			{
				/* Yes - run them! */
				$this->log( sprintf( $this->lang->words['smt_app'], $app['app_directory'] ) );
			
				$directory = new DirectoryIterator( IPSlib::getAppDir( $app['app_directory'] ) . '/extensions/sitemapPlugins' );
				foreach ( $directory as $file )
				{
					if ( $file->isFile() and substr( $file, -4 ) === '.php' )
					{
						require_once( $file->getPathName() );
						$pluginName = str_replace( '.php', '', $file->getBaseName() );
						$className = 'sitemap_' . $app['app_directory'] . '_' . str_replace( '.php', '', $file->getBaseName() );
						
						if ( class_exists( $className ) )
						{
							/* Init */
							$this->log( sprintf( $this->lang->words['smt_plugin'], $pluginName ) );
							
							$classToLoad	= IPSLib::loadLibrary( '', 'Sitemap' );
							$sitemap		= new $classToLoad( $app['app_directory'], $pluginName );
							$classToLoad	= IPSLib::loadLibrary( '', $className );
							$plugin			= new $classToLoad( $this->registry, $sitemap );
							
							/* Run the plugin */
							try
							{
								$plugin->generate();
								$sitemaps = $sitemap->save();
							}
							catch(ipSeo_Sitemap_File_Exception $ex)
							{
								$this->log('- - ' . $ex->getMessage());
								continue;
							}
							
							/* Log */
							$this->log( sprintf( $this->lang->words['smt_generated'], count( $sitemaps ) ) );
							$i = 0;
							foreach($sitemaps as $details)
							{
								$this->sitemapIndex[] = $details;
	
								if($details['changed'])
								{
									$this->log( sprintf( $this->lang->words['smt_file'], (++$i), $details['count'] ) );
								}
							}
						}
					}
				}
			}
			else
			{
				/* Nope */
				$this->log( sprintf( $this->lang->words['smt_no_plugins'], $app['app_directory'] ) );
			}
		}
		$this->log( $this->lang->words['smt_finished'] );

		/* Put it all togather */
		$sitemapIndex  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		$sitemapIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
		if( is_array($this->sitemapIndex) AND count($this->sitemapIndex) )
		{
			foreach( $this->sitemapIndex as $sitemap )
			{
				// Skip empty sitemaps:
				if($sitemap['count'] == 0)
				{
					continue;
				}
				
				// Write sitemap entry:
				$url = ipsRegistry::$settings['board_url'] . '/index.php?app=core&module=global&section=sitemap&sitemap=' . $sitemap['file'];
				
				$sitemapIndex .= '<sitemap>' . PHP_EOL;
				$sitemapIndex .= '<loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
				$sitemapIndex .= '<lastmod>' . date('c', $sitemap['modified']) . '</lastmod>' . PHP_EOL;
				$sitemapIndex .= '</sitemap>' . PHP_EOL;
			}
		}
		$sitemapIndex .= '</sitemapindex>';
		
		/* Write */
		@file_put_contents( $sitemapFile, $sitemapIndex );
		@chmod( $sitemapFile, IPS_FILE_PERMISSION );
		$this->log( sprintf( $this->lang->words['smt_written'], $sitemapFile ) );
		
		//-----------------------------------------
		// Ping services
		//-----------------------------------------
		
		if( ipsRegistry::$settings['sitemap_ping'] )
		{	
			$this->log( $this->lang->words['smt_pinging'] );
		
			$rtn = true;
			$sitemapUrl    = urlencode( ( ipsRegistry::$settings['sitemap_url'] ? ipsRegistry::$settings['sitemap_url'] : ipsRegistry::$settings['board_url'] . '/' ) . 'sitemap.xml');
			
			$classToLoad   = IPSLib::loadLibrary( IPS_KERNEL_PATH . 'classFileManagement.php', 'classFileManagement' );
			$http          = new $classToLoad();
			$http->timeout = 5;
						
			// Ping Google:
			@$http->getFileContents('http://www.google.com/webmasters/tools/ping?sitemap='.$sitemapUrl);
			if($http->http_status_code != 200)
			{
				$rtn = false;
				$this->log( sprintf( $this->lang->words['smt_ping_fail'], "Google" ) );
			}
			else
			{
				$this->log( sprintf( $this->lang->words['smt_ping_ok'], "Google" ) );
			}
			
			// Ping Bing:
			@$http->getFileContents('http://www.bing.com/webmaster/ping.aspx?siteMap='.$sitemapUrl);	
			if($http->http_status_code != 200)
			{
				$rtn = false;
				$this->log( sprintf( $this->lang->words['smt_ping_fail'], "Bing" ) );
			}
			else
			{
				$this->log( sprintf( $this->lang->words['smt_ping_ok'], "Bing" ) );
			}
					
			// Ping Ask:
			@$http->getFileContents('http://submissions.ask.com/ping?sitemap='.$sitemapUrl);
			if($http->http_status_code != 200)
			{
				$rtn = false;
				$this->log( sprintf( $this->lang->words['smt_ping_fail'], "Ask" ) );
			}
			else
			{
				$this->log( sprintf( $this->lang->words['smt_ping_ok'], "Ask" ) );
			}
			
			// Ping Moreover:
			@$http->getFileContents('http://api.moreover.com/ping?u='.$sitemapUrl);
			if($http->http_status_code != 200)
			{
				$rtn = false;
				$this->log( sprintf( $this->lang->words['smt_ping_fail'], "Moreover" ) );
			}
			else
			{
				$this->log( sprintf( $this->lang->words['smt_ping_ok'], "Moreover" ) );
			}
		}
							
		//-----------------------------------------
		// Finish
		//-----------------------------------------
		
		$this->log( $this->lang->words['smt_done'] );
		
		$this->class->appendTaskLog( $this->task, $this->log );
		$this->class->unlockTask( $this->task );
		return true;
	}
}