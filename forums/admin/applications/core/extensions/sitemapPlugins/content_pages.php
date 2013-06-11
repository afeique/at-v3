<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_content_pages extends ipseoSitemapPlugin
{
	public function generate()
	{
		if(!IPSLib::appIsInstalled('ccs'))
		{
			return;
		}
		
		$maxPages = 10000;
		$curPages = 0;	
			
		
		while($curPages < $maxPages)
		{
			$permCheck = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'page_view_perms', true);
			
			$this->DB->build(array(
									'select'	=> '*', 
									'from'		=> 'ccs_pages', 
									'where'		=> "({$permCheck}) AND page_content_type = 'page'",
									'order'		=> 'page_last_edited DESC',
									'limit'		=> array($curPages, 100)));
			
			$result = $this->DB->execute();

			if($result)
			{
				// Add the resulting rows to the sitemap:
				while($row = $this->DB->fetch($result))
				{
					if(!$this->registry->isClassLoaded('ccsFunctions')) 
					{ 
						$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('ccs') . '/sources/functions.php', 'ccsFunctions', 'ccs' );
						$this->registry->setClass('ccsFunctions', new $classToLoad($this->registry));
					}
					$url = $this->registry->ccsFunctions->returnPageUrl($row);
					
					$priority = ($row['page_folder'] == '' && $row['page_seo_name'] == $this->settings['ccs_default_page']) ? $this->settings['sitemap_priority_ccs_index'] : $this->settings['sitemap_priority_ccs_page'];
					
					$this->sitemap->addURL($url, $row['page_last_edited'], $priority);
				}
				
				// If we've got back less rows than expected, we've probably got no more to pull:
				$pulledRows = $this->DB->getTotalRows($result);
				$curPages  += $pulledRows;
				if($pulledRows < 100)
				{
					break;
				}
			}
		}			
	}
}