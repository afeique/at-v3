<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_downloads_files extends ipseoSitemapPlugin
{
	public function generate()
	{
		if(!IPSLib::appIsInstalled('downloads') || $this->settings['sitemap_priority_downloads_files'] == 0)
		{
			return;
		}
				
		$addedCount = 0;	
		$limitCount = 0;																
		while($addedCount < $this->settings['sitemap_count_downloads_files'])
		{
			if(ipSeo_SitemapGenerator::isCronJob())
			{
				sleep(0.5);
			}
			
			// Get files:
			$permCheck = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'p.perm_2', true );

			$this->DB->build(array(	'select'	=> 'f.file_id, f.file_name_furl, f.file_updated',
									'from'		=> array('downloads_files' => 'f'),
									'add_join'	=> array( 
													array(
														'from'	=> array('downloads_categories' => 'c'),
														'where'	=> "c.cid = f.file_cat",
														'type'	=> 'left'),
													array(
														'from'	=> array('permission_index' => 'p'),
														'where'	=> "(p.app = 'downloads' AND p.perm_type = 'cat' AND p.perm_type_id = c.cid)",
														'type'	=> 'left'),
														),
									'where'		=> "f.file_broken = 0 AND file_open = 1 AND c.copen = 1 AND ({$permCheck})",
									'order'		=> 'f.file_updated DESC',
									'limit'		=> array($limitCount, 100)));
			$result = $this->DB->execute();
				
			// Add blogs to sitemap:
			while($file = $this->DB->fetch($result))
			{
				if ( ! $file['file_updated'] )
				{
					$file['file_updated'] = $file['file_submitted'];
				}
				
				$url = $this->settings['board_url'] . '/index.php?app=downloads&showfile=' . $file['file_id'];
				$url = ipSeo_FURL::build($url, 'none', $file['file_name_furl'], 'idmshowfile');
				//$url = ipsRegistry::getClass('output')->buildSEOUrl($url, 'none', $file['file_name_furl'], 'idmshowfile');
				$addedCount = $this->sitemap->addURL($url, $file['file_updated'], $this->settings['sitemap_priority_downloads_files']);
			}
			
			$limitCount += 100;
			
			// If we've got back less rows than expected, we've probably got no more to pull:
			if($this->DB->getTotalRows($result) < 100)
			{
				break;
			}
		}
	}
}