<?php

namespace Acrosstime;

class JavaScriptSequence {
    protected $scripts;

    public function __construct($js = Null) {
        if (isset($js) )
            $this->scripts = $js;
        else
            $this->scripts = array();
    }

    /**
     * Adds a script to the squence. Can insert the script at a specific point.
     * $index_script is the script relative which to insert, $in
     * 
     * @param string $script Name of the script to add to the sequence
     * @param string $index_script Name of the script in the sequence to insert relative to
     * @param mixed $insert_after Whether to insert before or after the $index_script
     *
     * @return bool Returns True on successful insert, False on failure.
     */
    public function add($script, $index_script = Null, $insert_after = False) {
        if (!isset($index_script) ) {
            $this->scripts[] = $script;
        } else {
            $i = array_search($index_script, $this->scripts);
            if ($i === False)
                return False;

            if ($insert_after) {
                if (!isset($this->scripts[$i+1]) ) {
                    $this->scripts[] = $script;
                    return True;
                }
                
                $a = array_slice($this->scripts, 0, $i+1);
                $b = array_slice($this->scripts, $i+1);
            } else {
                $a = array_slice($this->scripts, 0, $i);
                $b = array_slice($this->scripts, $i);
            }

            $a[] = $script;
            $this->scripts = array_merge($a, $b);
        }

        return True;
    }

    public function toArray() {
        return $this->scripts;
    }
}