<?php
App::uses('WarehouseProduct', 'Model');

/**
 * WarehouseProduct Test Case
 *
 */
class WarehouseProductTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.warehouse_product',
		'app.warehouse',
		'app.order',
		'app.user',
		'app.third_party',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.currency',
		'app.purchase_order_product',
		'app.purchase_order',
		'app.purchase_order_state',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.product_nature',
		'app.production_run',
		'app.machine',
		'app.machine_product',
		'app.operator',
		'app.shift',
		'app.production_type',
		'app.incidence',
		'app.production_movement',
		'app.stock_item',
		'app.production_result_code',
		'app.stock_movement',
		'app.stock_item_log',
		'app.product_price_log',
		'app.price_client_category',
		'app.product_production',
		'app.client_user',
		'app.invoice',
		'app.sales_order',
		'app.quotation',
		'app.rejected_reason',
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
		'app.role',
		'app.user_log',
		'app.user_warehouse',
		'app.stock_movement_type',
		'app.purchase_order_invoice'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->WarehouseProduct = ClassRegistry::init('WarehouseProduct');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->WarehouseProduct);

		parent::tearDown();
	}

}
