<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 3               */
/* CACHE FILE: Generated: Mon, 10 Jun 2013 14:58:21 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_profile_3 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['acknowledgeWarning'] = array('valueIsPermanent','hasValue','options','memberNote','hasReasonAndContent','hasContent','hasReason','hasExpireDate','hasExpiration','hasPoints','isVerbalWarning','valueIsPermanent','hasValue','options','warningsAcknowledgeMessage','memberNote','hasReasonAndContent','hasContent','hasReason','hasExpireDate','hasExpiration','hasPoints','isVerbalWarning','warningsAcknowledge');
$this->_funcHooks['addWarning'] = array('reasons','canUseAsBanGroup','banGroups','hasOtherOption','currentMq','currentRpa','currentSuspend','reasons','canUseAsBanGroup','banGroups','hasOtherOption','currentMq','currentRpa','currentSuspend');
$this->_funcHooks['customField__gender'] = array('male','female','nottelling','gender_set','male','female','nottelling','gender_set');
$this->_funcHooks['customField__generic'] = array('genericIsArray','genericIsArray');
$this->_funcHooks['customFieldGroup__contact'] = array('cfieldgroups','cf_icon','cf_skype','cf_jabber','cf_website','cf_icq','cf_yahoo','cf_msn','cf_aim','cf_array','contact_field','cfieldgroups','cf_icon','cf_skype','cf_jabber','cf_website','cf_icq','cf_yahoo','cf_msn','cf_aim','cf_array','contact_field');
$this->_funcHooks['customizeProfile'] = array('hasBackgroundColor','backgroundIsFixed','hasBackgroundImage','hasBodyCustomization','hasBackgroundColor','backgroundIsFixed','hasBackgroundImage','hasBodyCustomization');
$this->_funcHooks['dnameWrapper'] = array('records','isAjaxModule','hasDnameHistory','records','isAjaxModule','hasDnameHistory');
$this->_funcHooks['explainPoints'] = array('reasons','valueIsPermanent','hasValue','options','actions','hasActions','reasons','valueIsPermanent','hasValue','options','actions','hasActions');
$this->_funcHooks['friendsList'] = array('norep','posrep','negrep','repson','weAreSupmod','addfriend','notus','sendpm','blog','gallery','norep','posrep','negrep','repson','loopOnPending','friendsList','friendListPages','tabIsList','tabIsPending','friendListNone','hasFriendsList','friendListPagesBottom','friendListRate1','friendListRate2','friendListRate3','friendListRate4','friendListRate5','friendListRate','friendListRepPos','friendListRepNeg','friendListRepZero','friendListRep','friendListIsFriend','friendListIsMember','friendListSendPm','friendListSearchType','friendListBlog','friendListGallery','friendListRateApp1','friendListRateApp2','friendListRateApp3','friendListRateApp4','friendListRateApp5','friendListAllowRate','friendListAppRepPos','friendListAppRepNeg','friendListAppRepZero','friendListAppRep','loopOnPending','friendsList','friendListPages','tabIsList','tabIsPending','friendListNone','hasFriendsList','friendListPagesBottom');
$this->_funcHooks['listWarnings'] = array('hasReason','warnings','paginationTop','canWarn','hasPaginationOrWarn','noWarnings','paginationBottom','hasReason','warnings','paginationTop','canWarn','hasPaginationOrWarn','noWarnings','paginationBottom');
$this->_funcHooks['photoEditor'] = array('canHasUpload','canHasURL','allowGravatars','hasTwitter','hasFacebook');
$this->_funcHooks['profileModern'] = array('tabactive','tabs','warnClickable','warnPopup','warnIsSet','warnsLoop','pcfieldsLoop','pcfieldsOtherLoopCheckInner','pcfieldsOtherLoopCheck2','pcfieldsOtherLoopCheck','pcfieldsOtherLoop','cfields','friendsLoop','visitorismember','latest_visitors_loop','jsIsFriend','friendsEnabled','hasCustomization','weAreSupmod','weAreOwner','supModCustomization','canEditUser','canEditPic','haswarn','hasWarns','onlineDetails','userStatus','rate1','rate2','rate3','rate4','rate5','rated1','rated2','rated3','rated4','rated5','hasrating','noRateYourself','allowRate','isFriend','noFriendYourself','pmlink','member_title','member_age','member_bday_year','member_birthday','pcfields','pcfieldsOther','showContactHead','isadmin','member_contact_fields','hasContactFields','RepPositive','RepNegative','RepZero','RepText','RepImage','ourReputation','authorspammerinner','authorspammer','dnameHistory','supModCustomizationDisable','checkModTools','hasFriends','has_visitors','latest_visitors','thisIsNotUs','authorspammerinner','authorspammer','member_title','member_age','member_bday_year','member_birthday','isFriend','noFriendYourself','pmlink','tabs','pmlink');
$this->_funcHooks['reputationPage'] = array('isTheActiveApp','apps','hasMoreThanOneApp','hasResults','isTheActiveApp','apps','hasMoreThanOneApp','hasResults');
$this->_funcHooks['showCard'] = array('cardRepPos','cardRepNeg','cardRepZero','cardRep','cardSendPm','cardStatus','cardOnline','cardWhere','isadmin','authorspammerinner','authorspammer','cardIsFriend','cardFriend','cardBlog','cardGallery','cardStatus','cardOnline','cardWhere','isadmin','authorspammerinner','authorspammer','cardIsFriend','cardFriend','cardSendPm','cardFindPostsLink','cardBlog','cardGallery','cardRepPos','cardRepNeg','cardRepZero','cardRep');
$this->_funcHooks['statusReplies'] = array('canDelete','innerLoop','noWrapperTop','noWrapperBottom','canDelete','innerLoop','canDelete');
$this->_funcHooks['statusUpdates'] = array('isUs','moderated','forSomeoneElse','noLocked','cImg','creatorText','canDelete','isLocked','canLock','isUnapproved','addReturn','hasMore','hasReplies','canReply','maxReplies','statusApproved','outerLoop','moderated','forSomeoneElse','statusApproved','outerLoop','canDelete','outerLoop');
$this->_funcHooks['statusUpdatesPage'] = array('tabactive','tabactive2','updateTwitter','updateFacebook','update','canCreate','hasUpdates','hasPagination','hasUpdates');
$this->_funcHooks['tabFriends'] = array('friends','friends_loop','friends','friends','friends_loop','friends','friends');
$this->_funcHooks['tabReputation'] = array('isTheActiveApp','apps','hasMoreThanOneApp','currentIsGiven','canViewRep','currentIsReceived','hasResults','bottomPagination','isTheActiveApp','apps','hasMoreThanOneApp','currentIsGiven','canViewRep','currentIsReceived','hasResults','bottomPagination');
$this->_funcHooks['tabReputation_calendar'] = array('postMid','postMember','postMid','postMember','postMid','postMember','postMid','postMember');
$this->_funcHooks['tabReputation_posts'] = array('notLastFtAsForum','topicsForumTrail','postMid','postMember','hasForumTrail','notLastFtAsForum','topicsForumTrail','postMid','postMember','hasForumTrail');
$this->_funcHooks['tabSingleColumn'] = array('singleColumnUrl','singleColumnTitle','date','singleColumnUrl','singleColumnTitle');
$this->_funcHooks['tabStatusUpdates'] = array('updateTwitter','updateFacebook','update','canCreate','leave_comment','hasUpdates','actions','canCreate','hasUpdates','canCreate','leave_comment','hasUpdates');


}

/* -- acknowledgeWarning --*/
function acknowledgeWarning($warning) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['acknowledgeWarning'] ) )
{
$count_56c32af8b03c30cfde0e07c62fa6732d = is_array($this->functionData['acknowledgeWarning']) ? count($this->functionData['acknowledgeWarning']) : 0;
$this->functionData['acknowledgeWarning'][$count_56c32af8b03c30cfde0e07c62fa6732d]['warning'] = $warning;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- addWarning --*/
function addWarning($member, $reasons, $errors, $editor) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['addWarning'] ) )
{
$count_191a82ea5a7641d6cd22061424334a43 = is_array($this->functionData['addWarning']) ? count($this->functionData['addWarning']) : 0;
$this->functionData['addWarning'][$count_191a82ea5a7641d6cd22061424334a43]['member'] = $member;
$this->functionData['addWarning'][$count_191a82ea5a7641d6cd22061424334a43]['reasons'] = $reasons;
$this->functionData['addWarning'][$count_191a82ea5a7641d6cd22061424334a43]['errors'] = $errors;
$this->functionData['addWarning'][$count_191a82ea5a7641d6cd22061424334a43]['editor'] = $editor;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- customField__gender --*/
function customField__gender($f) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['customField__gender'] ) )
{
$count_48dd27351625e8474c3ee458dd53219a = is_array($this->functionData['customField__gender']) ? count($this->functionData['customField__gender']) : 0;
$this->functionData['customField__gender'][$count_48dd27351625e8474c3ee458dd53219a]['f'] = $f;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- customField__generic --*/
function customField__generic($f) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['customField__generic'] ) )
{
$count_0890fdb8022ed697fc78115f8f38012b = is_array($this->functionData['customField__generic']) ? count($this->functionData['customField__generic']) : 0;
$this->functionData['customField__generic'][$count_0890fdb8022ed697fc78115f8f38012b]['f'] = $f;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- customFieldGroup__contact --*/
function customFieldGroup__contact($f) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['customFieldGroup__contact'] ) )
{
$count_48dd68a16751ea0517e7c3117050adc2 = is_array($this->functionData['customFieldGroup__contact']) ? count($this->functionData['customFieldGroup__contact']) : 0;
$this->functionData['customFieldGroup__contact'][$count_48dd68a16751ea0517e7c3117050adc2]['f'] = $f;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- customizeProfile --*/
function customizeProfile($member) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['customizeProfile'] ) )
{
$count_e23ae1bd0829900c0999222ba887c83d = is_array($this->functionData['customizeProfile']) ? count($this->functionData['customizeProfile']) : 0;
$this->functionData['customizeProfile'][$count_e23ae1bd0829900c0999222ba887c83d]['member'] = $member;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- dnameWrapper --*/
function dnameWrapper($member_name="",$records=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['dnameWrapper'] ) )
{
$count_7df6358562248291126dcd960f1f0203 = is_array($this->functionData['dnameWrapper']) ? count($this->functionData['dnameWrapper']) : 0;
$this->functionData['dnameWrapper'][$count_7df6358562248291126dcd960f1f0203]['member_name'] = $member_name;
$this->functionData['dnameWrapper'][$count_7df6358562248291126dcd960f1f0203]['records'] = $records;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- explainPoints --*/
function explainPoints($reasons, $actions) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['explainPoints'] ) )
{
$count_a97a28709e9d3dde223d6a671c0687ce = is_array($this->functionData['explainPoints']) ? count($this->functionData['explainPoints']) : 0;
$this->functionData['explainPoints'][$count_a97a28709e9d3dde223d6a671c0687ce]['reasons'] = $reasons;
$this->functionData['explainPoints'][$count_a97a28709e9d3dde223d6a671c0687ce]['actions'] = $actions;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- friendsList --*/
function friendsList($friends, $pages) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['friendsList'] ) )
{
$count_8ec9e06e544fe95bdf439a3030212273 = is_array($this->functionData['friendsList']) ? count($this->functionData['friendsList']) : 0;
$this->functionData['friendsList'][$count_8ec9e06e544fe95bdf439a3030212273]['friends'] = $friends;
$this->functionData['friendsList'][$count_8ec9e06e544fe95bdf439a3030212273]['pages'] = $pages;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- listWarnings --*/
function listWarnings($member, $warnings, $pagination, $reasons, $canWarn) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['listWarnings'] ) )
{
$count_97586a87ecee768ba87117182d24edec = is_array($this->functionData['listWarnings']) ? count($this->functionData['listWarnings']) : 0;
$this->functionData['listWarnings'][$count_97586a87ecee768ba87117182d24edec]['member'] = $member;
$this->functionData['listWarnings'][$count_97586a87ecee768ba87117182d24edec]['warnings'] = $warnings;
$this->functionData['listWarnings'][$count_97586a87ecee768ba87117182d24edec]['pagination'] = $pagination;
$this->functionData['listWarnings'][$count_97586a87ecee768ba87117182d24edec]['reasons'] = $reasons;
$this->functionData['listWarnings'][$count_97586a87ecee768ba87117182d24edec]['canWarn'] = $canWarn;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- photoEditor --*/
function photoEditor($data, $member) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['photoEditor'] ) )
{
$count_e5ba08ecb05d4ae3fdd9c6173816aa4f = is_array($this->functionData['photoEditor']) ? count($this->functionData['photoEditor']) : 0;
$this->functionData['photoEditor'][$count_e5ba08ecb05d4ae3fdd9c6173816aa4f]['data'] = $data;
$this->functionData['photoEditor'][$count_e5ba08ecb05d4ae3fdd9c6173816aa4f]['member'] = $member;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- profileModern --*/
function profileModern($tabs=array(), $member=array(), $visitors=array(), $default_tab='status', $default_tab_content='', $friends=array(), $status=array(), $warns=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['profileModern'] ) )
{
$count_f598b38dacbee8ed49fd73b3b7df8b12 = is_array($this->functionData['profileModern']) ? count($this->functionData['profileModern']) : 0;
$this->functionData['profileModern'][$count_f598b38dacbee8ed49fd73b3b7df8b12]['tabs'] = $tabs;
$this->functionData['profileModern'][$count_f598b38dacbee8ed49fd73b3b7df8b12]['member'] = $member;
$this->functionData['profileModern'][$count_f598b38dacbee8ed49fd73b3b7df8b12]['visitors'] = $visitors;
$this->functionData['profileModern'][$count_f598b38dacbee8ed49fd73b3b7df8b12]['default_tab'] = $default_tab;
$this->functionData['profileModern'][$count_f598b38dacbee8ed49fd73b3b7df8b12]['default_tab_content'] = $default_tab_content;
$this->functionData['profileModern'][$count_f598b38dacbee8ed49fd73b3b7df8b12]['friends'] = $friends;
$this->functionData['profileModern'][$count_f598b38dacbee8ed49fd73b3b7df8b12]['status'] = $status;
$this->functionData['profileModern'][$count_f598b38dacbee8ed49fd73b3b7df8b12]['warns'] = $warns;
}
$IPBHTML .= "<template>profileView</template>
<profileData>
	<id>{$member['member_id']}</id>
	<name><![CDATA[{$member['members_display_name']}]]></name>
	<memberTitle><![CDATA[{$member['title']}]]></memberTitle>
	<reputation>{$member['pp_reputation_points']}</reputation>
	<postCount>{$member['posts']}</postCount>
	<avatar><![CDATA[{$member['pp_main_photo']}]]></avatar>	
