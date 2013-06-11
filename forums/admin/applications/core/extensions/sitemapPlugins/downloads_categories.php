<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_downloads_categories extends ipseoSitemapPlugin
{
	public function generate()
	{
		if(!IPSLib::appIsInstalled('downloads') || $this->settings['sitemap_priority_downloads_categories'] == 0)
		{
			return;
		}
				
		// Get categories:
		$permCheck = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'p.perm_view', true);
		
		$this->DB->build(array(	'select'	=> 'c.cid, c.cname_furl',
								'from'		=> array('downloads_categories' => 'c'),
								'add_join'	=> array( array(
												'from'	=> array('permission_index' => 'p'),
												'where'	=> "(p.app = 'downloads' AND p.perm_type = 'cat' AND p.perm_type_id = c.cid)",
												'type'	=> 'left')),
								'where'		=> "c.copen = 1 AND ({$permCheck})"));
		$this->DB->execute();
				
		// Add to sitemap:															
		while($cat = $this->DB->fetch())
		{
			
			$url = $this->settings['board_url'] . '/index.php?app=downloads&showcat=' . $cat['cid'];
			$url = ipSeo_FURL::build($url, 'none', $cat['cname_furl'], 'idmshowcat');
			//$url = ipsRegistry::getClass('output')->buildSEOUrl($url, 'none', $cat['cname_furl'], 'idmshowcat');
			$this->sitemap->addURL($url, null, $this->settings['sitemap_priority_downloads_categories']);
		}
	}
}