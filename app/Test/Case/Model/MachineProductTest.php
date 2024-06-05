<?php
App::uses('MachineProduct', 'Model');

/**
 * MachineProduct Test Case
 *
 */
class MachineProductTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.machine_product',
		'app.machine',
		'app.production_run',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.currency',
		'app.purchase_order_product',
		'app.stock_movement',
		'app.order',
		'app.third_party',
		'app.invoice',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.stock_movement_type',
		'app.production_result_code',
		'app.stock_item',
		'app.production_movement',
		'app.stock_item_log',
		'app.operator',
		'app.shift'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->MachineProduct = ClassRegistry::init('MachineProduct');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MachineProduct);

		parent::tearDown();
	}

}