</profileData>
<tab><![CDATA[{$default_tab}]]></tab>
" . (($default_tab == 'core:info') ? ("" . ((($member['member_id'] != $this->memberData['member_id']) AND $this->memberData['g_use_pm'] AND $this->memberData['members_disable_pm'] == 0 AND IPSLib::moduleIsEnabled( 'messaging', 'members' ) AND $member['members_disable_pm'] == 0) ? ("
<pmMeLink><![CDATA[" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=members&amp;module=messaging&amp;section=send&amp;do=form&amp;fromMemberID={$member['member_id']}", "public",'' ), "", "" ) . "]]></pmMeLink>
") : ("")) . "
<viewMyContent><![CDATA[" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "app=core&amp;module=search&amp;do=user_activity&amp;mid={$member['member_id']}", "public",'' ), "", "" ) . "]]></viewMyContent>
<profileTabs>
	".$this->__f__51f3d8b60bffe6ed552e0c76f5b8cccc($tabs,$member,$visitors,$default_tab,$default_tab_content,$friends,$status,$warns)."</profileTabs>") : ("
	{$default_tab_content}
")) . "";
return $IPBHTML;
}


function __f__51f3d8b60bffe6ed552e0c76f5b8cccc($tabs=array(), $member=array(), $visitors=array(), $default_tab='status', $default_tab_content='', $friends=array(), $status=array(), $warns=array())
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $tabs as $tab )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
		<profileTab>
			<name><![CDATA[{$tab['_lang']}]]></name>
			<url><![CDATA[" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$member['member_id']}&amp;tab={$tab['plugin_key']}", "public",'' ), "{$member['members_seo_name']}", "showuser" ) . "]]></url>
		</profileTab>
	
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- reputationPage --*/
function reputationPage($langBit, $currentApp='', $supportedApps=array(), $processedResults='') {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['reputationPage'] ) )
{
$count_d6dcea171ea1f63bfe5e3563c004f205 = is_array($this->functionData['reputationPage']) ? count($this->functionData['reputationPage']) : 0;
$this->functionData['reputationPage'][$count_d6dcea171ea1f63bfe5e3563c004f205]['langBit'] = $langBit;
$this->functionData['reputationPage'][$count_d6dcea171ea1f63bfe5e3563c004f205]['currentApp'] = $currentApp;
$this->functionData['reputationPage'][$count_d6dcea171ea1f63bfe5e3563c004f205]['supportedApps'] = $supportedApps;
$this->functionData['reputationPage'][$count_d6dcea171ea1f63bfe5e3563c004f205]['processedResults'] = $processedResults;
}
$IPBHTML .= "<!--no data in this master skin-->";
return $IPBHTML;
}

