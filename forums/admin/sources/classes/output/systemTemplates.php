<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * System Templates
 * Last Updated: $Date: 2012-05-10 21:10:13 +0100 (Thu, 10 May 2012) $
 * </pre>
 *
 * @author 		Mark Wade
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		5th September 2012
 * @version		$Rev: 10721 $
 *
 */

/**
 * System Templates
 */
class systemTemplates
{
	/**
	 * Get Templates
	 *
	 * @return	array
	 */
	public function getList()
	{
		$templates = array();
	
		$directoryIterator = new DirectoryIterator( IPS_CACHE_PATH . 'cache/skin_cache/system' );
		foreach ( $directoryIterator as $file )
		{
			if ( !$file->isDot() and $file->isFile() and substr( $file, -4 ) === '.php' )
			{
				require_once $file->getPathName();
				
				$className = substr( $file, 0, -4 );
				if ( class_exists( $className ) )
				{
					$class = new $className;
					if ( $class instanceof systemTemplate )
					{
						$templates[ $className ] = $className;
					}
				}
			}
		}
		
		return $templates;
	}
	
	/**
	 * Get Class
	 *
	 * @param	string	Key
	 * @return	systemTemplate
	 * @throws	Exception
	 */
	public function getClass( $key )
	{
		/* If IPS_CACHE_PATH isn't set up quite yet */
		if ( ! defined( 'IPS_CACHE_PATH' ) )
		{
			define( 'IPS_CACHE_PATH', IPS_TEMP_CACHE_PATH );
		}
		
		$file = IPS_CACHE_PATH . 'cache/skin_cache/system/' . $key . '.php';
		if ( !file_exists( $file ) )
		{
			$this->writeDefaults();

			if( !file_exists( $file ) )
			{
				throw new Exception('NO_FILE');
			}
		}
		
		require_once $file;
		if ( !class_exists( $key ) )
		{
			throw new Exception('NO_CLASS');
		}
		
		$class = new $key;
		if ( !( $class instanceof systemTemplate ) )
		{
			throw new Exception('BAD_TEMPLATE');
		}
		
		if ( !method_exists( $class, 'getTemplate' ) )
		{
			throw new Exception( 'NO_GETTEMPLATE_METHOD' );
		}
		
		return $class;
	}
	
	/**
	 * Write
	 *
	 * @param	string		Key
	 * @param	array		Param Names
	 * @param	string		Content
	 * @return	int|false	Data written
	 */
	public function write( $key, $paramNames, $content )
	{
		$implodedParamNames = implode( ', ', $paramNames );
	
		$fileContents = <<<CONTENT
<?php

class {$key} extends systemTemplate
{
	public function getTemplate( {$implodedParamNames} )
	{
		return <<<EOF
{$content}
EOF;

	}
}

CONTENT;

		if ( ! defined('IPS_CACHE_PATH') )
		{
			define( 'IPS_CACHE_PATH', DOC_IPS_ROOT_PATH );
		}

		return file_put_contents( IPS_CACHE_PATH . 'cache/skin_cache/system/' . $key . '.php', $fileContents );
		
	}
	
	/**
	 * Revert
	 *
	 * @param	string		Key
	 * @return	int|false	Data written
	 */
	public function revert( $key )
	{		
		$defaultTemplates = file_get_contents( IPS_ROOT_PATH . 'setup/xml/system_templates.xml' );
		
		require_once IPS_KERNEL_PATH . 'classXML.php';
		$xml = new classXML( 'utf-8' );
		$xml->loadXML( $defaultTemplates );
		
		$array = $xml->fetchXMLAsArray();
		
		foreach ( $array['system_templates']['template'] as $template )
		{
			if ( $template['key']['#alltext'] == $key )
			{
				$params = array();
				foreach ( $template['params']['param'] as $p )
				{
					$params[] = $p['#alltext'];
				}
				
				return $this->write( $key, $params, $template['content']['#alltext'] );
			}
		}
		
		throw new Exception('NO_DEFAULT_VALUE');
	}
	
	/**
	 * Write Default Values
	 */
	public function writeDefaults()
	{
		$defaultTemplates = file_get_contents( IPS_ROOT_PATH . 'setup/xml/system_templates.xml' );
	
		if ( !class_exists( 'classXML' ) )
		{
			require_once IPS_KERNEL_PATH . 'classXML.php';
		}
		$xml = new classXML( 'utf-8' );
		$xml->loadXML( $defaultTemplates );
		
		$array = $xml->fetchXMLAsArray();
				
		foreach ( $array['system_templates']['template'] as $template )
		{
			$params = array();
			foreach ( $template['params']['param'] as $p )
			{
				$params[] = $p['#alltext'];
			}
			
			$this->write( $template['key']['#alltext'], $params, $template['content']['#alltext'] );
		}
	}
}

/**
 * Abstract System Template Class
 */
abstract class systemTemplate
{
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	@e void
	 */	
	public function __construct()
	{
		
	}
}

/**
 * Passthrough variable for displaying in templates
 */
class fakeArray implements ArrayAccess
{
	public function __construct( $name )
	{
		$this->name = $name;
	}

	public function offsetExists ( $offset )
	{
		return true;
	}
	
	public function offsetGet ( $offset )
	{
		return '{$'.$this->name.'[\'' . $offset . '\']}';
	}
	
	public function offsetSet ( $offset , $value )
	{
	}
	
	public function offsetUnset ( $offset )
	{
	}
}