<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_blogs extends ipseoSitemapPlugin
{	
	/**
	* Generate sitemap entries:
	*/
	public function generate()
	{
		if(!IPSLib::appIsInstalled('blog') || $this->settings['sitemap_priority_blogs'] == 0)
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
				
		// Get blogs:
		$query = $this->DB->build(array(	'select' => 'blog_id, blog_seo_name, blog_last_udate', 
											'from'   => 'blog_blogs', 
											'where'  => 'blog_private = 0 
															AND blog_disabled = 0 
															AND blog_allowguests = 1 
															AND blog_view_level = \'public\''));
		$this->DB->execute();
				
		// Add blogs to sitemap:															
		while($blog = $this->DB->fetch())
		{
			$url = $this->settings['board_url'] . '/index.php?app=blog&blogid=' . $blog['blog_id'];
			$url = ipSeo_FURL::build($url, 'none', $blog['blog_seo_name'], 'showblog');
			//$url = ipsRegistry::getClass('output')->buildSEOUrl($url, 'none', $blog['blog_seo_name'], 'showblog');
			$this->sitemap->addURL($url, $blog['blog_last_udate'], $this->settings['sitemap_priority_blogs']);
		}
	}
}