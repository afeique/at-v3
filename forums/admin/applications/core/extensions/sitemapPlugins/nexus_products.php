<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_nexus_products extends ipseoSitemapPlugin
{
	public function generate()
	{
		if(!IPSLib::appIsInstalled('nexus') || $this->settings['sitemap_priority_nexus_products'] == 0)
		{
			return;
		}
				
		// Get categories:
		$this->DB->build(array(	'select'	=> 'p_id, p_name, p_seo_name',
								'from'		=> 'nexus_packages',
								'where'		=> 'p_store=1 AND ' . $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'p_member_groups' ),
							) );
		$this->DB->execute();
				
		// Add to sitemap:															
		while( $row = $this->DB->fetch() )
		{
			$url = $this->settings['board_url'] . '/index.php?app=nexus&module=payments&section=store&do=item&id=' . $row['p_id'];
			$url = ipSeo_FURL::build( $url, 'none', $row['p_seo_name'], 'storeitem' );

			$this->sitemap->addURL( $url, null, $this->settings['sitemap_priority_nexus_products'] );
		}
	}
}