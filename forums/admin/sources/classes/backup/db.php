<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Back Up Model
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 * </pre>
 *
 * @author 		Matt Mecham
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		Tue. 15th June 2012
 * @version		$Rev: 10721 $
 *
 */

/**
 * Back up database class
 * @author matt
 *
 */
class ipsBackup_db extends db_driver_mysql
{
	/* Current DB method */
	private $_method = '';
	
	/* Current table */
	private $_table  = '';
	
	/* Back up class */
	private $Backup  = '';
	
	/* Shut down queries */
	private $_shutDownData = array();
	
	private $_isMysqli     = false;
	
	private $_insertId     = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		$this->_isMysqli = ( extension_loaded('mysqli') AND ! defined( 'FORCE_MYSQL_ONLY' ) ) ? true : false;
		
		return parent::__construct();
	}
	
	/**
	 * Can't load back-up class at INIT because it requires
	 * that ipsRegistry is set up
	 */
	private function _backup()
	{
		if ( ! is_object( $this->Backup ) )
		{
			/* Fetch back up class */
			require_once( IPS_ROOT_PATH . 'sources/classes/backup.php' );
			$this->Backup = new ipsBackup();
		}
		
		return $this->Backup;
	}
	
	 /**
	 * Delete data from a table
	 *
	 * @param	string 		Table name
	 * @param	string 		[Optional] Where clause
	 * @param	string		[Optional] Order by
	 * @param	array		[Optional] Limit clause
	 * @param	boolean		[Optional] Run on shutdown
	 * @return	@e resource
	 */
	public function delete( $table, $where='', $orderBy='', $limit=array(), $shutdown=false )
	{
		$this->_table  = '';
		$this->_method = '';
		
		/* Want to capture? */
		if ( $this->_backup()->isOkToGetThisTableData( $table ) )
		{
			$this->_table  = $table;
			$this->_method = 'delete';
		}
		
		parent::delete( $table, $where, $orderBy, $limit, $shutdown );
	}
	
	/**
	 * Insert data into a table
	 *
	 * @param	string 		Table name
	 * @param	array 		Array of field => values
	 * @param	boolean		Run on shutdown
	 * @return	@e resource
	 */
	public function insert( $table, $set, $shutdown=false )
	{
		$this->_table  = '';
		$this->_method = '';
		
		/* Want to capture? */
		if ( $this->_backup()->isOkToGetThisTableData( $table ) )
		{
			$this->_table  = $table;
			$this->_method = 'insert';
		}
		
		if ( $table !== 'backup_queue' )
		{
			/* Reset insert ID ready for next query
			   unless we're adding to the queue table
			   in which case we want to preserve insertID from preceeding query */
			$this->_insertId = null;
		}
		
		return parent::insert( $table, $set, $shutdown );
	}
	
	/**
	 * Insert record into table if not present, otherwise update existing record
	 *
	 * @param	string 		Table name
	 * @param	array 		Array of field => values
	 * @param	array 		Array of fields to check
	 * @param	boolean		[Optional] Run on shutdown
	 * @return	@e resource
	 */
	public function replace( $table, $set, $where, $shutdown=false )
	{
		$this->_table  = '';
		$this->_method = '';
		
		/* Want to capture? */
		if ( $this->_backup()->isOkToGetThisTableData( $table ) )
		{
			$this->_table  = $table;
			$this->_method = 'replace';		
		}
		
		return parent::replace( $table, $set, $where, $shutdown );
	}
	
	/**
	* Update data in a table
	*
	* @param	string 		Table name
	* @param	mixed 		Array of field => values, or pre-formatted "SET" clause
	* @param	string 		[Optional] Where clause
	* @param	boolean		[Optional] Run on shutdown
	* @param	boolean		[Optional] $set is already pre-formatted
	* @return	@e resource
	*/
	public function update( $table, $set, $where='', $shutdown=false, $preformatted=false, $debug=false )
	{
		$this->_table  = '';
		$this->_method = '';
		
		/* Want to capture? */
		if ( $this->_backup()->isOkToGetThisTableData( $table ) )
		{
			$this->_table  = $table;
			$this->_method = 'update';
		}
		
		return parent::update( $table, $set, $where, $shutdown, $preformatted, $debug );
	}
	
	/**
	 * Generates and executes SQL query, and returns the all results in an array
	 *
	 * @param	array		Set commands (select, from, where, order, limit, etc)
	 * @param	string		Key to index array on (member_id, for example)
	 * @return	@e array
	 */
    public function buildAndFetchAll( $data, $arrayIndex=null )
    {
	    if ( substr( $data['from'], 0, 7 ) == 'backup_' )
	    {
		    $this->_method = '';
	    }
	    else
	    {
		   return parent::buildAndFetchAll( $data, $arrayIndex );
	    }
    }
    
	 /**
	 * Build a query based on template from cache file
	 *
	 * @param	string		Name of query file method to use
	 * @param	array		Optional arguments to be parsed inside query function
	 * @param	string		Optional class name
	 * @return	@e void
	 */
    public function buildFromCache( $method, $args=array(), $class='sql_queries' )
    {
    	$backup = $this->_backup();
    	
	    parent::buildFromCache( $method, $args, $class );
	    
	    if ( $this->cur_query )
	    {
		    if ( preg_match( '#^(?:insert|update|replace|delete|truncate)#i', $this->cur_query ) )
			{
				/* Ghetto ->query() method used */
				preg_match( '#^(TRUNCATE TABLE|DELETE FROM|INSERT INTO|UPDATE|REPLACE INTO)(?:\s+?)?(\S+)(\s+?)?#i', $this->cur_query, $matches );
				
				if ( count( $matches ) && ! empty( $matches[1] ) )
				{
					$this->_method = trim( str_replace( array( 'table', 'from', 'into' ), '', strtolower( $matches[1] ) ) );
					$this->_table  = trim( $matches[2] );
					
					if ( $this->_backup()->isOkToGetThisTableData( $this->_table ) )
					{
						/* Do this now if query ran OK */
						$this->_backup()->addRawQueryToLog( $the_query, $this->_table );
					}
				}
			}
	    }
    }
    
	/**
	 * Execute a direct database query
	 *
	 * @param	string		Database query
	 * @param	boolean		[Optional] Do not convert table prefix
	 * @return	@e resource
	 */
	public function query( $the_query, $bypass=false )
	{
		$val = parent::query( $the_query, $bypass );
		
		if ( $this->_isShutDown && in_array( md5( $the_query ), array_keys( $this->_shutDownData ) ) )
		{
			$_data = $this->_shutDownData[ md5( $the_query ) ];
			
			if ( $this->_backup()->isOkToGetThisTableData(  $_data[1] ) )
			{
				$this->_method = $_data[0];
				$this->_table  = $_data[1];
			}
		}
		
		if ( $this->_method )
		{
			/* Fetch real insert ID */
			$this->_insertId = null;
			$this->_insertId = $this->getInsertId();
		
			/* Clear method now or it will retain the current table when query log is run below */
			$this->_method = '';
			
			/* Do this now if query ran OK */
			$this->_backup()->addRawQueryToLog( $the_query, $this->_table );
		}
		else if ( preg_match( '#^(?:insert|update|replace|delete|truncate)#i', $the_query ) )
		{
			/* Ghetto ->query() method used */
			preg_match( '#^(TRUNCATE TABLE|DELETE FROM|INSERT INTO|UPDATE|REPLACE INTO)(?:\s+?)?(\S+)(\s+?)?#i', $the_query, $matches );
			
			if ( count( $matches ) && ! empty( $matches[1] ) )
			{
				$this->_method = trim( str_replace( array( 'table', 'from', 'into' ), '', strtolower( $matches[1] ) ) );
				$this->_table  = trim( $matches[2] );
				
				if ( $this->_backup()->isOkToGetThisTableData( $this->_table ) )
				{
					/* Fetch real insert ID */
					$this->_insertId = null;
					$this->_insertId = $this->getInsertId();
			
					/* Do this now if query ran OK */
					$this->_backup()->addRawQueryToLog( $the_query, $this->_table );
				}
			}
		}
		
		$this->_method = '';
		$this->_table  = '';
		
		return $val;
	}
	
	/**
	 * Retrieve latest autoincrement insert id
	 *
	 * @return	@e integer
	 */
	public function getInsertId()
	{
		if ( $this->_insertId === null )
		{
			$this->_insertId = parent::getInsertId();
		}
		
		return $this->_insertId;
	}
	
	/**
	 * Determine if query is shutdown and run it
	 *
	 * @param	string 		Query
	 * @param	boolean 	[Optional] Run on shutdown
	 * @return	@e mixed
	 */
	protected function _determineShutdownAndRun( $query, $shutdown=false )
	{
		if ( $shutdown && $this->obj['use_shutdown'] )
		{
			/* store data for later execution */
			$this->_shutDownData[ md5($query ) ] = array( $this->_method, $this->_table );
			
			/* Prevent it from storing attributes that won't be cleared as query isn't run */
			$this->_method = '';
			$this->_table  = '';
		}
		
		return parent::_determineShutdownAndRun( $query, $shutdown );
	}
	
	/* Have to define this as inherited class */
	public function _getErrorString()
	{
		return parent::_getErrorString();
	}
	
	public function _getErrorNumber()
	{
		return parent::_getErrorNumber();
	}	
}

