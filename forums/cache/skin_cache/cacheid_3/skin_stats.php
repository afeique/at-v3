<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 3               */
/* CACHE FILE: Generated: Mon, 10 Jun 2013 14:58:22 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_stats_3 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['group_strip'] = array('forums','forums','moreThanOne','noVisibleForums','specificForums','isonline','isFriend','isFriendable','canPm','members','hasPaginationTop','hasLeaders','hasPaginationBottom','forums','forums','moreThanOne','noVisibleForums','specificForums','isonline','isFriend','isFriendable','canPm','members','hasPaginationTop','hasLeaders','hasPaginationBottom');
$this->_funcHooks['top_posters'] = array('tpIsFriend','tpIsFrindable','tpPm','tpBlog','tpGallery','topposters','hasTopPosters','tpIsFriend','tpIsFrindable','tpPm','tpBlog','tpGallery','topposters','hasTopPosters');
$this->_funcHooks['whoPosted'] = array('whoposted','hasPosters','whoposted','hasPosters');


}

/* -- group_strip --*/
function group_strip($group="", $members=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_stats', $this->_funcHooks['group_strip'] ) )
{
$count_df37fd38b3e0ed7629ca29d72bebc5a6 = is_array($this->functionData['group_strip']) ? count($this->functionData['group_strip']) : 0;
$this->functionData['group_strip'][$count_df37fd38b3e0ed7629ca29d72bebc5a6]['group'] = $group;
$this->functionData['group_strip'][$count_df37fd38b3e0ed7629ca29d72bebc5a6]['members'] = $members;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- top_posters --*/
function top_posters($rows) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_stats', $this->_funcHooks['top_posters'] ) )
{
$count_e924596d167be7c09bf29808ac0a7ea1 = is_array($this->functionData['top_posters']) ? count($this->functionData['top_posters']) : 0;
$this->functionData['top_posters'][$count_e924596d167be7c09bf29808ac0a7ea1]['rows'] = $rows;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- whoPosted --*/
function whoPosted($tid=0, $title="", $rows=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_stats', $this->_funcHooks['whoPosted'] ) )
{
$count_c6193be093aa77bbb2060a4bd08dacfa = is_array($this->functionData['whoPosted']) ? count($this->functionData['whoPosted']) : 0;
$this->functionData['whoPosted'][$count_c6193be093aa77bbb2060a4bd08dacfa]['tid'] = $tid;
$this->functionData['whoPosted'][$count_c6193be093aa77bbb2060a4bd08dacfa]['title'] = $title;
$this->functionData['whoPosted'][$count_c6193be093aa77bbb2060a4bd08dacfa]['rows'] = $rows;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>