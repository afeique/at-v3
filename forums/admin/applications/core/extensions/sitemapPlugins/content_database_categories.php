<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_content_database_categories extends ipseoSitemapPlugin
{
	public function generate()
	{
		if(!IPSLib::appIsInstalled('ccs') || $this->settings['sitemap_priority_content_categories'] == 0)
		{
			return;
		}
		
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir('ccs') . '/sources/functions.php', 'ccsFunctions', 'ccs' );
		$ccsFunctions = new $classToLoad($this->registry);
		
		$permCheck1 = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'perm1.perm_view', true);
		$permCheck2 = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'perm2.perm_view', true);
		$permCheck3 = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'p.page_view_perms', true);
		
		$this->DB->build(array(	'select'	=> 'c.category_id, c.category_last_record_date, d.database_id, p.*',
								'from'		=> array('ccs_database_categories' => 'c'),
								'add_join'	=> array( 
													array(
														'from'	=> array('ccs_databases' => 'd'),
														'where'	=> "d.database_id = c.category_database_id",
														'type'	=> 'left'),
													array(
														'from'	=> array('ccs_pages' => 'p'),
														'where'	=> "(	d.database_is_articles = 0 
																		AND p.page_content LIKE CONCAT('%parse database=\"', d.database_key, '\"%') 
																	OR 
																	(	d.database_is_articles = 1 
																		AND p.page_content LIKE '%parse articles%'))",
														'type'	=> 'left'),
													array(
														'from'	=> array('permission_index' => 'perm1'),
														'where'	=> "(perm1.app = 'ccs' AND 
																		(perm1.perm_type = 'databases' OR perm1.perm_type = 'database') AND 
																		perm1.perm_type_id = d.database_id)",
														'type'	=> 'left'),
													array(
														'from'	=> array('permission_index' => 'perm2'),
														'where'	=> "(perm2.app = 'ccs' AND 
																		(perm2.perm_type = 'categories' OR perm2.perm_type = 'cat') AND 
																		perm2.perm_type_id = c.category_id)",
														'type'	=> 'left')
													),
								'where'		=> "	{$permCheck1} AND 
													(perm2.perm_view IS NULL OR perm2.perm_view = '' OR perm2.perm_2=',,' OR {$permCheck2}) AND
													({$permCheck3})",
								'order'		=> 'category_id DESC',
								));
								
		$result = $this->DB->execute();	
		
		while($category = $this->DB->fetch($result))
		{
			$pageUrl 	= $ccsFunctions->returnPageUrl($category) .'?';
			$url		= $ccsFunctions->getCategoriesClass($category['database_id'], false)->getCategoryUrl($pageUrl, $category['category_id']);
			
			$this->sitemap->addURL($url, (int)$category['category_last_record_date'], $this->settings['sitemap_priority_content_categories']);
		}	
	}
}