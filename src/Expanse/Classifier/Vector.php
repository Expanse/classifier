<?php

namespace Expanse\Classifier;

class Vector extends \ArrayIterator implements \ArrayAccess {

	private $vec = array();
	private $iterPos = 0;

	public function __construct($word_list) {
		if ($word_list instanceOf WordList) {
			if ($word_list->size() == 0) {
				$size = $word_list->size() + 1;
			} else {
				$size = $word_list->size();
			}
			$this->vec = array_fill(0, $size, 0);
		} elseif (is_array($word_list)) {
			$this->vec = $word_list;
		} else {
			throw new \Exception("Not implemented");
		}

	}

	public function normalize() {
		$_vec = $this->vec;
		return array_map(function($val) {
			return $val / $this->magnitude();
		}, $_vec);
	}

	public function sum() {
		return array_sum($this->vec);
	}

	public function magnitude() {
		return sqrt(array_sum($this->square()));
	}

	public function collect($callback) {
		call_user_func_array($callback, $this->vec);
	}

	public function toArray() {
		return $this->vec;
	}

	private function square() {
		$_vec = $this->vec;
		return array_map(function($val) {
			return $val * $val;
		}, $_vec);
	}

	public function offsetExists($offset) {
		throw new \BadMethodCallException("Bad method " . __METHOD__);
	}
	
	public function offsetGet($offset) {
		return $this->vec[$offset];
	}
	
	public function offsetSet($offset, $value) {
		$this->vec[$offset] = $value;
	}

	public function offsetUnset($offset) {
		throw new \BadMethodCallException("Bad method " . __METHOD__);
	}

	public function valid() {
		if (isset($this->vec[$this->iterPos])) return true;

		return false;
	}

	public function current() {
		return $this->vec[$this->iterPos];
	}

	public function next() {
		$this->iterPos++;
	}
}
