<?php
App::uses('Transfer', 'Model');

/**
 * Transfer Test Case
 *
 */
class TransferTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.transfer',
		'app.currency',
		'app.purchase_order_product',
		'app.bank_accounting_code',
		'app.cashbox_accounting_code',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.accounting_movement',
		'app.accounting_code'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Transfer = ClassRegistry::init('Transfer');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Transfer);

		parent::tearDown();
	}

}
