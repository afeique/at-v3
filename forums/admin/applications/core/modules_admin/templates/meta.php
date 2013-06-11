<?php
/**
 * Invision Power Services
 * IP.SEO -  Meta Tags
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2010-2011 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.SEO
 * @link		http://www.invisionpower.com
 * @version		$Revision: 10721 $
 */

class admin_core_templates_meta extends ipsCommand
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
		
		$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'settemplates_meta' );
		
		ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_seo' ) );
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_seo' );
		
		//-----------------------------------------
		// What are we doing?
		//-----------------------------------------

		switch($this->request['do'])
		{
			case 'add':
			case 'edit':
				$this->form();
			break;
		
			case 'save':
				$this->save();
			break;
			
			case 'delete':
				$this->delete();
			break;
			
			default:
				$this->manage();
			break;
		}
		
		//-----------------------------------------
		// Display
		//-----------------------------------------
		
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Action: Manage Meta Tags
	 */
	protected function manage()
	{
		$metaTags = ips_CacheRegistry::instance()->getCache('meta_tags');
				
		/* Display */
		$this->registry->output->html  = $this->html->metaTags( $metaTags );
	}
	
	/**
	 * Action: Show Form
	 */
	public function form()
	{
		$page = '';
		$tags = array();
		
		if ( $this->request['do'] == 'edit' && isset($this->request['page']) )
		{
			$cache = ips_CacheRegistry::instance()->getCache('meta_tags');
			
			$page  = IPSText::base64_decode_urlSafe($this->request['page']);
						
			if ( is_array($cache[ $page ]) && count($cache[ $page ]) )
			{
				$tags = $cache[ $page ];
			}
		}
	
		/* Display */
		$this->registry->output->html  = $this->html->metaTagForm( $page, $tags );
	}
	
	/**
	 * Action: Save
	 */	
	protected function save()
	{
		/* Get Cache */
		$cache = ips_CacheRegistry::instance()->getCache('meta_tags');
		
		/* Delete any DB entries for this page as we're about to rebuild them */
		$escapedPage = $this->DB->addSlashes( $this->request['old-page'] );
		$this->DB->delete( 'seo_meta', "url='{$escapedPage}'" );
		unset( $cache[ $this->request['old-page'] ] );
		
		/* Init Page */
		if ( !$this->request['page'] )
		{
			$this->registry->output->showError( 'err_no_page' );
		}
		$cache[ $this->request['page'] ] = array();
		
		/* Insert Tags */
		$id = 0;
		do
		{
			if ( !empty( $this->request[ 'title-' . $id ] ) )
			{
				$cache[ $this->request['page'] ][ $this->request[ 'title-' . $id ] ] = $this->request[ 'content-' . $id ];
				$this->DB->insert('seo_meta', array(
					'url'		=> $this->request['page'],
					'name'		=> $this->request[ 'title-' . $id ],
					'content'	=> $this->request[ 'content-' . $id ]
					) );
			}
			
			$id++;
		}
		while ( isset( $this->request[ 'title-' . $id ] ) );
				
		/* Rebuild Cache */
		ips_CacheRegistry::instance()->setCache( 'meta_tags', serialize( $cache ) );
		
		/* Boink */
		$this->registry->output->silentRedirect( ipsRegistry::$settings['base_url'] . "module=templates&section=meta" );
	}
	
	/**
	 * Action: Delete
	 */	
	protected function delete()
	{
		/* Rebuild Cache */
		$cache = ips_CacheRegistry::instance()->getCache('meta_tags');
		unset( $cache[ $this->request['page'] ] );
		ips_CacheRegistry::instance()->setCache( 'meta_tags', serialize( $cache ) );
		
		/* Delete any DB entries for this page */
		$page  = IPSText::base64_decode_urlSafe( $this->request['page'] );
		
		$escapedPage = $this->DB->addSlashes( $page );
		$this->DB->delete( 'seo_meta', "url='{$escapedPage}'" );
		
		/* Rebuild Cache */
		$this->rebuildMetaTagCache();
		
		/* Boink */
		$this->registry->output->silentRedirect( ipsRegistry::$settings['base_url'] . "module=templates&section=meta" );
	}
	
	/**
	 * Recache
	 */
	public function rebuildMetaTagCache()
	{
		$meta = array();
		$db   = ipsRegistry::DB();
		
		$db->build(array('select' => '*', 'from' => 'seo_meta'));
		$db->execute();
		
		while($row = $db->fetch())
		{
			$meta[$row['url']][$row['name']] = $row['content'];
		}
		
		ipsRegistry::instance()->cache()->setCache( 'meta_tags', $meta,  array( 'array' => 1, 'donow' => 1 ) );
	}
}