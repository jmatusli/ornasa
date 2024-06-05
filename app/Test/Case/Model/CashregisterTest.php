<?php
App::uses('Cashregister', 'Model');

/**
 * Cashregister Test Case
 *
 */
class CashregisterTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.cashregister',
		'app.transaction'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Cashregister = ClassRegistry::init('Cashregister');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Cashregister);

		parent::tearDown();
	}

}
