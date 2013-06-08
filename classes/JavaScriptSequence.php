<?php

namespace Acrosstime;

class JavaScriptSequence {
    protected $sequence;

    public function __construct(array $sequence = Null) {
        if (isset($sequence) )
            $this->sequence = $sequence;
        else
            $this->sequence = array();
    }

    /**
     * Adds a script to the squence. Can insert the script at a specific point.
     * $relative_to is the script relative which to insert.
     * 
     * @param string $script Name of the script to add to the sequence
     * @param string $relative_to Name of the script in the sequence to insert relative to
     * @param mixed $insert_after Whether to insert before or after the $relative_to
     *
     * @return bool Returns True on successful insert, False on failure.
     */
    public function add_script($script, $relative_to = Null, $insert_after = False) {
        if (!isset($relative_to) ) {
            $this->sequence[] = $script;
        } else {
            $i = array_search($relative_to, $this->sequence);
            if ($i === False)
                return False;

            if ($insert_after) {
                if (!isset($this->sequence[$i+1]) ) {
                    $this->sequence[] = $script;
                    return True;
                }
                
                $a = array_slice($this->sequence, 0, $i+1);
                $b = array_slice($this->sequence, $i+1);
            } else {
                $a = array_slice($this->sequence, 0, $i);
                $b = array_slice($this->sequence, $i);
            }

            $a[] = $script;
            $this->sequence = array_merge($a, $b);
        }

        return True;
    }

    public function add_scripts(array $scripts, $relative_to = Null, $insert_after = False) {
        if (isset($relative_to) )
            $scripts = array_reverse($scripts);
        foreach ($scripts as $script) {
            $this->add_script($script, $relative_to, $insert_after);
        }
    }

    public function to_array() {
        return $this->sequence;
    }
}