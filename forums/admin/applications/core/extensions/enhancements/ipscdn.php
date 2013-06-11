<?php
/**
 * @file		ipscdn.php 	Community Enhancements - IPS CDN
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mark $
 * @since		24 July 2012
 * $LastChangedDate: 2012-06-20 10:50:23 +0100 (Wed, 20 Jun 2012) $
 * @version		v3.4.5
 * $Revision: 10952 $
 */

/**
 *
 * @class		enhancements_core_ipscdn
 * @brief		Community Enhancements - IPS CDN
 */
class enhancements_core_ipscdn
{
	/**
	 * Constructor
	 *
	 * @param	ipsRegistry
	 */
	public function __construct( $registry )
	{
		$this->title = $registry->getClass('class_localization')->words['enhancements_ipscdn'];
		$this->description = $registry->getClass('class_localization')->words['enhancements_ipscdn_desc'];
		$this->icon = '';
		$this->enabled = ipsRegistry::$settings['ips_cdn'];
		
		$this->html = $registry->output->loadTemplate( 'cp_skin_applications' );
	}
	
	/**
	 * Edit Settings
	 */
	public function editSettings()
	{
		require_once( IPS_ROOT_PATH . 'sources/classes/skins/skinFunctions.php' );/*noLibHook*/
		require_once( IPS_ROOT_PATH . 'sources/classes/skins/skinCaching.php' );/*noLibHook*/
			
		$skinFunctions = new skinCaching( ipsRegistry::instance() );
			
		if ( ipsRegistry::$request['recache'] )
		{
			$skinFunctions->flushipscdn();
			
			ipsRegistry::getClass('output')->redirect( ipsRegistry::$settings['_base_url'] . "app=core&amp;module=applications&amp;section=enhancements&amp;do=edit&amp;service=enhancements_core_ipscdn", ipsRegistry::getClass('class_localization')->words['cdn_recached'] );
		}
		
		if ( ipsRegistry::$request['disable'] )
		{
			IPSLib::updateSettings( array( 'ips_cdn' => FALSE, 'ipb_img_url' => '', 'ipb_css_url' => '', 'ipb_js_url' => '', 'upload_url' => '' ) );
			
			if ( IPSLib::appIsInstalled('gallery') )
			{
				$this_script = str_replace( '\\', '/', getenv( 'SCRIPT_FILENAME' ) );
				$url         = ipsRegistry::$settings['_original_base_url'];
				
				if( $this_script )
				{
					$this_script = str_replace( '/'.CP_DIRECTORY.'/index.php', '', $this_script );
					if ( substr( ipsRegistry::$settings['gallery_images_path'], 0, strlen( $this_script ) ) === $this_script )
					{
						$url = str_replace( '\\', '/', str_replace( $this_script, $url, ipsRegistry::$settings['gallery_images_path'] ) );
					}
				}
				else
				{
					$url .= '/uploads';
				}

				IPSLib::updateSettings( array( 'gallery_images_path' => $url ) );
			}
					
			IPSContentCache::truncate( 'post' );
			IPSContentCache::truncate( 'sig' );
			
			/* Set skin sets to recache */
			$skinFunctions->flagSetForRecache();
			
			ipsRegistry::getClass('output')->redirect( ipsRegistry::$settings['_base_url'] . "app=core&amp;module=applications&amp;section=enhancements", ipsRegistry::getClass('class_localization')->words['cdn_disabled'] );
			return;
		}
	
		if ( !ipsRegistry::$settings['ipb_reg_number'] )
		{
			ipsRegistry::getClass('output')->showError( sprintf( ipsRegistry::getClass('class_localization')->words['enhancements_ipscdn_error_nokey'], ipsRegistry::getClass('output')->buildUrl('app=core&module=tools&section=licensekey', 'admin') ) );
		}
				
		$classToLoad	= IPSLib::loadLibrary( IPS_KERNEL_PATH . 'classFileManagement.php', 'classFileManagement' );
		$file			= new $classToLoad();
		$ping			= $file->getFileContents( "http://license.invisionpower.com/?a=cdn&key=" . ipsRegistry::$settings['ipb_reg_number'] );
		
		if ( $json = @json_decode( $ping, TRUE ) )
		{
			if ( $json['ENABLED'] and $json['BYTES'] > 0 )
			{
				if ( !ipsRegistry::$settings['ips_cdn'] and !ipsRegistry::$request['enable'] )
				{
					return $this->html->cdnInactive( $json );
				}
				else
				{			
					$settings = array( 'ips_cdn' => TRUE, 'ipb_img_url' => $json['URL'], 'ipb_css_url' => rtrim( $json['URL'], '/' ) . '/', 'ipb_js_url' => rtrim( $json['URL'], '/' ) . '/', 'upload_url' => $json['URL'] . '/uploads' );
					if ( IPSLib::appIsInstalled('downloads') )
					{
						if ( substr( ipsRegistry::$settings['idm_localsspath'], 0, 11 ) === '{root_path}' )
						{
							$settings['idm_screenshot_url'] = str_replace( '{root_path}', $json['URL'], ipsRegistry::$settings['idm_localsspath'] );
						}
					}
					if ( IPSLib::appIsInstalled('gallery') )
					{
						$this_script = str_replace( '\\', '/', getenv( 'SCRIPT_FILENAME' ) );
						if( $this_script )
						{
							$this_script = str_replace( '/'.CP_DIRECTORY.'/index.php', '', $this_script );
							if ( substr( ipsRegistry::$settings['gallery_images_path'], 0, strlen( $this_script ) ) === $this_script )
							{
								$settings['gallery_images_url'] = str_replace( '\\', '/', str_replace( $this_script, $json['URL'], ipsRegistry::$settings['gallery_images_path'] ) );
							}
						}
					}
					
					$_settings = array();
					foreach ( $settings as $k => $v )
					{
						if ( ipsRegistry::$settings[ $k ] != $v )
						{
							$_settings[ $k ] = $v;
						}
					}
					
					if ( !empty( $_settings ) )
					{
						IPSLib::updateSettings( $settings );
					}
					
					/* Set skin sets to recache */
					$skinFunctions->flagSetForRecache();
				}
			}
			else
			{
				$licenseData = ipsRegistry::cache()->getCache('licenseData');
				if ( $licenseData['key']['url'] != ipsRegistry::$settings['board_url'] )
				{
					ipsRegistry::getClass('output')->showError( ipsRegistry::getClass('class_localization')->words['enhancements_ipscdn_error_url'] );
				}
				
				/* Set skin sets to recache */
				$skinFunctions->flagSetForRecache();
			
				return $this->html->cdnNotEnabled( $json );
			}
		}
		else
		{
			ipsRegistry::getClass('output')->showError( sprintf( ipsRegistry::getClass('class_localization')->words['enhancements_ipscdn_error_key'], ipsRegistry::getClass('output')->buildUrl('app=core&module=tools&section=licensekey', 'admin') ) );
		}
				
		return $this->html->cdnOverview( $json );
	}
}