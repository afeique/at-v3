<?php
/*--------------------------------------------------*/
/* FILE GENERATED BY INVISION POWER BOARD 3         */
/* CACHE FILE: Skin set id: 3               */
/* CACHE FILE: Generated: Mon, 10 Jun 2013 14:58:21 GMT */
/* DO NOT EDIT DIRECTLY - THE CHANGES WILL NOT BE   */
/* WRITTEN TO THE DATABASE AUTOMATICALLY            */
/*--------------------------------------------------*/

class skin_register_3 extends skinMaster{

/**
* Construct
*/
function __construct( ipsRegistry $registry )
{
	parent::__construct( $registry );
	

$this->_funcHooks = array();
$this->_funcHooks['completePartialLogin'] = array('reqCfieldDescSpan','custom_required','optCfieldDescSpan','custom_optional','hasAName','partialLoginErrors','fbDNInner','fbDisplayName','partialAllowDnames','partialNoEmail','reqCfields','optCfields','partialCustomFields','reqCfieldDesc','reqCfieldDescSpan','custom_required','optCfieldDesc','optCfieldDescSpan','custom_optional','partialLoginErrors','partialAllowDnames','partialNoEmail','reqCfields','optCfields','partialCustomFields');
$this->_funcHooks['lostPasswordForm'] = array('lostPasswordErrors','lostPasswordErrors');
$this->_funcHooks['registerCoppaForm'] = array('coppaConsentExtra','coppaConsentExtra');
$this->_funcHooks['registerCoppaStart'] = array('coppaMRange','coppaDRange','coppaYRange','useCoppa');
$this->_funcHooks['registerForm'] = array('general_errors','statesJs','isCountrySelect','isCountryWords','options','states','statesCountries','isAddressOrPhone','isAddress2','isAddress1','textRequired','textErrorMessage','isText','dropdownRequired','isCountry','dropdownErrorMessage','isDropdown','specialRequired','specialErrorMessage','isSpecial','fields','reqCfieldDescSpan','custom_required','optCfieldDescSpan','custom_optional','registerHasErrors','registerUsingFb','twitterBox','registerServices','registerHasInlineErrors','ieDnameClass','ieDname','ieEmailClass','ieEmail','iePasswordClass','iePassword','hasNexusFields','reqCfields','optCfields','hasCfields','defaultAAE','checkedTOS','ieDnameClass','ieTOS','privvy','general_errors','reqCfieldDescSpan','custom_required','optCfieldDescSpan','custom_optional','registerHasErrors','ieDname','ieDname','ieEmail','ieEmail','iePassword','iePassword','reqCfields','optCfields','hasCfields','defaultAAE','useCoppa');
$this->_funcHooks['showLostpassForm'] = array('lostpassFormErrors','lpFormMethodChoose','lostpassFormErrors','lpFormMethodChoose');
$this->_funcHooks['showRevalidateForm'] = array('revalidateError','revalidateError');


}

/* -- completePartialLogin --*/
function completePartialLogin($mid="",$key="",$custom_fields="",$errors="", $reg="", $userFromService=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_register', $this->_funcHooks['completePartialLogin'] ) )
{
$count_a0d1a50194ec2489f790b7b43314714f = is_array($this->functionData['completePartialLogin']) ? count($this->functionData['completePartialLogin']) : 0;
$this->functionData['completePartialLogin'][$count_a0d1a50194ec2489f790b7b43314714f]['mid'] = $mid;
$this->functionData['completePartialLogin'][$count_a0d1a50194ec2489f790b7b43314714f]['key'] = $key;
$this->functionData['completePartialLogin'][$count_a0d1a50194ec2489f790b7b43314714f]['custom_fields'] = $custom_fields;
$this->functionData['completePartialLogin'][$count_a0d1a50194ec2489f790b7b43314714f]['errors'] = $errors;
$this->functionData['completePartialLogin'][$count_a0d1a50194ec2489f790b7b43314714f]['reg'] = $reg;
$this->functionData['completePartialLogin'][$count_a0d1a50194ec2489f790b7b43314714f]['userFromService'] = $userFromService;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- lostPasswordForm --*/
function lostPasswordForm($errors="") {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_register', $this->_funcHooks['lostPasswordForm'] ) )
{
$count_a9fa050e6a41037fb463807d30477414 = is_array($this->functionData['lostPasswordForm']) ? count($this->functionData['lostPasswordForm']) : 0;
$this->functionData['lostPasswordForm'][$count_a9fa050e6a41037fb463807d30477414]['errors'] = $errors;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- lostPasswordWait --*/
function lostPasswordWait($member="") {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- registerCoppaForm --*/
function registerCoppaForm() {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_register', $this->_funcHooks['registerCoppaForm'] ) )
{
$count_d15ab287254e3b9113889b406e88a0f5 = is_array($this->functionData['registerCoppaForm']) ? count($this->functionData['registerCoppaForm']) : 0;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- registerCoppaStart --*/
function registerCoppaStart() {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_register', $this->_funcHooks['registerCoppaStart'] ) )
{
$count_f48c0fb77e21b73e1ed6b31007d843c5 = is_array($this->functionData['registerCoppaStart']) ? count($this->functionData['registerCoppaStart']) : 0;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- registerCoppaTwo --*/
function registerCoppaTwo() {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- registerForm --*/
function registerForm($general_errors=array(), $data=array(), $inline_errors=array(), $time_select=array(), $custom_fields=array(), $nexusFields=array(), $nexusStates=array()) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_register', $this->_funcHooks['registerForm'] ) )
{
$count_904edfd97375cb0b2670c988ca69dbbb = is_array($this->functionData['registerForm']) ? count($this->functionData['registerForm']) : 0;
$this->functionData['registerForm'][$count_904edfd97375cb0b2670c988ca69dbbb]['general_errors'] = $general_errors;
$this->functionData['registerForm'][$count_904edfd97375cb0b2670c988ca69dbbb]['data'] = $data;
$this->functionData['registerForm'][$count_904edfd97375cb0b2670c988ca69dbbb]['inline_errors'] = $inline_errors;
$this->functionData['registerForm'][$count_904edfd97375cb0b2670c988ca69dbbb]['time_select'] = $time_select;
$this->functionData['registerForm'][$count_904edfd97375cb0b2670c988ca69dbbb]['custom_fields'] = $custom_fields;
$this->functionData['registerForm'][$count_904edfd97375cb0b2670c988ca69dbbb]['nexusFields'] = $nexusFields;
$this->functionData['registerForm'][$count_904edfd97375cb0b2670c988ca69dbbb]['nexusStates'] = $nexusStates;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- registerStepBar --*/
function registerStepBar($step) {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- show_lostpass_form_auto --*/
function show_lostpass_form_auto($aid="",$uid="") {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- show_lostpass_form_manual --*/
function show_lostpass_form_manual() {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- showAuthorize --*/
function showAuthorize($member="") {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- showLostpassForm --*/
function showLostpassForm($error) {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_register', $this->_funcHooks['showLostpassForm'] ) )
{
$count_0931481fc92c9ea6e93d44f1b02c88c8 = is_array($this->functionData['showLostpassForm']) ? count($this->functionData['showLostpassForm']) : 0;
$this->functionData['showLostpassForm'][$count_0931481fc92c9ea6e93d44f1b02c88c8]['error'] = $error;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- showLostPassWaitRandom --*/
function showLostPassWaitRandom($member="") {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- showManualForm --*/
function showManualForm($type="reg") {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- showPreview --*/
function showPreview($member="") {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- showRevalidated --*/
function showRevalidated() {
$IPBHTML = "";
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}

/* -- showRevalidateForm --*/
function showRevalidateForm($name="",$error="") {
$IPBHTML = "";
if( IPSLib::locationHasHooks( 'skin_register', $this->_funcHooks['showRevalidateForm'] ) )
{
$count_9a1041cd395faeab836ffef66bd2f7cc = is_array($this->functionData['showRevalidateForm']) ? count($this->functionData['showRevalidateForm']) : 0;
$this->functionData['showRevalidateForm'][$count_9a1041cd395faeab836ffef66bd2f7cc]['name'] = $name;
$this->functionData['showRevalidateForm'][$count_9a1041cd395faeab836ffef66bd2f7cc]['error'] = $error;
}
$IPBHTML .= "<!-- NoData -->";
return $IPBHTML;
}


}


/*--------------------------------------------------*/
/* END OF FILE                                      */
/*--------------------------------------------------*/

?>