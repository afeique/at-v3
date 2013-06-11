<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_nexus_categories extends ipseoSitemapPlugin
{
	public function generate()
	{
		if(!IPSLib::appIsInstalled('nexus') || $this->settings['sitemap_priority_nexus_categories'] == 0)
		{
			return;
		}
						
		// Get categories:
		$this->DB->build(array(	'select'	=> 'pg_id, pg_name, pg_seo_name',
								'from'		=> 'nexus_package_groups',
							) );
		$this->DB->execute();
				
		// Add to sitemap:															
		while( $row = $this->DB->fetch() )
		{
			$url = $this->settings['board_url'] . '/index.php?app=nexus&module=payments&cat=' . $row['pg_id'];
			$url = ipSeo_FURL::build( $url, 'none', $row['pg_seo_name'], 'storecat' );

			$this->sitemap->addURL( $url, null, $this->settings['sitemap_priority_nexus_categories'] );
		}
	}
}