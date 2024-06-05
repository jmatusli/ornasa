<?php
App::uses('PaymentDeposit', 'Model');

/**
 * PaymentDeposit Test Case
 *
 */
class PaymentDepositTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.payment_deposit',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.currency',
		'app.purchase_order_product',
		'app.third_party',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.order',
		'app.stock_movement_type',
		'app.stock_movement',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.production_run',
		'app.machine',
		'app.machine_product',
		'app.operator',
		'app.shift',
		'app.production_run_type',
		'app.incidence',
		'app.user',
		'app.role',
		'app.user_log',
		'app.production_movement',
		'app.stock_item',
		'app.production_result_code',
		'app.warehouse',
		'app.stock_item_log',
		'app.product_production',
		'app.invoice',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.accounting_register_cash_receipt',
		'app.transfer'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->PaymentDeposit = ClassRegistry::init('PaymentDeposit');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PaymentDeposit);

		parent::tearDown();
	}

}
