<?php

/*
+---------------------------------------------------------------------------
|   IP.Board v3.4.5
|   ========================================
|   by Matthew Mecham
|   (c) 2008 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
+---------------------------------------------------------------------------
|   Invision Power Board IS NOT FREE SOFTWARE!
+---------------------------------------------------------------------------
|   http://www.invisionpower.com/
|   > $Id: 10039 2011-12-20 19:49:28Z mmecham $
|   > $Revision: 10039 $
|   > $Date: 2011-12-20 14:49:28 -0500 (Tue, 20 Dec 2011) $
+---------------------------------------------------------------------------
*/
@set_time_limit( 3600 );

/**
* Main public executable wrapper.
*
* Set-up and load module to run
*
* @package	IP.Board
* @author   Matt Mecham
* @version	3.0
*/

if ( is_file( './initdata.php' ) )
{
	require_once( './initdata.php' );/*noLibHook*/
}
elseif ( is_file( '../initdata.php' ) )
{
	require_once( '../initdata.php' );/*noLibHook*/
}
else
{
	require_once( 'initdata.php' );/*noLibHook*/
}

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );/*noLibHook*/

$reg = ipsRegistry::instance();
$reg->init();

$moo = new moo( $reg );

class moo
{
	private $processed = 0;
	private $parser;
	private $oldparser;
	private $start     = 0;
	private $end       = 0;
	
	const TOPICS_PER_GO = 100;
	
	function __construct( ipsRegistry $registry )
	{
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
		$this->memberData = array();
		
		switch( $this->request['do'] )
		{
			case 'media':
				$this->media();
			break;
			default:
				$this->splash();
			break;
		}
	}
	
	function show( $content, $url='' )
	{
		if ( $url )
		{
			$firstBit = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
			$refresh = "<meta http-equiv='refresh' content='0; url={$firstBit}?{$url}'>";
		}
		
		if ( is_array( $content ) )
		{
			$content = implode( "<br />", $content );
		}
		
		$html = <<<EOF
		<html>
			<head>
				<title>3.4.x Post Repair</title>
				$refresh
			</head>
			<body>
				$content
			</body>
		</html>			
EOF;

		print $html; exit();
	}
	
	/**
	 * SPLASH
	 */
	function splash()
	{
		$txt = '';
		
		$html = <<<EOF
		<strong>Please select a tool</strong>
		<br />{$txt}
		<a href="?do=media">Fix MEDIA (Youtube, etc)</a>
EOF;
	
		$this->show( $html );
	}
	
	/**
	 * Fix up media stuffs
	 */
	function media()
	{
		$this->DB->build( array( 'select' => '*',
								 'from'   => 'posts',
								 'where'  => "post LIKE '%<object%'" ) );
								 
		$o = $this->DB->execute();
		
		while( $row = $this->DB->fetch( $o ) )
		{
			$original = $row['post'];
			
			preg_match_all( '#<object(.+?)</object>#i', $row['post'], $matches, PREG_SET_ORDER );
			
			foreach( $matches as $val )
			{
				preg_match( '#http(?:s)?://(?:www.)?youtube.com/v/([\d\w-_]{4,})(&\S+?)?#', $row['post'], $matches );
			
				if ( $matches[1] )
				{
					$row['post'] = str_replace( $val[0], '[media]http://youtube.com/watch?v=' . $matches[1] . '[/media]', $row['post'] );
				}
			} 
			
			if ( $row['post'] && $row['pid'] && ( $original != $row['post'] ) )
			{
				$this->DB->update( 'posts', array( 'post' => $row['post'] ), 'pid=' . intval( $row['pid'] ) );
				
				$output .= "Fixed Post Id: " . $row['pid'] . '<br />';
			}
		}
		
		$this->show( "<h2>Complete</h2>" . $output );
	}
}

?>