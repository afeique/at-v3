<?php
/**
 * @file		ips.php			API for informing IPB that license data needs to be refreshed - called when license is renewed, etc.
 *
 * $Copyright: $
 * $License: $
 * $Author: bfarber $
 * $LastChangedDate: 2013-04-12 18:05:43 -0400 (Fri, 12 Apr 2013) $
 * $Revision: 12171 $
 * @since 		22nd February 2012
 */

define( 'IPS_ENFORCE_ACCESS', TRUE );
define( 'IPB_THIS_SCRIPT', 'public' );
require_once( '../initdata.php' );/*noLibHook*/

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );/*noLibHook*/
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );/*noLibHook*/

$registry = ipsRegistry::instance();
$registry->init();

if ( $_GET['cdnCheck'] )
{
	echo 'OK';
	exit;
}
else
{
	if ( ipsRegistry::$settings['ipb_reg_number'] and ipsRegistry::$request['key'] == ipsRegistry::$settings['ipb_reg_number'] )
	{
		switch ( ipsRegistry::$request['do'] )
		{
			case 'reset':
				ipsRegistry::DB()->update( 'cache_store', array( 'cs_rebuild' => 1 ), "cs_key='licenseData'" );
				ipsRegistry::cache()->putWithCacheLib( 'licenseData', 'rebuildCache', 200 );
				break;
				
			case 'cdnoff':
				$settings = array( 'ips_cdn' => FALSE, 'ipb_img_url' => '', 'ipb_css_url' => '', 'ipb_js_url' => '', 'upload_url' => '' );
				
				if ( IPSLib::appIsInstalled('downloads') )
				{
					$settings['idm_screenshot_url'] = str_replace( ipsRegistry::$request['url'], ipsRegistry::$settings['board_url'], $settings['idm_screenshot_url'] );
				}
				if ( IPSLib::appIsInstalled('gallery') )
				{
					$settings['gallery_images_url'] = str_replace( ipsRegistry::$request['url'], ipsRegistry::$settings['board_url'], $settings['gallery_images_url'] );
				}
							
				IPSLib::updateSettings( $settings );
				echo "OK";
				break;
				
			case 'cdnon':
				$settings = array( 'ips_cdn' => TRUE, 'ipb_img_url' => ipsRegistry::$request['url'], 'ipb_css_url' => ipsRegistry::$request['url'], 'ipb_js_url' => ipsRegistry::$request['url'], 'upload_url' => ipsRegistry::$request['url'] . '/uploads' );
				if ( IPSLib::appIsInstalled('downloads') )
				{
					if ( substr( ipsRegistry::$settings['idm_localsspath'], 0, 11 ) === '{root_path}' )
					{
						$settings['idm_screenshot_url'] = str_replace( '{root_path}', ipsRegistry::$request['url'], ipsRegistry::$settings['idm_localsspath'] );
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
							$settings['gallery_images_url'] = str_replace( '\\', '/', str_replace( $this_script, ipsRegistry::$request['url'], ipsRegistry::$settings['gallery_images_path'] ) );
						}
					}
				}
			
				IPSLib::updateSettings( $settings );
				
				if ( ipsRegistry::$settings['ips_cdn'] )
				{
					echo "OK";
				}
				else
				{
					echo "FLUSH";
				}
				break;
		}
	}
}


exit;
