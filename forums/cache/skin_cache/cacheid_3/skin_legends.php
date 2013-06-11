<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 3               */
/* CACHE FILE: Generated: Mon, 10 Jun 2013 14:58:21 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_legends_3 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['bbcodePopUpList'] = array('bbcode','bbcode');
$this->_funcHooks['emoticonPopUpList'] = array('emoticons','emoticons');


}

/* -- bbcodePopUpList --*/
function bbcodePopUpList($rows) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_legends', $this->_funcHooks['bbcodePopUpList'] ) )
{
$count_9fd2f8156503e1eb5299febc6e51c131 = is_array($this->functionData['bbcodePopUpList']) ? count($this->functionData['bbcodePopUpList']) : 0;
$this->functionData['bbcodePopUpList'][$count_9fd2f8156503e1eb5299febc6e51c131]['rows'] = $rows;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- emoticonPopUpList --*/
function emoticonPopUpList($editor_id, $rows, $legacy_editor=false) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_legends', $this->_funcHooks['emoticonPopUpList'] ) )
{
$count_6bfcad7820d225ce8f3dce3a1ddebe43 = is_array($this->functionData['emoticonPopUpList']) ? count($this->functionData['emoticonPopUpList']) : 0;
$this->functionData['emoticonPopUpList'][$count_6bfcad7820d225ce8f3dce3a1ddebe43]['editor_id'] = $editor_id;
$this->functionData['emoticonPopUpList'][$count_6bfcad7820d225ce8f3dce3a1ddebe43]['rows'] = $rows;
$this->functionData['emoticonPopUpList'][$count_6bfcad7820d225ce8f3dce3a1ddebe43]['legacy_editor'] = $legacy_editor;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- wrap_tag --*/
function wrap_tag($tag="") {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>