/* -- showCard --*/
function showCard($member, $download=0) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['showCard'] ) )
{
$count_9c609b720fa611887753abe71cc39e40 = is_array($this->functionData['showCard']) ? count($this->functionData['showCard']) : 0;
$this->functionData['showCard'][$count_9c609b720fa611887753abe71cc39e40]['member'] = $member;
$this->functionData['showCard'][$count_9c609b720fa611887753abe71cc39e40]['download'] = $download;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- statusReplies --*/
function statusReplies($replies=array(), $no_wrapper=false) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['statusReplies'] ) )
{
$count_3a43d929fedfd6c7c0fe3ba1a354afb1 = is_array($this->functionData['statusReplies']) ? count($this->functionData['statusReplies']) : 0;
$this->functionData['statusReplies'][$count_3a43d929fedfd6c7c0fe3ba1a354afb1]['replies'] = $replies;
$this->functionData['statusReplies'][$count_3a43d929fedfd6c7c0fe3ba1a354afb1]['no_wrapper'] = $no_wrapper;
}
$IPBHTML .= "<commentReplies>
	".$this->__f__37f129914316d73bc42b01709565c28e($replies,$no_wrapper)."</commentReplies>";
return $IPBHTML;
}


function __f__37f129914316d73bc42b01709565c28e($replies=array(), $no_wrapper=false)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $replies as $reply )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
		<commentReply>
			<author><![CDATA[{$reply['members_display_name']}]]></author>
			<avatar><![CDATA[{$reply['pp_main_photo']}]]></avatar>	
			<reply><![CDATA[{$reply['reply_content']}]]></reply>
			<date>{$reply['reply_date_formatted']}</date>
			<canDelete>" . (($reply['_canDelete']) ? ("1") : ("0")) . "</canDelete>
			<deleteURL><![CDATA[{$this->settings['base_url']}app=members&amp;module=profile&amp;section=status&amp;do=deleteReply&amp;status_id={$reply['reply_status_id']}&amp;reply_id={$reply['reply_id']}&amp;k={$this->member->form_hash}]]></deleteURL>
		</commentReply>
	
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- statusUpdates --*/
function statusUpdates($updates=array(), $smallSpace=0, $latestOnly=0) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['statusUpdates'] ) )
{
$count_7d2bf7900af38a9d0e5c67695935a4e9 = is_array($this->functionData['statusUpdates']) ? count($this->functionData['statusUpdates']) : 0;
$this->functionData['statusUpdates'][$count_7d2bf7900af38a9d0e5c67695935a4e9]['updates'] = $updates;
$this->functionData['statusUpdates'][$count_7d2bf7900af38a9d0e5c67695935a4e9]['smallSpace'] = $smallSpace;
$this->functionData['statusUpdates'][$count_7d2bf7900af38a9d0e5c67695935a4e9]['latestOnly'] = $latestOnly;
}
$IPBHTML .= "<profileComments>".$this->__f__902b3de913dfea891ab8cc38b978b04a($updates,$smallSpace,$latestOnly)."</profileComments>";
return $IPBHTML;
}


