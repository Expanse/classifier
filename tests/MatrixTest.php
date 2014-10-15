<?php

class MatrixTest extends PHPUnit_Framework_TestCase {
	public function setUp() {

	}

	public function testMatrixSetsProper() {
		$matrix = new Expanse\Classifier\Matrix(array(
			array(1, 2, 3),
			array(4, 5, 6)
		));

		$this->assertEquals(2, $matrix->row_count);
		$this->assertEquals(3, $matrix->column_count);
	}

	public function testMatrixReturnsColumn() {
		$matrix = new Expanse\Classifier\Matrix(array(
			array(1, 2, 3),
			array(4, 5, 6)
		));

		$column = $matrix->column(1);
		$this->assertEquals(array(2, 5), $column);
	}

	public function testMatrixReturnsColumnAsVector() {
		$matrix = new Expanse\Classifier\Matrix(array(
			array(1, 2, 3),
			array(4, 5, 6)
		));

		$column = $matrix->column(2, true);
		$this->assertInstanceOf('Expanse\Classifier\Vector', $column);
		$this->assertEquals(array(3, 6), $column->toArray());
	}
}
