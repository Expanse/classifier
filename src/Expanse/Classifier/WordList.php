<?php

namespace Expanse\Classifier;

class WordList {
	public function __construct() {
		$this->location_table = array();
	}
    
    # Adds a word (if it is new) and assigns it a unique dimension.
	public function add_word($word) {
		$term = $word;
		if (! array_key_exists($term, $this->location_table)) {
			$this->location_table[$term] = count($this->location_table);
		}
	}
    
	public function size() {
		return count($this->location_table);
	}
	
	public function lookup($term) {
		if (isset($this->location_table[$term])) {
			// TODO: This finds "text" in position 0, when the ruby code finds it in position 1
			return $this->location_table[$term];
		}
		return false;
	}
    # Returns the dimension of the word or nil if the word is not in the space.
    // def [](lookup)
    //   term = lookup
    //   @location_table[term]
    // end
    // 
    // def word_for_index(ind)
    //   @location_table.invert[ind]
    // end
    
}
