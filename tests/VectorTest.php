<?php

class VectorTest extends PHPUnit_Framework_TestCase {
	public function setUp() {

	}

	public function testMagnitudeCalculation() {
		$vector = new Expanse\Classifier\Vector(array(5, 8, 2));
		$result = $vector->magnitude();
		$this->assertEquals(9.6436507609929549, $result);
	}

	public function testNormalize() {
		$vector = new Expanse\Classifier\Vector(array(5, 8, 2));
		$result = $vector->normalize();
		$this->assertEquals(array(0.51847584736521268, 0.8295613557843402, 0.20739033894608505), $result);
	}
}
