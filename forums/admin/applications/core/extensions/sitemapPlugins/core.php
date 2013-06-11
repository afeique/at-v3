<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_core extends ipseoSitemapPlugin
{
	public function generate()
	{
		if($this->settings['sitemap_priority_index'] > 0)
		{
			$this->sitemap->addURL($this->settings['board_url'] . '/', time(), $this->settings['sitemap_priority_index']);
		}
	}
}