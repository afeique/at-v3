<?php

if(!IN_IPB)
{
	die('This file is not designed to be accessed directly.');
}

class sitemap_core_calendar extends ipseoSitemapPlugin
{	
	/**
	* Generate sitemap entries:
	*/
	public function generate()
	{
		//-----------------------------------------
		// Is it enabled?
		//-----------------------------------------
	
		if(!IPSLib::appIsInstalled('calendar') || $this->settings['sitemap_priority_calendar'] == 0)
		{
			return;
		}
		
		//-----------------------------------------
		// Which calendars can we access?
		//-----------------------------------------
		
		$calendars = array();
		$this->DB->build( array(
			'select'	=> 'c.cal_id',
			'from'		=> array( 'cal_calendars' =>  'c' ),
			'add_join'	=> array( array(
				'from'	=> array('permission_index' => 'p'),
				'where'	=> "(p.app = 'calendar' AND p.perm_type = 'calendar' AND p.perm_type_id = c.cal_id)",
				'type'	=> 'left'
				) ),
			'where'		=> $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'p.perm_view' )
			) );
		$this->DB->execute();
		while ( $row = $this->DB->fetch() )
		{
			$calendars[] = $row['cal_id'];
		}
		
		if ( empty( $calendars ) )
		{
			return;
		}
		
		//-----------------------------------------
		// Get past events
		//-----------------------------------------
		
		$time = time();
				
		$this->DB->build( array(
			'select'	=> 'event_id, event_title_seo, event_start_date',
			'from'		=> 'cal_events',
			'where'		=> "event_end_date < {$time} AND " . $this->DB->buildWherePermission( $calendars, 'event_calendar_id', FALSE ) . ' AND ' . $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'event_perms' ),
			'limit'		=> $this->settings['sitemap_count_calendar_past']
			) );
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$url = $this->settings['board_url'] . '/index.php?app=calendar&module=calendar&section=view&do=showevent&event_id=' . $row['event_id'];
			$url = ipSeo_FURL::build( $url, 'none', $row['event_title_seo'], 'cal_event' );

			$this->sitemap->addURL( $url, strtotime( $row['event_start_date'] ), $this->settings['sitemap_priority_calendar'] );
		}
		
		//-----------------------------------------
		// Get future events
		//-----------------------------------------
						
		$this->DB->build( array(
			'select'	=> 'event_id, event_title_seo, event_start_date',
			'from'		=> 'cal_events',
			'where'		=> "event_end_date > {$time} AND " . $this->DB->buildWherePermission( $calendars, 'event_calendar_id', FALSE ) . ' AND ' . $this->DB->buildWherePermission( array( $this->caches['group_cache'][ $this->settings['guest_group'] ]['g_perm_id'] ), 'event_perms' ),
			'limit'		=> $this->settings['sitemap_count_calendar_future']
			) );
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$url = $this->settings['board_url'] . '/index.php?app=calendar&module=calendar&section=view&do=showevent&event_id=' . $row['event_id'];
			$url = ipSeo_FURL::build( $url, 'none', $row['event_title_seo'], 'cal_event' );

			$this->sitemap->addURL( $url, strtotime( $row['event_start_date'] ), $this->settings['sitemap_priority_calendar'] );
		}
	}
}