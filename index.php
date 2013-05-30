<?php

/**
 * Load fat-free framework (f3).
 * https://github.com/bcosca/fatfree
 */
$f3 = require 'lib/base.php';

/*
 * Contains site-wide settings set to f3 global variables.
 * Because variables are set using f3, fat-free must be loaded before the config.
 */
require 'config.php';

/**
 * Page class is an object that abstracts a page.
 * Tied inherently to the f3 global variables containing page data (see below).
 * Contains services methods used to manipulate page data stored in f3 globals.
 * E.g. page title, page content, page scripts, page stylesheets.
 */
require 'classes/Page.php';

/**
 * Create a new page.
 */
$page = new Page($f3);

/**
 * We could alternatively have added the JavaScripts to the page by writing:
 *
 * $index = $page->add_js('jquery.min');    // create a new sequence containing jquery (seq. index = 0)
 * $page->add_js('bootstrap.min', $index);  // add bootstrap to jquery sequence (seq. index = 0)
 * $page->add_js('analytics');              // create a new sequence containing google analytics (seq. index = 1)
 */

$f3->route('GET /', function($f3) use ($page) {
    ob_start();
    require 'pages/frontpage.php';
    $page->content = ob_get_clean();

    $view = new View;

    echo $view->render($f3->get('view_path') . $f3->get('view') . $f3->get('view_ext') );
});

$f3->run();