<?php
App::uses('Constant', 'Model');

/**
 * Constant Test Case
 *
 */
class ConstantTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.constant'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Constant = ClassRegistry::init('Constant');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Constant);

		parent::tearDown();
	}

}