function __f__902b3de913dfea891ab8cc38b978b04a($updates=array(), $smallSpace=0, $latestOnly=0)
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $updates as $id => $status )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
		<profileComment>
			<author><![CDATA[{$status['members_display_name']}]]></author>
			<avatar><![CDATA[{$status['pp_main_photo']}]]></avatar>	
			<reply><![CDATA[{$status['status_content']}]]></reply>
			<date>{$status['status_date_formatted']}</date>
			<canDelete>" . (($status['_canDelete']) ? ("1") : ("0")) . "</canDelete>
			<deleteURL><![CDATA[{$this->settings['base_url']}app=members&amp;module=profile&amp;section=status&amp;do=deleteReply&amp;status_id={$status['status_status_id']}&amp;reply_id={$status['status_id']}&amp;k={$this->member->form_hash}]]></deleteURL>
			" . (($status['status_replies'] AND count( $status['replies'] )) ? ("
				" . ( method_exists( $this->registry->getClass('output')->getTemplate('profile'), 'statusReplies' ) ? $this->registry->getClass('output')->getTemplate('profile')->statusReplies($status['replies'], 1) : '' ) . "
			") : ("")) . "
			" . (($status['_userCanReply']) ? ("
					<replyURL><![CDATA[{$this->settings['base_url']}app=members&amp;module=profile&amp;section=status&amp;do=reply&amp;status_id={$status['status_id']}&amp;k={$this->member->form_hash}&amp;id={$this->memberData['member_id']}]]></replyURL>
			") : ("")) . "
		</profileComment>
	
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- statusUpdatesPage --*/
function statusUpdatesPage($updates=array(), $pages='') {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['statusUpdatesPage'] ) )
{
$count_2477c3a9a2839af68c80877de635792b = is_array($this->functionData['statusUpdatesPage']) ? count($this->functionData['statusUpdatesPage']) : 0;
$this->functionData['statusUpdatesPage'][$count_2477c3a9a2839af68c80877de635792b]['updates'] = $updates;
$this->functionData['statusUpdatesPage'][$count_2477c3a9a2839af68c80877de635792b]['pages'] = $pages;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- tabFriends --*/
function tabFriends($friends=array(), $member=array(), $pagination='') {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['tabFriends'] ) )
{
$count_9cccebf6fb6d0a7bb9f1153ef1373f20 = is_array($this->functionData['tabFriends']) ? count($this->functionData['tabFriends']) : 0;
$this->functionData['tabFriends'][$count_9cccebf6fb6d0a7bb9f1153ef1373f20]['friends'] = $friends;
$this->functionData['tabFriends'][$count_9cccebf6fb6d0a7bb9f1153ef1373f20]['member'] = $member;
$this->functionData['tabFriends'][$count_9cccebf6fb6d0a7bb9f1153ef1373f20]['pagination'] = $pagination;
}
$IPBHTML .= "<pagination>{$pagination}</pagination>
<friends>
	".$this->__f__d2fd08d68db5c9d78a31ee684b599341($friends,$member,$pagination)."</friends>";
