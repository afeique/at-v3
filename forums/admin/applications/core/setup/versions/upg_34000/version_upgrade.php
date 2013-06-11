<?php
/**
 *
 * @class	version_upgrade
 * @brief	3.4.0 Alpha 1 Upgrade Logic
 *
 */
class version_upgrade
{
	/**
	 * Custom HTML to show
	 *
	 * @var		string
	 */
	private $_output = '';
	
	/**
	 * fetchs output
	 * 
	 * @return	string
	 */
	public function fetchOutput()
	{
		return $this->_output;
	}
	
	/**
	 * Execute selected method
	 *
	 * @param	object		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		/* Make object */
		$this->registry =  $registry;
		$this->DB       =  $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->cache    =  $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
		
		//--------------------------------
		// What are we doing?
		//--------------------------------

		switch( $this->request['workact'] )
		{
			case 'system_templates':
				$this->_writeSystemTemplates();
				break;
			case 'oldhooks':
				$this->removeOldHooks();
				break;		
			case 'seo':
			default:
				$this->_convertIPSeo();
				break;
		}
		
		/* Workact is set in the function, so if it has not been set, then we're done. The last function should unset it. */
		if ( $this->request['workact'] )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Write system templayes
	 */
	public function _writeSystemTemplates()
	{
		require_once( IPS_ROOT_PATH . 'sources/classes/output/systemTemplates.php' );/*noLibHook*/
		$systemTemplates = new systemTemplates();
		$systemTemplates->writeDefaults();
		
		$this->registry->output->addMessage( "Wrote system templates..." );
		$this->request['workact'] = '';
	}
	
	/**
	 * Convert IP.SEO
	 */
	public function _convertIPSeo() 
	{
		if ( IPSLib::appIsInstalled('ipseo') )
		{
			if ( $this->settings['seo_index_md'] )
			{
				$this->DB->insert( 'seo_meta', array( 'url' => '', 'name' => 'description', 'content' => $this->settings['seo_index_md'] ) );
			}
			if ( $this->settings['seo_index_mk'] )
			{
				$this->DB->insert( 'seo_meta', array( 'url' => '', 'name' => 'keywords', 'content' => $this->settings['seo_index_mk'] ) );
			}
			if ( $this->settings['seo_index_title'] )
			{
				$this->DB->insert( 'seo_meta', array( 'url' => '', 'name' => 'title', 'content' => $this->settings['seo_index_title'] ) );
			}
		}
		
		$this->DB->delete( 'core_sys_conf_settings', "conf_key IN( 'seo_index_md', 'seo_index_mk', 'seo_index_title', 'ipseo_ping_services', 'ipseo_guest_skin' )" );
		
		$this->DB->delete( 'core_sys_settings_titles', "conf_title_keyword='ipseo'" );
		
		if ( !$this->DB->checkForField( 'ipseo_priority', 'forums' ) )
		{
			$this->DB->addField( 'forums', 'ipseo_priority', 'varchar(3)', "''" );
		}
		
		$this->DB->delete( 'core_applications', "app_directory='ipseo'" );
		$this->DB->delete( 'upgrade_history', "upgrade_app='ipseo'" );
		
		$this->registry->output->addMessage( "Converted IP.SEO data..." );
		$this->request['workact'] = 'oldhooks';
	}
	
	/**
	 * Remove old hooks
	 *
	 * @return	@e void
	 */
	public function removeOldHooks()
	{
		/* Hooks to remove */
		$hooks		= array( 'ipseo_acronyms', 'ipseo_guest_skin', 'ipseo_meta', 'ipseo_ping_services', 'ipseo_tracking' );
		$_hookIds	= array();
		$_total		= 0;
	
		/* Get hook records */
		$this->DB->build( array( 'select' => 'hook_id', 'from' => 'core_hooks', 'where' => "hook_key IN('" . implode( "','", $hooks ) . "')" ) );
		$this->DB->execute();
	
		while( $r = $this->DB->fetch() )
		{
			$_hookIds[]	= $r['hook_id'];
		}
	
		/* Remove associated files */
		if( count($_hookIds) )
		{
			$this->DB->build( array( 'select' => 'hook_file_stored', 'from' => 'core_hooks_files', 'where' => 'hook_hook_id IN(' . implode( ',', $_hookIds ) . ')' ) );
			$this->DB->execute();
				
			while( $r = $this->DB->fetch() )
			{
				@unlink( IPS_HOOKS_PATH . $r['hook_file_stored'] );
			}
				
			/* Remove hook records */
			$this->DB->delete( 'core_hooks_files', 'hook_hook_id IN(' . implode( ',', $_hookIds ) . ')' );
			$this->DB->delete( 'core_hooks', 'hook_id IN(' . implode( ',', $_hookIds ) . ')' );
				
			$_total++;
		}
	
		/* Message */
		$this->registry->output->addMessage("{$_total} outdated hook(s) uninstalled....");
	
		/* Next Page */
		$this->request['workact'] = 'system_templates';
	}
	
	
}