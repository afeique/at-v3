<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_topics extends ipseoSitemapPlugin
{
	public function generate()
	{
		if($this->settings['sitemap_priority_topics'] == 0)
		{
			return;
		}
		
		$maxTopics = (int)ipsRegistry::$settings['sitemap_recent_topics'];
		
		if(!ipSeo_SitemapGenerator::isCronJob() && ($maxTopics > 10000 || $maxTopics == -1))
		{
			$maxTopics = 10000;
		}
		elseif(ipSeo_SitemapGenerator::isCronJob() && $maxTopics == -1)
		{
			$maxTopics = 50000000;
		}
		
		$curTopics   = 0;
		$limitTopics = 0;	
		
		if(ipSeo_SitemapGenerator::isCronJob())
		{
			//print 'Done: ';
		}
		
		while($curTopics < $maxTopics)
		{
			if(ipSeo_SitemapGenerator::isCronJob())
			{
				//print $curTopics . ', ';
				sleep(0.5);
			}
			
			$permCheck = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'p.perm_2', true);
			
			$this->DB->build( array(
				'select'	=> 't.*, f.ipseo_priority',
				'from'		=> array( 'topics' => 't' ),
				'add_join'	=> array(
					array(
						'from'	=> array( 'permission_index' => 'p' ),
						'where'	=> "(p.perm_type = 'forum' AND p.perm_type_id = t.forum_id)",
						'type'	=> 'left'
						),
					array(
						'from'	=> array( 'forums' => 'f' ),
						'where'	=> "f.id=t.forum_id",
						'type'	=> 'left'
						),
					),
				'where'		=> "{$permCheck} AND " . $this->registry->getClass('class_forums')->fetchTopicHiddenQuery( array( 'visible' ), '' ),
				'limit'		=> array( $limitTopics, 100 )
				) );
			$result = $this->DB->execute();
			
			if($result)
			{				
				/*$_one   = 0;
				$_two   = 0;
				$_three = 0;*/
				
				// Add the resulting rows to the sitemap:
				while($row = $this->DB->fetch($result))
				{
					if ( $row['ipseo_priority'] == '0' )
					{
						continue;
					}
				
					if(!$this->settings['sitemap_topic_pages'] || $row['posts'] <= $this->settings['display_max_posts'])
					{	
						$url = $this->settings['board_url'] . '/index.php?showtopic=' . $row['tid'];
						$url = ipSeo_FURL::build($url, 'none', $row['title_seo'], 'showtopic');
						//$url = ipsRegistry::getClass('output')->buildSEOUrl($url, 'none', $row['title_seo'], 'showtopic');
						
						if($this->settings['sitemap_priority_topics'] == 100)
						{
							$priority = $this->calculatePriority($row);
						}
						else
						{
							$priority = $this->settings['sitemap_priority_topics'];
						}
						
						$curTopics = $this->sitemap->addURL($url, $row['last_post'], $priority);
					}
					else
					{
						$j = 1;

						for($i = 0; $i <= $row['posts']; $i += $this->settings['display_max_posts'])
						{
							$url = $this->settings['board_url'] . '/index.php?showtopic=' . $row['tid'] . ( ( $j == 1 ) ? '' : '&page=' . $j );
							$url = ipSeo_FURL::build($url, 'none', $row['title_seo'], 'showtopic');
							//$url = ipsRegistry::getClass('output')->buildSEOUrl($url, 'none', $row['title_seo'], 'showtopic');

							if($this->settings['sitemap_priority_topics'] == 100)
							{
								$priority = $this->calculatePriority($row, true);
							}
							else
							{
								$priority = $this->settings['sitemap_priority_topics'];
							}

							$curTopics = $this->sitemap->addURL($url, $row['last_post'], $priority);
							$j++;
						}
					}
					
					/*$_one += $one;
					$_two += $two;
					$_three += $three;*/
				}
				
				$limitTopics += 100;				
				
				// If we've got back less rows than expected, we've probably got no more to pull:
				if($this->DB->getTotalRows($result) < 100)
				{
					break;
				}				
			}
						
		}
		
		if(ipSeo_SitemapGenerator::isCronJob())
		{
			//print PHP_EOL;
		}
	}
	
	protected function calculatePriority($topic, $subPage = false)
	{
		$priority = $subPage ? 0.4 : 0.6;
		
		// Modify if forum has a special setting
		if ( $topic['ipseo_priority'] != '' )
		{
			$priority += ( $topic['ipseo_priority'] - $priority );
		}
		
		// Boost topics where start date is today:
		if($topic['start_date'] > (time() - 86400))
		{
			$priority = $priority + 0.1;
		}
		
		// Drop closed topics, but only if not pinned.
		if(!$topic['pinned'] && $topic['state'] != 'open')
		{
			$priority = $priority - 0.2;
		}
		
		// Boost pinned topics:
		if($topic['pinned'])
		{
			$priority = $priority + 0.1;
		}
		
		// Boost topics with more than one page of posts:
		if($topic['posts'] > $this->settings['display_max_posts'])
		{
			$priority = $priority + 0.1;
		}
		
		// Extra boost for topics with more than ten pages of posts:
		if($topic['posts'] > ($this->settings['display_max_posts'] * 10))
		{
			$priority = $priority + 0.2;
		}
		
		if($priority > 1)
		{
			$priority = 1.0;
		}
		elseif($priority < 0)
		{
			$priority = 0.0;
		}
		
		return $priority;
	}
}