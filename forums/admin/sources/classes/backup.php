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
 * Back up model
 * @author matt
 *
 */
class ipsBackup
{
	/**
	 * Holds the back-up variables
	 * @var array
	 */
	private $Vars       = array();
	
	/**
	 * DB tables and primary keys
	 * @var array
	 */
	private $Keys		= array();
	/**
	 * Contains list of tables NOT to gather.
	 * @var array
	 */
	private $BlackList = array();
	
	/**
	 * Contains list of tables ONLY to gather.
	 * @var array
	 */
	private $WhiteList = array();
	
	/**
	 * Contains all the schematic info. Use wisely, it can be 'uuuge (700k+)
	 */
	private $SchematicData = array();
	
	/**
	 * Limits for the batches
	 * @var array
	 */
	private $Limits        = array( 'rows' => 0, 'bytes' => 0 );
	
	/**
	 * Construct
	 */
	public function __construct()
	{
		/* Make objects */
		$this->registry = ipsRegistry::instance();
		$this->DB       = $this->registry->DB();
		
		/* Fetch *lists */
		$this->BlackList = $this->_getBlackList();
		$this->Whitelist = $this->_getWhiteList();
		
		/* Fetch data variables */
		$this->Vars		 = $this->_getVars();
		
		/* Fetch db table pkeys */
		$this->Keys      = $this->_getKeys();
		
		/* Set default limits for the batch pulling */
		$this->setLimits( 'rows', 10000 );
		$this->setLimits( 'bytes', 20 * 1024 * 1024 );
	}
	
	/**
	 * Add an insert/delete/update/replace query to our log
	 * @param string $query
	 * @param string $table
	 */
	public function addRawQueryToLog( $query, $table )
	{
		/* This would be LOLs */
		if ( $this->backUpRunning() && ! stristr( 'insert into ' . $this->registry->dbFunctions()->getPrefix() . 'backup_queue', $query ) )
		{
			$this->DB->insert( 'backup_queue', array( 'queue_entry_date'  => IPS_UNIX_TIME_NOW,
													  'queue_entry_type'  => 5,
													  'queue_entry_table' => $this->_stripPrefix( $table ),
													  'queue_entry_sql'   => $query ) );
		}
	}
	
	/**
	 * Is the back up system ready to process queries?
	 *
	 */
	public function backUpRunning()
	{
		return ( $this->Vars['populate_all_done'] > 0 ) ? true : false;
	}

	
	/**
	 * Does it need to reset?
	 *
	 */
	public function checkForRestart()
	{
		/* Eventually we'll expand the system so it can be pinged to see if a restart is needed but for now... */
		return ( ! $this->Vars['populate_all_done'] ) ? true : false;
	}
	
