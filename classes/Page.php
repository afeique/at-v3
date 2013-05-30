<?php

class Page {
    protected $f3;
    protected $js;
    protected $view;
    protected $title;
    protected $content;

    private static $instance;

    public function __construct($f3) {
        if (isset(self::$instance) )
            return self::$instance;

        $this->f3 = $f3;

        $this->js = $this->f3->get('js');
        $this->title = $this->f3->get('title');
        $this->content = $this->f3->get('content');
        $this->template = $this->f3->get('template');
    }

    public function __set($var, $val) {
        switch ($var) {
            case 'view':
                $view = dirname(dirname(__FILE__)) . $val . $this->f3->get('template_ext');
                if (!file_exists($view) )
                    break;
            case 'title':
            case 'content':
                $this->$var = $val;
                $this->f3->set($var, $val);
                break;
        }
    }

    /**
     * Add a script to the end of a particular sequence or create a new sequence containing a script.
     */
    public function add_js($script, $i = Null) {
        if ( !isset($i) ) {
            $this->js[] = array($script);
            $i = sizeof($this->js) - 1;
        } else {
            $this->js[$i][] = $script;
        }

        $f3->set('js', $this->js);

        return $i;
    }
}

?>