<?php

namespace Expanse\Classifier;

class ContentNode {
	public $raw_vector;
	public $raw_norm;
	public $lsi_vector;
	public $lsi_norm;
	public $categories;
	public $word_hash;

	# If text_proc is not specified, the source will be duck-typed
	# via source.to_s
	public function __construct($word_hash, array $categories = array()) {
		$this->categories = $categories;
		$this->word_hash = $word_hash;
	}

	public function search_vector() {
		if (! is_null($this->lsi_vector)) return $this->lsi_vector->toArray();
		return $this->raw_vector->toArray();
	}

	public function search_norm() {
		if (! is_null($this->lsi_norm)) return $this->lsi_norm->toArray();
		return $this->raw_norm->toArray();
	}

	public function raw_vector_with($word_list) {
		$vector = new Vector($word_list);

		foreach ($this->word_hash as $word => $idx) {
			if ($word_list->lookup($word) !== false) {
				$vector[$word_list->lookup($word)] = $this->word_hash[$word];
			}
		}

		# Perform the scaling transform
		$total_words = $vector->sum();

		# Perform first-order association transform if this vector has more
		# than one word in it. 
		if ($total_words > 1.0) {
			$weighted_total = 0.0;
			while ($vector->valid()) {
				$term = $vector->current();
				if ($term > 0) {
					$weighted_total += (( $term / $total_words ) * log( $term / $total_words ));
				}
				$vector->next();
			}
			$vector->collect(function($val) use ($weighted_total) {
				return log( $val + 1 ) / -$weighted_total;
			});
		}

		$this->raw_norm = $vector->normalize();
		$this->raw_vector = $vector;
	}

	public function __toString() {
		return join(" ", array_keys($this->word_hash));
	}
}