	/**
	 * Send batch to the backup server
	 */
	public function sendBatch()
	{
		/* Fetch kernel class */
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
		$cfm = new classFileManagement();
		
		/* Fetch DB data */
		$tables 	   = $this->_getDatabaseSchematics();
		$sqlRows	   = array();
		$touchedTables = array();
		$bytesUsed     = '';
		$topId		   = 0;
		$delIds		   = array();
		
		/* Do we need to restart? */
		if ( $this->checkForRestart() )
		{
			$this->populateLogWithAllTables();
			
			return true;
		}
		
		/* Little short cut */
		$p = $this->registry->dbFunctions()->getPrefix();
		
		/* Start looping on the table */
		$this->DB->build( array( 'select' => '*',
								 'from'   => 'backup_queue',
								 'order'  => 'queue_id',
								 'limit'  => array( 0, $this->Limits['rows'] ) ) );
		
		$o = $this->DB->execute();
		
		while( $row = $this->DB->fetch( $o ) )
		{
			$tbl    = $this->_stripPrefix( $row['queue_entry_table'] );
			$sql    = '';
			$fields = array();
			
			$touchedTables[ $tbl ] = $tbl;
			
			if ( $row['queue_entry_sql'] )
			{
				$sql = $row['queue_entry_sql'];
			}
			else
			{
				/* Get fields */
				foreach( $tables[ $row['queue_entry_table'] ]['cols'] as $col )
				{
					$fields[] = $col['Field'];
				}
				
				if ( ! count( $fields ) )
				{
					/* Empty - so remove this queue row or we'll never progress */
					$delIds[] = $row['queue_id'];
					
					continue;
				}
				
				/* INSERT */
				if ( $row['queue_entry_type'] == 1 )
				{
					/* Get data */
					$pKey    = $row['queue_entry_key'];
					$pKeyVal = ( $this->_isConcat( $pKey ) ) ? addslashes( $row['queue_entry_value'] ) : $row['queue_entry_value'];
					$data    = array();
					$vals    = array();
					
					$data    = $this->DB->buildAndFetch( array( 'select' => '`' . implode( "`, `", $fields ) . '`',
															    'from'   => $tbl,
															    'where'  => $pKey . "='" . $pKeyVal . "'" ) );
					
					if ( ! is_array( $data ) OR ! count( $data ) )
					{
						/* Empty - so remove this queue row or we'll never progress */
						$delIds[] = $row['queue_id'];
						
						continue;
					}
					
					foreach( $data as $k => $v )
					{
						$vals[] = $this->_makeValueSafeForQuery( $v );
					}
					
					$sql  = "INSERT INTO " . $p . $tbl . "(`" . implode( "`, `", $fields ). "`) " .
							"VALUES( '" . implode( "', '", $vals ) . "');";
				}
			}
			
			/* Got anything? */
			if ( ! $sql )
			{
				/* Empty - so remove this queue row or we'll never progress */
				$delIds[] = $row['queue_id'];
					
				continue;
			}
			
			/* check size */
			$bytesUsed += IPSLib::strlenToBytes( strlen( $sql ) );
			
			/* Truth time! */
			if ( $bytesUsed >= $this->Limits['bytes'] )
			{
				break;
			}
			else
			{
				$sqlRows[] = $sql;
				
				/* top id to remove */
				$topId = $row['queue_id'];
			}
		}
		
		/* Anything to delete? */
		if ( count( $delIds ) )
		{
			$this->DB->delete( 'backup_queue', 'queue_id IN (' . implode( ',', array_values( $delIds ) ) . ')' );
		}
		
		/* What do we have? */
		if ( $topId && count( $sqlRows ) )
		{
			$dataToSend = $this->_createTextToSend( $sqlRows, array_keys( $touchedTables ) );
									
			$returnedData = $cfm->postFileContents( $this->_getBackupServerUrl(), array( 'backup_data' => @gzcompress( $dataToSend ), 'lkey' => ipsRegistry::$settings['ipb_reg_number'] ) );
				
			$test = json_decode( trim( $returnedData ), true );
			
			if ( is_array( $test ) && $test['status'] == 'ok' )
			{
				$this->DB->delete( 'backup_queue', 'queue_id <= ' . intval( $topId ) );
				$this->_addLog( intval( $test['rows'] ), $test['status'] );
				
				$this->Vars['rows_sent']  = intval( $this->Vars['rows_sent'] ) + count( $sqlRows );
				$this->Vars['rows_total'] = $this->_getStoredRowCount();
				$this->_setVars();
			}
			else
			{
				# Fail lol
				$this->_addLog( 0, $test['status'] );
			}
		}
	}
	