return $IPBHTML;
}


function __f__d2fd08d68db5c9d78a31ee684b599341($friends=array(), $member=array(), $pagination='')
{
	$_ips___x_retval = '';
	$__iteratorCount = 0;
	foreach( $friends as $friend )
	{
		
		$__iteratorCount++;
		$_ips___x_retval .= "
		<friend>
			<url><![CDATA[" . $this->registry->getClass('output')->formatUrl( $this->registry->getClass('output')->buildUrl( "showuser={$friend['member_id']}", "public",'' ), "{$friend['members_seo_name']}", "showuser" ) . "]]></url>
			<avatar><![CDATA[{$friend['pp_small_photo']}]]></avatar>
			<name><![CDATA[{$friend['members_display_name']}]]></name>
			<memberTitle><![CDATA[{$friend['member_title']}]]></memberTitle>
		</friend>
	
";
	}
	$_ips___x_retval .= '';
	unset( $__iteratorCount );
	return $_ips___x_retval;
}

/* -- tabNoContent --*/
function tabNoContent($langkey) {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- tabPosts --*/
function tabPosts($content) {
$IPBHTML = "";
$IPBHTML .= "<posts>
	{$content}
</posts>";
return $IPBHTML;
}

/* -- tabReputation --*/
function tabReputation($member, $currentApp='', $type='', $supportedApps=array(), $processedResults='', $pagination='') {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['tabReputation'] ) )
{
$count_b121ff51ab45b8ce7b90c33096a0f79a = is_array($this->functionData['tabReputation']) ? count($this->functionData['tabReputation']) : 0;
$this->functionData['tabReputation'][$count_b121ff51ab45b8ce7b90c33096a0f79a]['member'] = $member;
$this->functionData['tabReputation'][$count_b121ff51ab45b8ce7b90c33096a0f79a]['currentApp'] = $currentApp;
$this->functionData['tabReputation'][$count_b121ff51ab45b8ce7b90c33096a0f79a]['type'] = $type;
$this->functionData['tabReputation'][$count_b121ff51ab45b8ce7b90c33096a0f79a]['supportedApps'] = $supportedApps;
$this->functionData['tabReputation'][$count_b121ff51ab45b8ce7b90c33096a0f79a]['processedResults'] = $processedResults;
$this->functionData['tabReputation'][$count_b121ff51ab45b8ce7b90c33096a0f79a]['pagination'] = $pagination;
}
$IPBHTML .= "<!--no data in this master skin-->";
return $IPBHTML;
}

