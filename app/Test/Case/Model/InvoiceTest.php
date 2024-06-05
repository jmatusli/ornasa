<?php
App::uses('Invoice', 'Model');

/**
 * Invoice Test Case
 *
 */
class InvoiceTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.invoice',
		'app.order',
		'app.third_party',
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
		'app.client',
		'app.currency',
		'app.purchase_order_product',
		'app.cashbox_accounting_code'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Invoice = ClassRegistry::init('Invoice');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Invoice);

		parent::tearDown();
	}

}