	/**
	 * Do the first run
	 * This will reset the system and re-populate the entire log table - USE WITH CAUTION!
	 */
	public function populateLogWithAllTables( $tablesToIndex=300 )
	{
		$lastTable = $this->Vars['populate_all_last_table'];
		$lastProc  = '';
		
		/* Reset the system if not middle of processing */
		if ( ! $lastTable )
		{
			$this->_resetSystem();
		}
			
		/* Little short cut */
		$p = $this->registry->dbFunctions()->getPrefix();
		
		/* Lets go */
		foreach( $this->Keys as $table => $pKey )
		{
			/* Seek to the first unindexed table */
			if ( ! $lastProc )
			{
				if ( $lastTable and $table != $lastTable )
				{
					continue;
				}
				
				if ( ( $lastTable == $table ) || ! $lastTable )
				{
					$lastProc = $table;
				}
			}
			
			$this->DB->allow_sub_select = 1;
			
			$pKeyText = ( $this->_isConcat( $pKey ) ) ? addslashes( $pKey ) : $pKey;
			
			/* Fetch, then */
			$this->DB->query( 'INSERT INTO ' . $p . 'backup_queue (queue_entry_date, queue_entry_type, queue_entry_table, queue_entry_key, queue_entry_value, queue_entry_sql)' .
							  "( SELECT UNIX_TIMESTAMP(), 1, '" . $table . "', '" . $pKeyText . "', " . $pKey . ", '' FROM " . $table . " )" );
			
			$this->Vars['populate_all_last_table'] = $table;
			$this->_setVars();
		}
		
		/* We are all done */
		if ( ( $lastTable && $lastProc == $this->Vars['populate_all_last_table'] ) OR ! $lastProc OR ( $tablesToIndex >= count( $this->Keys ) ) )
		{
			$this->Vars['rows_sent']			   = 0;
			$this->Vars['rows_total']			   = $this->_getStoredRowCount();
			$this->Vars['populate_all_done']       = IPS_UNIX_TIME_NOW;
			$this->Vars['populate_all_last_table'] = '';
			$this->_setVars();
		}
	}
	
	/**
	 * Ok to fetch the table data?
	 * @param string $table
	 */
	public function isOkToGetThisTableData( $table )
	{
		$table = $this->_stripPrefix( $table );
		
		if ( count( $this->Whitelist ) )
		{
			if ( in_array( $table, $this->Whitelist ) )
			{
				return true;
			}
		}
		
		return ( $this->_isBlackListTable( $table ) ) ? false : true;
	}
	
	/**
	 * Rebuilds the cache file so we can find out a little about the database structure
	 */
	public function rebuildPrimaryKeyCacheFile()
	{
		$cache     = array();
		$schematic = $this->_getDatabaseSchematics();
		
		foreach( $schematic as $table => $data )
		{
			$hasKey = false;
			
			if ( count( $data['idx'] ) )
			{
				foreach( $data['idx'] as $idx )
				{
					if ( $idx['Key_name'] == 'PRIMARY' )
					{
						$cache[ $table ] = $idx['Column_name'];
						$hasKey = true;
						break;
					}
				}
			}
			
			if ( ! $hasKey )
			{
				/* Lets make a key */
				$c      = 0;
				$concat = array();
				
				foreach( $data['cols'] as $col )
				{
					if ( strtolower( $col['Type'] ) != 'mediumtext' )
					{
						if ( stristr( strtolower( $col['Type'] ), 'int(' ) )
						{
							$concat[] = $col['Field'];
						}
						else
						{
							$concat[] = 'SUBSTR(' . $col['Field'] . ', 1, 32)';
						}
						
						$c++;
					}
					
					if ( $c > 5 )
					{
						break;
					}
				}
				
				$cache[ $table ] = 'CONCAT_WS( \',\', ' . implode( ',', $concat ) . ' )';
			}
		}
		
		$contents = '<' . "?php\n" . '$keyCache = ' . var_export( $cache, true ) . ';' . "\n?" . '>';
		
		if ( ! file_put_contents( $this->_getBackUpKeyCacheFilename(), $contents ) )
		{
			trigger_error( 'Could not save cache file for back up system' );
		}
		
		/* Reset table pkeys */
		$this->Keys = $this->_getKeys();
	}
	
	/**
	 * Set a per batch limit
	 * @param string $key
	 * @param string $value
	 */
	public function setLimits( $key, $value )
	{
		switch( $key )
		{
			case 'row':
			case 'pergo':
			case 'rows':
				$this->Limits['rows'] = $value;
			break;
			case 'bytes':
			case 'b':
				$this->Limits['bytes'] = $value;
			break;
			case 'kb':
				$this->Limits['bytes'] = $value * 1024;
			break;
			case 'mb':
				$this->Limits['bytes'] = $value * 1024 * 1024;
			break;
		}
	}
	