/* -- tabReputation_calendar --*/
function tabReputation_calendar($results) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['tabReputation_calendar'] ) )
{
$count_b82edb42dd3f078ec24f205b48e65135 = is_array($this->functionData['tabReputation_calendar']) ? count($this->functionData['tabReputation_calendar']) : 0;
$this->functionData['tabReputation_calendar'][$count_b82edb42dd3f078ec24f205b48e65135]['results'] = $results;
}
$IPBHTML .= "<!--no data in this master skin-->";
return $IPBHTML;
}

/* -- tabReputation_posts --*/
function tabReputation_posts($results) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['tabReputation_posts'] ) )
{
$count_cb9bd81db8ddfb79eb827317a549852a = is_array($this->functionData['tabReputation_posts']) ? count($this->functionData['tabReputation_posts']) : 0;
$this->functionData['tabReputation_posts'][$count_cb9bd81db8ddfb79eb827317a549852a]['results'] = $results;
}
$IPBHTML .= "<!--no data in this master skin-->";
return $IPBHTML;
}

/* -- tabSingleColumn --*/
function tabSingleColumn($row=array(), $read_more_link='', $url='', $title='') {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['tabSingleColumn'] ) )
{
$count_d4f78226c7a03be55b61a1bf06cf555d = is_array($this->functionData['tabSingleColumn']) ? count($this->functionData['tabSingleColumn']) : 0;
$this->functionData['tabSingleColumn'][$count_d4f78226c7a03be55b61a1bf06cf555d]['row'] = $row;
$this->functionData['tabSingleColumn'][$count_d4f78226c7a03be55b61a1bf06cf555d]['read_more_link'] = $read_more_link;
$this->functionData['tabSingleColumn'][$count_d4f78226c7a03be55b61a1bf06cf555d]['url'] = $url;
$this->functionData['tabSingleColumn'][$count_d4f78226c7a03be55b61a1bf06cf555d]['title'] = $title;
}
$IPBHTML .= "<post>
<title><![CDATA[" . IPSText::truncate( $title, 90 ) . "]]></title>
<url><![CDATA[{$url}]]></url>
<text><![CDATA[{$row['post']}]]></text>
<date>" . IPSText::htmlspecialchars($this->registry->getClass('class_localization')->getDate($row['_raw_date'],"long", 0)) . "</date>
</post>";
return $IPBHTML;
}

