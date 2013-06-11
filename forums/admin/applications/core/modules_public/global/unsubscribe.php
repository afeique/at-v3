<?php
/**
 * @file		unsubscribe.php 	Unsubscribes user from bulk mails
 *~TERABYTE_DOC_READY~
 * $Copyright: (c) 2001 - 2011 Invision Power Services, Inc.$
 * $License: http://www.invisionpower.com/company/standards.php#license$
 * $Author: ips_terabyte $
 * @since		23 August 2012
 * $LastChangedDate: 2012-04-05 17:35:31 +0100 (Thu, 05 Apr 2012) $
 * @version		v3.4.5
 * $Revision: 10571 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
 * @class		public_core_global_unsubscribe
 * @brief		Unsubscribes user from bulk mails
 */
class public_core_global_unsubscribe extends ipsCommand
{
	/**
	 * Main function executed automatically by the controller
	 *
	 * @param	object		$registry		Registry object
	 * @return	@e void
	 */
	public function doExecute( ipsRegistry $registry )
	{	
		$member = IPSMember::load( intval( $this->request['member'] ), 'none', 'id' );
		if ( $member['member_id'] and $this->request['key'] == md5( $member['email'] . ':' . $member['members_pass_hash'] ) )
		{
			IPSMember::save( $member['member_id'], array( 'members' => array( 'allow_admin_mails' => 0 ) ) );
			
			$this->registry->getClass('output')->addContent( $this->registry->output->getTemplate( 'ucp' )->unsubscribed() );
			$this->registry->getClass('output')->sendOutput();
		}
						
		$this->registry->output->showError( 'email_no_unsubscribe' );
    }
}