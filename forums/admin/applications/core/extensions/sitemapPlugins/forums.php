<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_forums extends ipseoSitemapPlugin
{
	public function generate()
	{
		if($this->settings['sitemap_priority_forums'] == 0)
		{
			return;
		}
		
		// Get categories:
		$permCheck = $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'p.perm_view', true);
		
		$this->DB->build(array(	'select'	=> 'f.*',
								'from'		=> array('forums' => 'f'),
								'add_join'	=> array( array(
												'from'	=> array('permission_index' => 'p'),
												'where'	=> "(p.perm_type = 'forum' AND p.perm_type_id = f.id)",
												'type'	=> 'left')),
								'where'		=> $permCheck));
		$result = $this->DB->execute();

		if($result)
		{
			// Add the resulting rows to the sitemap:
			while($row = $this->DB->fetch( $result ))
			{
				if ( $row['ipseo_priority'] == '0' )
				{
					continue;
				}
				$priority = ( $row['ipseo_priority'] == '' ) ? $this->settings['sitemap_priority_forums'] : $row['ipseo_priority'];
			
				$url = $this->settings['board_url'] . '/index.php?showforum=' . $row['id'];
				$url = ipSeo_FURL::build($url, 'none', $row['name_seo'], 'showforum');
				//$url = ipsRegistry::getClass('output')->buildSEOUrl( $url, 'none', $row['name_seo'], 'showforum' );
				
				$mod = intval($row['last_post']) == 0 ? time() : $row['last_post'];
				$this->sitemap->addURL( $url, $mod, $priority );
			}	
		}
	}
}