/* -- tabStatusUpdates --*/
function tabStatusUpdates($updates=array(), $actions, $member=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_profile', $this->_funcHooks['tabStatusUpdates'] ) )
{
$count_37a23f369ca3a872a199ff03ee085e15 = is_array($this->functionData['tabStatusUpdates']) ? count($this->functionData['tabStatusUpdates']) : 0;
$this->functionData['tabStatusUpdates'][$count_37a23f369ca3a872a199ff03ee085e15]['updates'] = $updates;
$this->functionData['tabStatusUpdates'][$count_37a23f369ca3a872a199ff03ee085e15]['actions'] = $actions;
$this->functionData['tabStatusUpdates'][$count_37a23f369ca3a872a199ff03ee085e15]['member'] = $member;
}
$IPBHTML .= "" . (($this->memberData['member_id'] AND ( $this->memberData['member_id'] == $member['member_id'] ) AND $this->registry->getClass('memberStatus')->canCreate( $member )) ? ("
<newStatusURL><![CDATA[{$this->settings['base_url']}app=members&amp;module=profile&amp;section=status&amp;do=new&amp;k={$this->member->form_hash}&amp;id={$this->memberData['member_id']}&amp;forMemberId={$member['member_id']}]]>
</newStatusURL>
") : ("")) . "
" . (($this->memberData['member_id'] && $this->memberData['member_id'] != $member['member_id'] && $member['pp_setting_count_comments']) ? ("
<profileCommentURL>
<![CDATA[{$this->settings['base_url']}app=members&amp;module=profile&amp;section=status&amp;do=new&amp;k={$this->member->form_hash}&amp;id={$this->memberData['member_id']}&amp;forMemberId={$member['member_id']}]]>
</profileCommentURL>
") : ("")) . "

" . ((count( $updates )) ? ("
	" . ( method_exists( $this->registry->getClass('output')->getTemplate('profile'), 'statusUpdates' ) ? $this->registry->getClass('output')->getTemplate('profile')->statusUpdates($updates) : '' ) . "
") : ("
<commentReplies>
	<commentReply>
		<reply><![CDATA[{$this->lang->words['status_updates_none']}]]></reply>
	</commentReply>
</commentReplies>
")) . "";
return $IPBHTML;
}

/* -- tabTopics --*/
function tabTopics($content) {
$IPBHTML = "";
$IPBHTML .= "<posts>
		{$content}
<posts>";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>