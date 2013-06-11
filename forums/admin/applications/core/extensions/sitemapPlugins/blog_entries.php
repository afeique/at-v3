<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_blog_entries extends ipseoSitemapPlugin
{	
	public function generate()
	{
		if(!IPSLib::appIsInstalled('blog') || $this->settings['sitemap_priority_blog_entries'] == 0)
		{
			return;
		}
		
		// Check whether groups can access blog at all:
		$guestGroup = $this->DB->buildAndFetch(array(	'select' => 'g_blog_settings', 
														'from'   => 'groups', 
														'where'  => 'g_id = ' . $this->settings['guest_group']));
		
		// Default is to not allow access unless explicitly given:
		if(is_null($guestGroup['g_blog_settings']))
		{
			return;
		}		
														
		$settings = unserialize($guestGroup['g_blog_settings']);
		
		// Not allowed to access unless g_blog_allowview = 1
		if(!is_array($settings) || intval($settings['g_blog_allowview']) != 1)
		{
			return;
		}
		
		$max = $this->settings['sitemap_count_blog_entries'];
		
		if(!ipSeo_SitemapGenerator::isCronJob() && ($max > 10000 || $max == -1))
		{
			$max = 10000;
		}
		elseif(ipSeo_SitemapGenerator::isCronJob() && $max == -1)
		{
			$max = 50000000;
		}
		
		// Get blogs:
		$addedCount = 0;															
		$limitCount = 0;
		
		while($addedCount < $max)
		{
			if(ipSeo_SitemapGenerator::isCronJob())
			{
				sleep(0.25);
			}
			
			$query = $this->DB->build(array(	'select' => 'e.entry_id, b.blog_id, e.entry_name_seo, e.entry_last_update', 
														'from'   => array('blog_entries' => 'e'), 
														'add_join'   => array(array('type'=>'left', 'from' => array('blog_blogs' => 'b'),
																			  'where' => 'b.blog_id = e.blog_id')),
														'where'  => 'b.blog_private = 0 
																		AND b.blog_disabled = 0 
																		AND b.blog_allowguests = 1 
																		AND b.blog_view_level = \'public\'
																		AND e.entry_status = \'published\'',
														'order' => 'e.entry_id DESC',
														'limit' => array($limitCount, 100)));
			$outer = $this->DB->execute();
		
			// Add blogs to sitemap:
			while($entry = $this->DB->fetch($outer))
			{				
				$url = $this->settings['board_url'] . '/index.php?app=blog&module=display&section=blog&blogid='.$entry['blog_id'].'&showentry=' . $entry['entry_id'];
				$url = ipSeo_FURL::build($url, 'none', $entry['entry_name_seo'], 'showentry');
				//$url = ipsRegistry::getClass('output')->buildSEOUrl($url, 'none', $entry['entry_name_seo'], 'showentry');
				$addedCount = $this->sitemap->addURL($url, $entry['entry_last_update'], $this->settings['sitemap_priority_blog_entries']);
				
				unset($url);
				unset($entry);
			}
			
			$limitCount += 100;
			
			// If we've got back less rows than expected, we've probably got no more to pull:
			if($this->DB->getTotalRows($outer) < 100)
			{
				break;
			}
		}
	}
}