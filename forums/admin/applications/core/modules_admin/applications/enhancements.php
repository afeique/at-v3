<?php
/**
 * @file		applications.php 	Community Enhancements
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: mark $
 * @since		24 July 2012
 * $LastChangedDate: 2012-06-20 10:50:23 +0100 (Wed, 20 Jun 2012) $
 * @version		v3.4.5
 * $Revision: 10952 $
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

/**
 *
 * @class		admin_core_applications_enhancements
 * @brief		Community Enhancements
 */
class admin_core_applications_enhancements extends ipsCommand
{
	/**
	 * Main function executed automatically by the controller
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry )
	{
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_applications' );
		$this->registry->class_localization->loadLanguageFile( array( 'admin_applications' ) );
		
		switch( $this->request['do'] )
		{
			case 'edit':
				$this->edit();
				break;
				
			case 'save':
				$this->save();
				break;
		
			default:
				$this->manage();
				break;
		}
		
		$this->registry->getClass( 'output' )->html_main .= $this->registry->getClass( 'output' )->global_template->global_frame_wrapper();
		$this->registry->getClass( 'output' )->sendOutput();
	}
	
	/**
	 * Manage
	 */
	public function manage()
	{
		$enhancements = array();
		
		foreach ( IPSLib::getEnabledApplications() as $app )
		{
			if ( is_dir( IPSLib::getAppDir( $app['app_directory'] ) . '/extensions/enhancements' ) )
			{
				$dir = new DirectoryIterator( IPSLib::getAppDir( $app['app_directory'] ) . '/extensions/enhancements' );
				foreach ( $dir as $file )
				{
					if ( $file->isFile() and !$file->isDot() and substr( $file, 0, 1 ) !== '.' and substr( $file, -4 ) === '.php' )
					{					
						$className = "enhancements_{$app['app_directory']}_" . str_replace( '.php', '', $file->getFilename() );
						
						/* Backup system isn't ready */
						if ( $className == 'enhancements_core_ipsbackup' )
						{
							continue;
						}
						
						$classToLoad = IPSLib::loadLibrary( $file->getPathName(), $className );
						$enhancements[ $className ] = new $classToLoad( $this->registry );
					}
				}
			}
		}
				
		uasort( $enhancements, create_function( '$a, $b', 'return $a->title > $b->title;' ) );
		
		$this->registry->output->html .= $this->html->communityEnhancements( $enhancements );
	}
	
	/**
	 * Edit
	 */
	public function edit()
	{
		$exploded = explode( '_', $this->request['service'] );
		if ( !IPSLib::appIsInstalled( $exploded[1] ) or !is_file( IPSLib::getAppDir( $exploded[1] ) . '/extensions/enhancements/' . $exploded[2] . '.php' ) )
		{
			$this->registry->output->showError( $this->lang->words['err_no_enhancement'], 111800 );
		}
		
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( $exploded[1] ) . '/extensions/enhancements/' . $exploded[2] . '.php', $this->request['service'] );
		$class = new $classToLoad( $this->registry );
		
		if ( method_exists( $class, 'check' ) )
		{
			$class->check();
		}
				
		if ( isset( $class->settings ) and !empty( $class->settings ) )
		{
			$settingsClass = IPSLib::loadActionOverloader( IPSLib::getAppDir('core') . '/modules_admin/settings/settings.php', 'admin_core_settings_settings' );
			$settingsClass = new $settingsClass();
			$settingsClass->makeRegistryShortcuts( $this->registry );
			$settingsClass->html = $this->registry->output->loadTemplate('cp_skin_settings');
			
			$html = '';
		
			$imploded = implode( ', ', array_map( create_function( '$v', 'return "\'{$v}\'";' ), $class->settings ) );
			$settings = array();
			$this->DB->build( array( 'select' => '*', 'from' => 'core_sys_conf_settings', 'where' => "conf_key IN( {$imploded} )", 'order' => 'conf_position' ) );
			$e = $this->DB->execute();
			while ( $row = $this->DB->fetch( $e ) )
			{
				$html .= $settingsClass->_processSettingEntry( $row );
			}		
		
			$this->registry->output->html .= $this->html->communityEnhancementsSettings( $class, $html );
		}
		else
		{
			$this->registry->output->html .= $class->editSettings();
		}
	}
	
	/**
	 * Save
	 */
	public function save()
	{
		$exploded = explode( '_', $this->request['service'] );
		if ( !IPSLib::appIsInstalled( $exploded[1] ) or !is_file( IPSLib::getAppDir( $exploded[1] ) . '/extensions/enhancements/' . $exploded[2] . '.php' ) )
		{
			$this->registry->output->showError( $this->lang->words['err_no_enhancement'], 111801 );
		}
		
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( $exploded[1] ) . '/extensions/enhancements/' . $exploded[2] . '.php', $this->request['service'] );
		$class = new $classToLoad( $this->registry );
		
		if ( method_exists( $class, 'check' ) )
		{
			$class->check();
		}
		
		if ( isset( $class->settings ) and !empty( $class->settings ) )
		{
			$toSave = array();
			foreach ( $class->settings as $key )
			{
				$toSave[ $key ] = $_POST[ $key ];
			}
			IPSLib::updateSettings( $toSave, TRUE );
		}
		else
		{
			$output = $class->saveSettings();
			if ( $output and is_string( $output ) )
			{
				$this->registry->output->html .= $output;
				return;
			}
		}
		
		$this->registry->output->redirect( "{$this->settings['base_url']}app=core&amp;module=applications&amp;section=enhancements", $this->lang->words['enhancements_saved'] );
	}
}