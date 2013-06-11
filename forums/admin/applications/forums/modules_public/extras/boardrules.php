<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.4.5
 * Board Rules
 * Last Updated: $Date: 2012-10-10 17:49:08 -0400 (Wed, 10 Oct 2012) $
 * </pre>
 *
 * @author 		$Author $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/company/standards.php#license
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 11440 $
 */
if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_forums_extras_boardrules extends ipsCommand
{
	/**
	 * Class entry point
	 *
	 * @param	object		Registry reference
	 * @return	@e void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry )
	{		
		/* Get board rule (not cached) */
		$row = $this->DB->buildAndFetch( array( 'select' => '*', 'from' => 'core_sys_conf_settings', 'where' => "conf_key='gl_guidelines'" ) );

		IPSText::getTextClass( 'bbcode' )->parse_smilies	= 1;
		IPSText::getTextClass( 'bbcode' )->parse_html		= 1;
		IPSText::getTextClass( 'bbcode' )->parse_nl2br		= 1;
		IPSText::getTextClass( 'bbcode' )->parse_bbcode		= 1;
			
		$row['conf_value']	= IPSText::getTextClass( 'bbcode' )->preDisplayParse( ( $row['conf_value'] ) ? $row['conf_value'] : $row['conf_default'] );
		
		$this->registry->output->addNavigation( $this->settings['gl_title'], '' );
		$this->registry->output->setTitle( $this->settings['gl_title'] . ' - ' . ipsRegistry::$settings['board_name'] );
		$this->registry->output->addContent( $this->registry->output->getTemplate('emails')->boardRules( $this->settings['gl_title'], IPSText::getTextClass( 'bbcode' )->preDisplayParse( $row['conf_value'] ) ) );
		$this->registry->output->sendOutput();
	}
}