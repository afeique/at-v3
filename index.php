<?php

require 'classes/JavaScriptSequence.php';

require 'vendor/autoload.php';
$klein = new \Klein\Klein();

$klein->respond(function($request, $response, $page) {
    $page->title = 'untitled';
    $page->title_separator = ' @ ';
    $page->title_suffix = 'acrossti.me';
    $page->content = '';
    $page->view = 'main';
    $page->use_minified_resources = True;
    $page->js_sequence = new \Acrosstime\JavaScriptSequence(array(array('jquery', 'bootstrap'), array('analytics') ) );
});

$klein->respond('/', function($request, $response, $page) {
    $page->title = '';
    $page->title_separator = '';

    ob_start();
    require 'pages/frontpage.php';
    $page->content = ob_get_clean();
    $page->render('views/'. $page->view .'.php');
});

$klein->dispatch();