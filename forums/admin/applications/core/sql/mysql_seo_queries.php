<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Last Updated: $LastChangedDate: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Blog
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 10721 $
 *
 */



class core_seo_queries extends db_driver_mysql
{
     protected $db  = "";
     protected $tbl = "";

    /* Construct */
    public function __construct( &$obj )
    {
    	$this->DB     = ipsRegistry::DB();
    	$this->prefix = ips_DBRegistry::getPrefix();
    }

    /*========================================================================*/

    public function ipseo_increment_keyword_count( $keyword )
    {
		$keyword = substr( $this->DB->addSlashes($keyword), 0, 255 );
		
    	$query   = "INSERT INTO {$this->prefix}search_keywords (keyword, count) VALUES ('{$keyword}', 1) ON DUPLICATE KEY UPDATE count = count + 1";
		
    	return $query;
	}
}
?>