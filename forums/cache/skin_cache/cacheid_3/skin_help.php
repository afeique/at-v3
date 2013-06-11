<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 3               */
/* CACHE FILE: Generated: Tue, 11 Jun 2013 11:12:00 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_help_3 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['helpShowSection'] = array('notajax','isajax','notajax','notajax','isajax','notajax');
$this->_funcHooks['helpShowTopics'] = array('helpfiles','helpfiles');


}

/* -- helpShowSection --*/
function helpShowSection($one_text="",$two_text="",$three_text="", $text) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_help', $this->_funcHooks['helpShowSection'] ) )
{
$count_aba9b1cc84fac3c504d31f50ad2a7f70 = is_array($this->functionData['helpShowSection']) ? count($this->functionData['helpShowSection']) : 0;
$this->functionData['helpShowSection'][$count_aba9b1cc84fac3c504d31f50ad2a7f70]['one_text'] = $one_text;
$this->functionData['helpShowSection'][$count_aba9b1cc84fac3c504d31f50ad2a7f70]['two_text'] = $two_text;
$this->functionData['helpShowSection'][$count_aba9b1cc84fac3c504d31f50ad2a7f70]['three_text'] = $three_text;
$this->functionData['helpShowSection'][$count_aba9b1cc84fac3c504d31f50ad2a7f70]['text'] = $text;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- helpShowTopics --*/
function helpShowTopics($one_text="",$two_text="",$three_text="",$rows) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_help', $this->_funcHooks['helpShowTopics'] ) )
{
$count_670c3695679d433af8a233329f602c5a = is_array($this->functionData['helpShowTopics']) ? count($this->functionData['helpShowTopics']) : 0;
$this->functionData['helpShowTopics'][$count_670c3695679d433af8a233329f602c5a]['one_text'] = $one_text;
$this->functionData['helpShowTopics'][$count_670c3695679d433af8a233329f602c5a]['two_text'] = $two_text;
$this->functionData['helpShowTopics'][$count_670c3695679d433af8a233329f602c5a]['three_text'] = $three_text;
$this->functionData['helpShowTopics'][$count_670c3695679d433af8a233329f602c5a]['rows'] = $rows;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>