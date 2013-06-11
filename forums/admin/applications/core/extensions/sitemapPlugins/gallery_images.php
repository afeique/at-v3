<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_gallery_images extends ipseoSitemapPlugin
{
	public function generate()
	{
		$galleryClassFile = IPSLib::getAppDir('gallery') . '/sources/classes/gallery.php';
		
		if(!IPSLib::appIsInstalled('gallery') || $this->settings['sitemap_priority_gallery_images'] == 0 || !is_file($galleryClassFile))
		{
			return;
		}
		
		$classToLoad = IPSLib::loadLibrary( $galleryClassFile, 'ipsGallery', 'gallery' );
		$this->registry->setClass( 'gallery', new $classToLoad( $this->registry ) );
		
		$max = $this->settings['sitemap_count_gallery_images'];
		
		if(!ipSeo_SitemapGenerator::isCronJob() && ($max > 10000 || $max == -1))
		{
			$max = 10000;
		}
		elseif(ipSeo_SitemapGenerator::isCronJob() && $max == -1)
		{
			$max = 500000000;
		}
		
		$addedCount = 0;
		$limitCount = 0;																
		while($addedCount < $max)
		{
			if(ipSeo_SitemapGenerator::isCronJob())
			{
				sleep(0.5);
			}
			
			$filters = array( 
								'sortOrder'		=> 'desc', 
								'sortKey'		=> 'date',
								'offset'		=> $limitCount, 
								'limit'			=> 100,
								'getLatestComment' => 1,
							);
			
			$memberId	= 0;
			$images		= $this->registry->gallery->helper('image')->fetchImages( $memberId, $filters );
			
			foreach($images as $image)
			{						
				$url		= "{$this->settings['board_url']}/index.php?app=gallery&image={$image['image_id']}";
				$url		= ipSeo_FURL::build( $url, 'none', $image['image_caption_seo'], 'viewimage' );
				$lastMod	= is_null($image['comment_post_date']) ? $image['image_date'] : $image['comment_post_date'];	
				$addedCount = $this->sitemap->addUrl($url, $lastMod, $this->settings['sitemap_priority_gallery_images']);
			}
			
			$limitCount += 100;
			
			if(count($images) < 100)
			{
				break;
			}
		}
	}
}