<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_content_database_records extends ipseoSitemapPlugin
{
	public function generate()
	{
		if(!IPSLib::appIsInstalled('ccs') || $this->settings['sitemap_priority_content_records'] == 0)
		{
			return;
		}
		
		$classToLoad	= IPSLib::loadLibrary( IPSLib::getAppDir('ccs') . '/sources/functions.php', 'ccsFunctions', 'ccs' );
		$databaseToLoad	= IPSLib::loadLibrary( IPSLib::getAppDir('ccs') . '/sources/databases.php', 'databaseBuilder', 'ccs' );
		$ccsFunctions = new $classToLoad($this->registry);
		
		$permCheck1 = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'perm1.perm_2', true);
		$permCheck2 = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'perm2.perm_2', true);
		$permCheck3 = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'p.page_view_perms', true);
		
		$this->DB->build(array(	'select'	=> 'c.category_id, d.*, p.*',
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
																		AND p.page_content LIKE '%\{parse articles\}%'))",
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
													(perm2.perm_2 IS NULL OR perm2.perm_2 = '' OR perm2.perm_2=',,' OR {$permCheck2}) AND
													({$permCheck3})",
								'order'		=> 'category_id DESC',
								));
								
		$result = $this->DB->execute();	
		
		$categories = array();
		$databases  = array();
		
		while($category = $this->DB->fetch($result))
		{
			$categories[$category['category_id']]				= $category['category_id'];
			$databases[$category['database_id']]				= $category;
			$databases[$category['database_id']]['base_link']	= $ccsFunctions->returnPageUrl($category) .'?';
		}

		$_databases	= $this->cache->getCache('ccs_databases');

		foreach( $_databases as $_db )
		{
			if( isset($databases[ $_db['database_id'] ]) )
			{
				continue;
			}

			if ( ipsRegistry::getClass('permissions')->check( 'view', $_db, explode( ',', $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ) ) != TRUE )
			{
				$databases[ $_db['database_id'] ]				= $_db;
				$databases[ $_db['database_id'] ]['base_link']	= $ccsFunctions->returnDatabaseUrl( $_db['database_id'] );
			}
		}

		/*if(!count($categories))
		{
			return;
		}*/
		
		$_cats		= ( count($categories) ) ? implode( ',', $categories ) : 0;
		$_hardLimit = !ipSeo_SitemapGenerator::isCronJob() ? 10000 : 500000000;
		$addedCount = 0;
				
		foreach($databases as $database)
		{
			if(ipSeo_SitemapGenerator::isCronJob())
			{
				sleep(1);
				//print 'Processing database: ' . $database['database_name'] . PHP_EOL;
			}
			
			$db = new $databaseToLoad($this->registry);
			$db->categories = $ccsFunctions->getCategoriesClass($database['database_id'], false);
			$db->database	= $database;
			
			$db->fieldsClass	= $this->registry->ccsFunctions->getFieldsClass();
			
			$limitCount = 0;
			while(1)
			{
				if(ipSeo_SitemapGenerator::isCronJob())
				{
					sleep(0.25);
				}
				
				if($addedCount >= $_hardLimit)
				{
					break;
				}
				
				$_published	= null;

				if( $database['database_is_articles'] )
				{
					$_cache	= $this->cache->getCache('ccs_fields');
					
					foreach( $_cache[ $database['database_id'] ] as $_field )
					{
						if( $_field['field_key'] == 'article_date' )
						{
							$_published	= 'field_' . $_field['field_id'];
						}
					}
				}

				$this->DB->build(array(	'select'	=> '*',
										'from'		=> $database['database_database'],
										'where'		=> 'record_approved = 1 AND ( category_id IN ('.$_cats.') OR category_id=0)',
										'order'		=> 'record_updated DESC',
										'limit'		=> array($limitCount, 100)
										));
				$result = $this->DB->execute();
				
				while($record = $this->DB->fetch($result))
				{
					if( $database['database_is_articles'] AND $record[ $_published ] > time() )
					{
						continue;
					}

					$record['_skipUpdateDynamic'] = true;
					$url = $db->getRecordUrl($record);
					
					$addedCount = $this->sitemap->addUrl($url, (int)$record['record_updated'], $this->settings['sitemap_priority_content_records']);
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
}