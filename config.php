<?php

// DIRECTORY SEPARATOR
define('WS', DIRECTORY_SEPARATOR);

define('ROOT_DIR', __DIR__ . WS );
define('LIB_DIR', ROOT_DIR . 'lib' . WS);
define('VIEW_DIR', ROOT_DIR .'views' . WS);
define('LAYOUT_DIR', VIEW_DIR .'layouts'. WS);
define('PUBLIC_DIR', ROOT_DIR . 'public' . WS);
define('FORUM_DIR', PUBLIC_DIR .'forums' . WS);

define('VIEW_EXT', '.phtml');

// Trailing slash will be prepended to PATH_URL, representing the root

// This is done so that in case the site is hosted in a subdirectory, Klein will still work
// e.g. http://acrossti.me will work as well as http://acrossti.me/beta

// We'll prepend all of our Klein directives with PATH_URL + 'timeline/hola'
// Note the lack of a forward slash in the string following PATH_URL

define('ROOT_URL', "http://$_SERVER[HTTP_HOST]");

// Figure out the path URL. This is some nasty code, sorry
// URIs & URLs always use forward slash AFAIK
$url = explode('/', $_SERVER['REQUEST_URI']);
$dir = explode(WS, __DIR__);
$dir = array_slice( $dir, -1 ); // slice off /public/

end($dir);
$last = key($dir);

$new = array();
foreach ($url as &$value) {
	if( !empty($value) )
	array_push( $new, $value );
	
	if( $value === $dir[$last] )
	break;
}
unset( $last );

$url = implode('/', $new);
unset( $new );

// IMPORTANT! Note that PATH_URL has a forward slash
// We might have to configure our .htaccess to auto-append a forward slash
// I did this mainly b/c I'm used to the IU Art Museum server auto-appending it
define('PATH_URL', '/' . $url . '/' );
unset( $url );