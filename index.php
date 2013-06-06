<?php

require 'classes/JavaScriptCollection.php';

require 'vendor/autoload.php';
$klein = new \Klein\Klein();

$klein->respond(function($request, $response, $page) {
    $page->title = 'untitled';
    $page->title_separator = ' @ ';
    $page->title_suffix = 'acrossti.me';
    $page->content = '';
    $page->view = 'main';
    $page->use_minified_resources = False;
    $page->js = new \Acrosstime\JavaScriptCollection(array(array('jquery', 'bootstrap'), array('analytics') ) );

});

$klein->respond('/', function($request, $response, $page) {
    $page->title = '';
    $page->title_separator = '';
    $page->js->add_sequence('frontpage-clock');

    ob_start();
    require 'pages/frontpage.php';
    $page->content = ob_get_clean();
    $page->render('views/'. $page->view .'.php');
});

$klein->dispatch();