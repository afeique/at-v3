<?php

require 'classes/JavaScriptCollection.php';

require 'vendor/autoload.php';
$klein = new \Klein\Klein();

$klein->respond(function($request, $response, $page) {
    $page->title = 'untitled';
    $page->title_separator = ' @ ';
    $page->title_suffix = 'acrossti.me';
    $page->content = '';
    $page->template = 'main';
    $page->use_minified_resources = False;
    $page->js = new \Acrosstime\JavaScriptCollection(
        array(
            array(
                'angular',
                'jquery', 'jquery-ui', 'bootstrap', 
                // load-image plugin -- for preview images and image resizing functionality
                'load-image',
                // canvas to blob plugin -- for preview images
                'canvas-to-blob',
                // jquery iframe transport plugin -- required for browsers lacking XHR support
                'jquery.iframe-transport',
                // jquery file upload
                'jquery.fileupload', 
                'jquery.fileupload-process', 
                'jquery.fileupload-image',
                'jquery.fileupload-audio',
                'jquery.fileupload-video',
                'jquery.fileupload-validate',
                'jquery.fileupload-angular',
                ), 
            array('analytics') 
            ) 
        );
});

$klein->respond('/', function($request, $response, $page) {
    $page->title = '';
    $page->title_separator = '';

    ob_start();
    require 'pages/frontpage.php';
    $page->content = ob_get_clean();

    $page->render('templates/'. $page->template .'.php');
});

$klein->respond('/[:page]?', function($request, $response, $page) {
    $request->page = str_replace('-', '_', $request->param('page') );
    if (!file_exists('pages/'. $request->page .'.php') )
        $request->page = '404';

    ob_start();
    require 'pages/'. $request->page .'.php';
    $page->content = ob_get_clean();

    $page->render('templates/'. $page->template .'.php');
});

$klein->dispatch();