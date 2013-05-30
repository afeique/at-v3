<?php

class Template {
    private static $instance;

    protected $useTitlePrefix;
    protected $title;

    private function __create() {
        $this->useTitlePrefix = True;
        $this->title = '&lt;No Title&gt;';
    }

    public function instance() {
        if ( !isset(self::$instance) ) {
            self::$instance = new Template;
        }

        return self::$instance;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTitle($title) {
        return $this->title;
    }

    public function disableTitlePrefix() {
        $this->useTitlePrefix = False;
    }
}