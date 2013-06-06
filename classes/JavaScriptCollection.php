<?php

namespace Acrosstime;

require 'JavaScriptSequence.php';

class JavaScriptCollection {
    protected $collection;

    public function __construct($collection = Null) {
        $this->collection = array();

        if (isset($collection) ) {
            if (is_array($collection) ) {    
                foreach ($collection as $sequence_array) {
                    $this->add_sequence($sequence_array);
                }
            } else if (is_string($collection) ) {
                $script = $collection;
                $this->add_sequence($script);
            }
        }
    }

    public function add_sequence($sequence) {
        if ($sequence instanceof JavaScriptSequence) {
            $this->collection[] = $sequence;
        } else {
            $this->collection[] = new JavaScriptSequence;
            $i = sizeof($this->collection)-1;

            if (is_array($sequence) ) {
                $this->collection[$i]->add_scripts($sequence);
            } else if (is_string($sequence) ) {
                $script = $sequence;
                $this->collection[$i]->add_script($script);
            }
        }

        return sizeof($this->collection)-1;
    }

    public function add_scripts($sequence_index, array $scripts, $relative_to = Null, $insert_after = False) {
        return $this->collection[$sequence_index]->add_scripts($scripts, $relative_to, $insert_after);
    }

    public function add_script($sequence_index, $script, $relative_to = Null, $insert_after = False) {
        return $this->collection[$sequence_index]->add_script($script, $relative_to, $insert_after);
    }

    public function to_array() {
        $array = array();
        foreach ($this->collection as $sequence) {
            $array[] = $sequence->to_array();
        }

        return $array;
    }
}