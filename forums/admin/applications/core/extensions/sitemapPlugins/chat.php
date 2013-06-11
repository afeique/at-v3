<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_chat extends ipseoSitemapPlugin
{
	public function generate()
	{
		if ( IPSLib::appIsInstalled( 'ipchat' ) and $this->settings['sitemap_priority_chat'] > 0 )
		{
			$this->sitemap->addURL( $this->settings['board_url'] . '/index.php?app=chat', time(), $this->settings['sitemap_priority_chat'] );
		}
	}
}