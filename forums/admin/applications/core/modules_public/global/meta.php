<?php
/**
 * Invision Power Services
 * IP.SEO - Manage Meta Tags
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 *
 * @author 		$Author: bfarber $ (Orginal: Mark)
 * @copyright	© 2011 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.SEO
 * @link		http://www.invisionpower.com
 * @since		15th August 2011
 * @version		$Revision: 10721 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}

class public_core_global_meta extends ipsCommand
{
	
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	@e void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		if ( $this->request['do'] != 'end' )
		{
			/* Are we a logged in admin? */
			if ( empty($this->memberData['member_id']) || empty($this->memberData['g_access_cp']) )
			{
				$this->registry->output->showError( 'meta_editor_no_admin', 'IPSEO-META-ADMIN' );
			}
			
			/* Permission Check */
			require_once IPS_ROOT_PATH . 'sources/classes/class_permissions.php';
			$class_permissions = new class_permissions( $this->registry );
			$class_permissions->checkPermissionAutoMsg( 'settemplates_meta', 'core', 'templates' );	
		}
		
		switch ( $this->request['do'] )
		{
			case 'init':
				$this->init();
				break;
				
			case 'save':
				$this->save();
				break;
				
			case 'end':
				$this->end();
				break;
		}
	}
	
	/**
	 * Activates the live meta editor
	 * 
	 * @return	@e void
	 */
	public function init()
	{
		/* Enable live meta editor and redirect */
		IPSMember::packMemberCache( $this->memberData['member_id'], array( 'ipseo_live_meta_edit' => 1 ) );
		
		$this->registry->output->silentRedirect( $this->settings['base_url'] );
	}
	
	/**
	 * Saves the meta tags for the page
	 * 
	 * @return	@e void
	 */
	public function save()
	{
		//-----------------------------------------
		// Save em
		//-----------------------------------------
	
		/* Delete any DB entries for this page as we're about to rebuild them */
		$escapedPage = $this->DB->addSlashes( $this->request['url'] );
		$this->DB->delete( 'seo_meta', "url='{$escapedPage}'" );
				
		/* Insert Tags */
		foreach( $this->request['meta-tags-title'] as $k => $v )
		{
			if ( $v )
			{
				$cache[ $this->request['url'] ][ $v ] = $this->request['meta-tags-content'][ $k ];
				$this->DB->insert('seo_meta', array(
					'url'		=> $this->request['url'],
					'name'		=> $v,
					'content'	=> $this->request['meta-tags-content'][ $k ]
					) );
			}
		}
		
		/* Rebuild Cache */
		ips_CacheRegistry::instance()->rebuildCache( 'meta_tags' );
				
		/* Boink */
		$this->registry->output->silentRedirect( ipsRegistry::$settings['base_url'] . $this->request['url'] );
	
	}
	
	/**
	 * Disables the live meta editor
	 * 
	 * @return	@e void
	 */
	public function end()
	{
		IPSMember::packMemberCache( $this->memberData['member_id'], array( 'ipseo_live_meta_edit' => 0 ) );
		
		$this->registry->output->silentRedirect( ipsRegistry::$settings['base_url'] . $this->request['url'] );
	}
}