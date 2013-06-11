<?php

$PRE = trim(ipsRegistry::dbFunctions()->getPrefix());

$SQL[] = "UPDATE custom_bbcode SET bbcode_replace='<span class=\'bbc_hr\'>&nbsp;</span>' where bbcode_tag='hr';";
//