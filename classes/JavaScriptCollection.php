<?php

namespace Acrosstime;

require 'JavaScriptSequence.php';

class JavaScriptCollection {
    protected $collection;

    public function __construct(array $collection = Null) {
        $this->colection = array();

        if (isset($collection) ) {
            foreach ($collection as $sequence_array) {
                $this->collection[] = new JavaScriptSequence;
                $i = sizeof($this->collection)-1;

                foreach ($sequence_array as $script) {
                    $this->collection[$i]->add_script($script);
                }
            }
        }
    }

    public function add_sequence(JavaScriptSequence $sequence) {
        $this->collection[] = $sequence;

        return sizeof($this->collection)-1;
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