<?php
App::uses('AccountingRegisterCashReceipt', 'Model');

/**
 * AccountingRegisterCashReceipt Test Case
 *
 */
class AccountingRegisterCashReceiptTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.accounting_register_cash_receipt',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.accounting_movement',
		'app.accounting_code',
		'app.currency',
		'app.purchase_order_product',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.third_party',
		'app.order',
		'app.stock_movement_type',
		'app.stock_movement',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.production_run',
		'app.machine',
		'app.operator',
		'app.shift',
		'app.production_movement',
		'app.stock_item',
		'app.production_result_code',
		'app.stock_item_log',
		'app.invoice',
		'app.cash_receipt_invoice'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AccountingRegisterCashReceipt = ClassRegistry::init('AccountingRegisterCashReceipt');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AccountingRegisterCashReceipt);

		parent::tearDown();
	}

}
