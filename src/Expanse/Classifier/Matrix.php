<?php

namespace Expanse\Classifier;

class Matrix {

	private $mtx = array();
	public $row_count;
	public $column_count;

	public function __construct($array) {
		$this->updateMatrix($array);
	}

	private function updateMatrix($values) {
		$this->mtx = $values;
		$this->row_count = count($values);
		$this->column_count = count($values[0]);
	}

	public function column($j, $asVector = false) {
		if ($j >= $this->column_count || $j < -$this->column_count) {
			return $this;
		}
		$col = array();
		for($i = 0; $i < $this->row_count; $i++) {
			$col[] = $this->mtx[$i][$j];
		}
		if ($asVector) {
			return new Vector($col);
		}
		return $col;
	}

	public function mult($matrix2) {
		$m1r = $this->row_count;
		$m1c = $this->column_count;

		$m2r = count($matrix2);
		$m2c = count($matrix2[0]);

		if ($m1c != $m2r) {
			throw new \Exception("Not possible; matrix1 has cols ${m1c}, matrix2 has rows ${m2r}");
		}

		$m3 = array();
		for($i = 0; $i < $m1r; $i++) {
			for($j = 0; $j < $m2c; $j++) {
				$m3[$i][$j] = 0;
				for($k = 0; $k < $m2r; $k++) {
					$m3[$i][$j] += $this->mtx[$i][$k] * $matrix2[$k][$j];
				}
			}
		}
		return($m3);
	}
}
