<?php

require 'classes/JavaScriptCollection.php';

require 'vendor/autoload.php';
$klein = new \Klein\Klein();
Propel::init("db/build/conf/acrosstime-conf.php");

$klein->respond(function($request, $response, $page) {
    $page->title = 'untitled';
    $page->title_separator = ' @ ';
    $page->title_suffix = 'acrossti.me';
    $page->content = '';
    $page->template = 'main';
    $page->use_minified_resources = False;
    $page->js = new \Acrosstime\JavaScriptCollection(array(array('jquery', 'bootstrap'), array('analytics') ) );
    $page->show_navbar_login = True;
});

$klein->respond('/', function($request, $response, $page) {
    $page->title = '';
    $page->title_separator = '';
    $page->js->add_sequence('frontpage-clock');
    $page->show_navbar_login = False;

    ob_start();
    require 'pages/frontpage.php';
    $page->content = ob_get_clean();
    $page->render('templates/'. $page->template .'.php');
});

$klein->dispatch();