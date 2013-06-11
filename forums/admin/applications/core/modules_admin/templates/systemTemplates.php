<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Manage System Templates
 * Last Updated: $Date: 2011-05-05 12:03:47 +0100 (Thu, 05 May 2011) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2012 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		5th September 2012
 * @version		$Revision: 8644 $
 *
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class admin_core_templates_systemtemplates extends ipsCommand
{
	/**
	 * Class entry point
	 *
	 * @param	object		Registry reference
	 * @return	@e void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Init
		//-----------------------------------------
	
		$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'systemtemplates' );
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'admin_templates' ), 'core' );
		$this->html = $this->registry->output->loadTemplate('cp_skin_templates');
	
		//-----------------------------------------
		// Get the class that handles this
		//-----------------------------------------
	
		$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/output/systemTemplates.php', 'systemTemplates' );
		$this->systemTemplates = new $classToLoad;
	
		//-----------------------------------------
		// What are we doing?
		//-----------------------------------------
		
		switch ( $this->request['do'] )
		{
			case 'edit':
				$this->edit();
				break;
				
			case 'save':
				$this->save();
				break;
				
			case 'revert':
				$this->revert();
				break;
				
			case 'preview':
				$this->preview();
				break;
		
			default:	
				$this->manage();
				break;
		}
		
		//-----------------------------------------
		// Output
		//-----------------------------------------
		
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
	
	/**
	 * Show List
	 */
	public function manage()
	{
		$this->registry->output->html .= $this->html->systemTemplatesList( is_writable( IPS_CACHE_PATH . 'cache/skin_cache/system' ), $this->systemTemplates->getList() );
	}
	
	/**
	 * Edit
	 */
	public function edit()
	{
		$key = $this->request['k'];
		
		try
		{
			$class = $this->systemTemplates->getClass( $key );
			
			if ( !is_writable( IPS_CACHE_PATH . 'cache/skin_cache/system/' . $key . '.php' ) )
			{
				$this->registry->output->showError( sprintf( $this->lang->words['system_templates_nowrite_file'], IPS_CACHE_PATH . 'cache/skin_cache/system/' . $key . '.php' ) );
			}
			
			$reflector = new ReflectionClass( $class );
			$params = $reflector->getMethod( 'getTemplate' )->getParameters();
						
			$paramsToPass = array();
			foreach ( $params as $p )
			{				
				if ( !$p->isOptional() or !is_array( $p->getDefaultValue() ) )
				{
					$paramsToPass[] = '{$' . $p->name . '}';
				}
				else
				{
					$paramsToPass[] = new fakeArray( $p->name );
				}
			}
			
			$this->registry->output->html .= $this->html->systemTemplatesEdit( $key, $key, $params, str_replace( '&', '&amp;', call_user_func_array( array( $class, 'getTemplate' ), $paramsToPass ) ) );
		}
		catch ( Exception $e )
		{
			$this->registry->output->showError( 'system_templates_err' );
		}
	}
	
	/**
	 * Preview
	 */
	public function preview()
	{
		$key = $this->request['k'];
		
		try
		{
			$class = $this->systemTemplates->getClass( $key );
									
			$reflector = new ReflectionClass( $class );
			$params = $reflector->getMethod( 'getTemplate' )->getParameters();
						
			$paramsToPass = array();
			foreach ( $params as $p )
			{				
				if ( !$p->isOptional() or !is_array( $p->getDefaultValue() ) )
				{
					$paramsToPass[] = '{$' . $p->name . '}';
				}
				else
				{
					if ( $p->name == 'settings' )
					{
						$paramsToPass[] = ipsRegistry::$settings;	
					}
					else
					{
						$paramsToPass[] = new fakeArray( $p->name );
					}
				}
			}
			
			echo call_user_func_array( array( $class, 'getTemplate' ), $paramsToPass );
			exit;
		}
		catch ( Exception $e )
		{
			$this->registry->output->showError( 'system_templates_err' );
		}
	}
	
	/**
	 * Save
	 */
	public function save()
	{
		$key = $this->request['k'];
		
		try
		{
			$class = $this->systemTemplates->getClass( $key );
			
			$reflector = new ReflectionClass( $class );
			$params = array();
			foreach ( $reflector->getMethod( 'getTemplate' )->getParameters() as $param )
			{						
				$params[] = '$' . $param->name . ( $param->isOptional() ? ( ' = ' . var_export( $param->getDefaultValue(), TRUE ) ) : '' );
			}
						
			if ( $this->systemTemplates->write( $key, $params, $_POST['content'] ) === FALSE )
			{
				$this->registry->output->showError( 'system_templates_errwrite' );
			}
			
			$this->registry->output->redirect( "{$this->settings['base_url']}app=core&&module=templates&section=systemTemplates", $this->lang->words['system_templates_saved'] );
		}
		catch ( Exception $e )
		{
			$this->registry->output->showError( 'system_templates_err' );
		}
	}
	
	/**
	 * Revert
	 */
	public function revert()
	{		
		$key = $this->request['k'];
		
		try
		{
			$this->systemTemplates->revert( $key );
			$this->registry->output->redirect( "{$this->settings['base_url']}app=core&&module=templates&section=systemTemplates", $this->lang->words['system_templates_saved'] );
		}
		catch ( Exception $e )
		{
			$this->registry->output->showError( 'system_templates_err' );
		}
	}

}