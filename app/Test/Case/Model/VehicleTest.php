<?php
App::uses('Vehicle', 'Model');

/**
 * Vehicle Test Case
 *
 */
class VehicleTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.vehicle',
		'app.warehouse',
		'app.order',
		'app.client_type',
		'app.third_party',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.currency',
		'app.purchase_order_product',
		'app.purchase_order',
		'app.purchase_order_state',
		'app.user',
		'app.role',
		'app.client_user',
		'app.user_log',
		'app.user_warehouse',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.product_nature',
		'app.production_type',
		'app.production_run',
		'app.machine',
		'app.machine_product',
		'app.operator',
		'app.shift',
		'app.incidence',
		'app.production_movement',
		'app.stock_item',
		'app.production_result_code',
		'app.stock_movement',
		'app.stock_item_log',
		'app.unit',
		'app.product_price_log',
		'app.price_client_category',
		'app.product_production',
		'app.product_threshold_volume',
		'app.warehouse_product',
		'app.zone',
		'app.quotation',
		'app.rejected_reason',
		'app.sales_order',
		'app.invoice',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.sales_order_product',
		'app.sales_order_remark',
		'app.action_type',
		'app.quotation_product',
		'app.quotation_remark',
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
		$this->Vehicle = ClassRegistry::init('Vehicle');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Vehicle);

		parent::tearDown();
	}

}