	/**
	 * Get the number of rows in the queue table
	 * @return int, yo
	 */
	private function _getStoredRowCount()
	{
		$count = $this->DB->buildAndFetch( array( 'select' => 'count(*) as tnuoc', 'from' => 'backup_queue' ) );
		
		return $count['tnuoc'];
	}
	
	/**
	 * Resets the system and preps for re-logging all
	 */
	private function _resetSystem()
	{
		/* Reset */
		$this->Vars['populate_all_start']      = IPS_UNIX_TIME_NOW;
		$this->Vars['populate_all_done']       = 0;
		$this->Vars['populate_all_last_table'] = '';
	
		$this->_setVars();
		
		/* Delete current logs */
		$this->DB->delete('backup_queue');
		$this->DB->delete('backup_log');
		
		/* Rebuild cache file */
		$this->rebuildPrimaryKeyCacheFile();
	}
	
	/**
	 * Gets the database keys
	 * @return array
	 */
	private function _getKeys()
	{
		$keyCache = array();
	
		if ( ! is_file( $this->_getBackUpKeyCacheFilename() ) )
		{
			$this->rebuildPrimaryKeyCacheFile();
		}
	
		include( $this->_getBackUpKeyCacheFilename() );
		
		return $keyCache;
	}
	
	/**
	 * Gets the vars from the deebee
	 * @return array:
	 */
	private function _getVars()
	{
		$vars = array();
		
		$db = $this->DB->buildAndFetchAll( array( 'select' => '*',
												  'from'   => 'backup_vars' ) );
		
		if ( is_array( $db ) )
		{
			foreach( $db as $var )
			{
				$vars[ $var['backup_var_key'] ] = $var['backup_var_value'];
			}
		}

		if( !isset($vars['populate_all_done']) )
		{
			$vars['populate_all_done']	= 0;
		}

		return $vars;
	}
	
	/**
	 * Update the variables 
	 */
	private function _setVars()
	{
		foreach( $this->Vars as $k => $v )
		{
			$this->DB->replace( 'backup_vars',array( 'backup_var_key' => $k, 'backup_var_value' => $v ), array( 'backup_var_key' ) );
		}
	}
	
	/**
	 * Removes table prefix if one exists
	 * @param string $table
	 * @return string
	 */
	private function _stripPrefix( $table )
	{
		if ( $this->registry->dbFunctions()->getPrefix() && preg_match( "/^" . $this->registry->dbFunctions()->getPrefix() ."/i", $table ) )
		{
			$table = preg_replace( "/^" . $this->registry->dbFunctions()->getPrefix() ."(.*)$/i", '\1', $table );
		}
		
		return $table;
	}
	
	/**
	 * Returns ignored tables
	 */
	private function _getWhiteList()
	{
		return array();
	}
	
