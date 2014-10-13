<?php

use Expanse\Classifier;

class LSITest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->str1 = "This text involves dogs too. Dogs! ";
		$this->str2 = "This text revolves around cats. Cats.";
		$this->str3 = "This text deals with dogs. Dogs.";
		$this->str4 = "This text also involves cats. Cats!";
		$this->str5 = "This text involves birds. Birds.";
	}
	public function tearDown() {
	}


	public function testMatrixMultiplication() {
		$matrix1 = array(array(1,2), array(3,4));
		$matrix2 = array(array(1,2), array(3,4));

		$result = Classifier\LSI::matrix_mult($matrix1, $matrix2);
		$this->assertEquals(array(array(7, 10), array(15, 22)), $result);
	}

	public function testBasicIndexing() {
		$lsi = new Classifier\LSI();
		$lsi->add_item($this->str1);
		$lsi->add_item($this->str2);
		$lsi->add_item($this->str3);
		$lsi->add_item($this->str4);
		$lsi->add_item($this->str5);

		$this->assertFalse($lsi->needs_rebuild());

		# note that the closest match to str1 is str2, even though it is not
		# the closest text match.
		$this->assertEquals(array($this->str2, $this->str5, $this->str3), $lsi->find_related($this->str1, 3));
	}
}
