<?php
App::uses('TransactionMode', 'Model');

/**
 * TransactionMode Test Case
 *
 */
class TransactionModeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.transaction_mode',
		'app.transaction'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->TransactionMode = ClassRegistry::init('TransactionMode');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TransactionMode);

		parent::tearDown();
	}

}