	/**
	 * Returns ignored tables
	 */
	private function _getBlackList()
	{
		/* @todo abstract this puppy */
		return array( 'admin_login_logs',
					  'admin_logs',
					  'api_log',
					  'backup_log',
					  'backup_queue',
					  'backup_vars',
					  'blog_askimet_logs',
					  'blog_trackback_spamlogs',
					  'blog_views',
					  'chat_log_archive',
					  'content_cache_posts',
				      'content_cache_sigs',
					  'core_editor_autosave',
					  'core_item_markers',
					  'core_like_cache',
				      'core_share_links_caches',
				      'core_share_links_log',
				      'core_tags_cache',
					  'error_logs',
					  'gallery_image_views',
					  'mail_error_logs',
				      'moderator_logs',
					  'profile_friends_flood',
					  'profile_portal_views',
					  'reputation_cache',
					  'search_sessions',
					  'sessions',
				      'skin_cache',
					  'skin_templates_cache',
					  'spam_service_log',
					  'spider_logs',
				      'task_logs',
					  'topic_views' );
	}
	/**
	 * Return database schematic information
	 */
	private function _getDatabaseSchematics()
	{
		if ( count( $this->SchematicData ) )
		{
			return $this->SchematicData;
		}
		
		$this->DB->query( "SHOW TABLE STATUS FROM `" . $this->registry->dbFunctions()->getDatabaseName() . "`" );
		
		/* Loop through the results */
		$this->SchematicData = array();
		
		while( $row = $this->DB->fetch() )
		{
			/* Check to ensure it's a table for this install... */
			if ( $this->registry->dbFunctions()->getPrefix() && ! preg_match( "/^" . $this->registry->dbFunctions()->getPrefix() ."/i", $row['Name'] ) )
			{
				continue;
			}
			
			if ( ! $this->isOkToGetThisTableData( $row['Name'] ) )
			{
				continue;
			}		
			
			/* Add to output array */
			$this->SchematicData[ $row['Name'] ] = array( 'cols' => array(), 'idx' => array(), 'data' => $row );
		}
		
		/* Now fetch table information */
		foreach( $this->SchematicData as $name => $data )
		{
			/* Fetch data */
			$this->DB->query( "DESCRIBE " . $name );
			
			while( $row = $this->DB->fetch() )
			{
				$this->SchematicData[ $name ]['cols'][] = $row;
			}
			
			/* Fetch index data */
			$this->DB->query( "SHOW INDEX FROM `" . $this->registry->dbFunctions()->getDatabaseName() . "`.`" . $name . "`" );
			
			while( $row = $this->DB->fetch() )
			{
				$this->SchematicData[ $name ]['idx'][] = $row;
			}
		}
		
		return $this->SchematicData;
	}
	
	/**
	 * Check to see if this is an ignored table
	 * @param string $table
	 */
	private function _isBlackListTable( $table )
	{
		return ( in_array( $table, $this->BlackList ) ) ? true : false;
	}
	
	/**
	 * Add slashes to single quotes to stop sql breaks
	 *
	 * @param	string	$value	String to add slashes too
	 * @return	string
	 */
	private function _makeValueSafeForQuery( $value )
	{
		return $this->DB->addSlashes( $value );
	}
	
	/**
	 * @param string $str
	 * @return boolean
	 */
	private function _isConcat( $str )
	{
		return ( substr( $str, 0, 10 ) == 'CONCAT_WS(' ) ? true : false;
	}
	
	/**
	 * Creates the post text
	 * @param string $sqlData
	 */
	private function _createTextToSend( $sqlData, $touchedTables=null )
	{
		$tables       = $this->_getDatabaseSchematics();
		$tblsToSend   = array();
		$tblsToCreate = array();
		$schematic    = '';
		
		/* Little short cut */
		$p = $this->registry->dbFunctions()->getPrefix();
		
		if ( is_array( $touchedTables ) )
		{
			foreach( $touchedTables as $tbl )
			{
				/* $touchedTables has no prefix */
				$tblsToSend[ $p . $tbl ] = $tables[ $p . $tbl ];
				
				$this->DB->query( "SHOW CREATE TABLE " . $p . $tbl );
				$_f = $this->DB->fetch();
				
				$tblsToCreate[] = str_replace( array( "\n", "\r" ), "\n", $_f['Create Table'] );
			}
		}
		
		$document = serialize( array( 'tableData' => $tblsToSend, 'createTables' => $tblsToCreate, 'sql' => $sqlData, 'vars' => $this->Vars ) );
		
		return $document;
	}
	
	/**
	 * Should be face-smashingly obvious.
	 * @return string
	 */
	private function _getBackUpKeyCacheFilename()
	{
		return IPS_CACHE_PATH . 'cache/backUpKeyCache.php';
	}
	
	/**
	 * Gets the backup server URL (clearly)
	 * @return string
	 */
	private function _getBackupServerUrl()
	{
		return 'http://gateway.backuptest.lw.ipslink.com/index.php';
	}
	
	/**
	 * Adds a log to the back up system
	 * @param int $rows
	 * @param string $status
	 */
	private function _addLog( $rows, $status )
	{
		$this->DB->insert( 'backup_log', array( 'log_row_count' => $rows, 'log_result' => $status ) );
	}
}

