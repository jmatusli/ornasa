<?php
App::uses('UserWarehouse', 'Model');

/**
 * UserWarehouse Test Case
 *
 */
class UserWarehouseTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.user_warehouse',
		'app.user',
		'app.third_party',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.currency',
		'app.purchase_order_product',
		'app.purchase_order',
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
		'app.production_movement',
		'app.stock_item',
		'app.production_result_code',
		'app.stock_movement',
		'app.order',
		'app.stock_movement_type',
		'app.invoice',
		'app.sales_order',
		'app.quotation',
		'app.rejected_reason',
		'app.warehouse',
		'app.stock_item_log',
		'app.quotation_image',
		'app.quotation_product',
		'app.quotation_remark',
		'app.action_type',
		'app.sales_order_product',
		'app.sales_order_remark',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.product_price_log',
		'app.product_production',
		'app.client_user',
		'app.role',
		'app.user_log'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->UserWarehouse = ClassRegistry::init('UserWarehouse');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->UserWarehouse);

		parent::tearDown();
	}

}
