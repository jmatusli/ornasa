<?php
App::uses('CashReceiptType', 'Model');

/**
 * CashReceiptType Test Case
 *
 */
class CashReceiptTypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.cash_receipt_type',
		'app.cash_receipt',
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
		$this->CashReceiptType = ClassRegistry::init('CashReceiptType');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->CashReceiptType);

		parent::tearDown();
	}

}
