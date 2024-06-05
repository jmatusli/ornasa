<?php
App::uses('CashReceipt', 'Model');

/**
 * CashReceipt Test Case
 *
 */
class CashReceiptTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.currency',
		'app.purchase_order_product',
		'app.client'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->CashReceipt = ClassRegistry::init('CashReceipt');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->CashReceipt);

		parent::tearDown();
	}

}
