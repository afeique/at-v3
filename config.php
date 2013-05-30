<?php

/**
 * Because this sets config variables to f3 globals, f3 needs to be included first.
 */

/**
 * F3's debug level. Needs to be modified when pushing to production.
 */
$f3->set('DEBUG', 3);

/**
 * Various page-specific and configuration-related variables set as f3 globals.
 * Self-explanatory for the most part, but commented nevertheless.
 */

/**
 * Page title. 
 * This should be set on a per-page basis.
 */
$f3->set('title', 'untitled');

/**
 * Page title suffix tagged on after the page title.
 * On most pages, it will be set here and not touched again.
 */
$f3->set('title_suffix', 'acrossti.me');

/**
 * Separator between page title and page title suffix. 
 * Only used if page title evaluates to non-False.
 */
$f3->set('title_separator', ' @ ');

/**
 * The default page content.
 */
$f3->set('content', '');

/**
 * The site's base url. This is the homepage.
 * Needs to be modified between environments (dev, build, production).
 */
$base_url = 'http://dev.acrossti.me/';
$f3->set('base_url', $base_url);

/**
 * Base url relative to which css is stored.
 */
$f3->set('css_base_url', '/css/');

/**
 * Base url relative to which js is stored.
 */
$f3->set('js_base_url', '/js/');

/**
 * The default view to use.
 */
$f3->set('view', 'main');

/**
 * The extension that views use.
 * This is set as a variable here so the convention can be easily changed.
 * Past development practices include: .tpl, .tpl.php, .phtml
 */
$f3->set('view_ext', '.php');

/**
 * Path relative to the web root where views are stored.
 * Note that it is not an absolute as this has caused permissions issues
 * in the development environment.
 * It may have to be modified to be an absolute path when pushed to other environments.
 */
$f3->set('view_path', 'views/');

/**
 * Indicates to use minified js and css resources if they are available.
 * Ideally set to 'False' during key development phases, then tested with 'True'
 * on development environment.
 * Should always be set to 'True' on staging and production environments.
 */
$f3->set('use_minified_resources', True);

/**
 * The $js scripts array is a 2D array.
 * The first index/dimension represents different sequences of scripts.
 * Each sequence of scripts is mutually exclusive and can be loaded AND executed in parallel.
 * The second index/dimension represents individual scripts in each sequence.
 * Individual scripts can be loaded in parallel but must be executed in the order of the sequence.
 *
 * The default setup has two sequences:
 * The first sequence loads jquery and bootstrap and executes them in that order.
 * The second sequence only loads google analytics.
*
 * Using head.js, all the scripts are loaded in parallel,
 * the two sequences are executed in parallel since they have no interdependencies,
 * but within the first sequence, jquery and bootstrap are executed in that specific order
 * (bootstrap depends on jquery).
 */
$f3->set('js', array( array('jquery', 'bootstrap'), array('analytics') ) );

/**
 * We could alternatively have added the JavaScripts to the page by writing:
 *
 * $index = $page->add_js('jquery.min');    // create a new sequence containing jquery (seq. index = 0)
 * $page->add_js('bootstrap.min', $index);  // add bootstrap to jquery sequence (seq. index = 0)
 * $page->add_js('analytics');              // create a new sequence containing google analytics (seq. index = 1)
 */