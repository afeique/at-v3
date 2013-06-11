<?php

$PRE = trim(ipsRegistry::dbFunctions()->getPrefix());

$SQL[] = "DELETE FROM core_sys_lang_words WHERE word_pack='public_cookies';";
$SQL[] = "DELETE FROM core_sys_lang_words WHERE word_app='core' AND word_pack='public_cookie_view';";
$SQL[] = "DELETE FROM core_sys_lang_words WHERE word_app='forums' AND word_pack='public_cookies';";
$SQL[] = "DELETE FROM core_sys_lang_words WHERE word_app='forums' AND word_pack='public_printpage';";

$SQL[] = "ALTER TABLE seo_meta CHANGE url url TEXT NULL";

$SQL[] = "UPDATE bbcode_mediatag SET mediatag_replace='<iframe id=\"ytplayer\" class=\"EmbeddedVideo\" type=\"text/html\" width=\"640\" height=\"390\" src=\"http://youtube.com/embed/$3?html5=1&fs=1\" frameborder=\"0\" allowfullscreen webkitallowfullscreen /></iframe>' WHERE mediatag_name='YouTube';";
$SQL[] = "UPDATE bbcode_mediatag SET mediatag_replace='<iframe id=\"ytplayer\" class=\"EmbeddedVideo\" type=\"text/html\" width=\"640\" height=\"390\" src=\"http://youtube.com/embed/$2?html5=1&fs=1\" frameborder=\"0\" allowfullscreen webkitallowfullscreen /></iframe>' WHERE mediatag_name='YouTu.be';";
$SQL[] = "UPDATE bbcode_mediatag SET mediatag_replace='<iframe src=\"http$1://player.vimeo.com/video/$2\" class=\"EmbeddedVideo\" width=\"400\" height=\"250\" frameborder=\"0\"></iframe>' WHERE mediatag_name='Vimeo';";

# Ensure that members who can use HTML have HTML enabled when viewing and editing signatures
$SQL[] = "UPDATE members SET members_bitoptions=members_bitoptions | 16384 WHERE member_group_id IN( SELECT g_id FROM `{$PRE}groups` WHERE g_dohtml=1 )";

//