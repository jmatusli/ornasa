<?php
App::uses('ProductionTypeProduct', 'Model');

/**
 * ProductionTypeProduct Test Case
 *
 */
class ProductionTypeProductTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.production_type_product',
		'app.production_type',
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
		'app.purchase_order',
		'app.third_party',
		'app.price_client_category',
		'app.product_price_log',
		'app.user',
		'app.role',
		'app.order',
		'app.stock_movement_type',
		'app.warehouse',
		'app.sales_order',
		'app.quotation',
		'app.rejected_reason',
		'app.quotation_product',
		'app.quotation_remark',
		'app.action_type',
		'app.invoice',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.sales_order_product',
		'app.sales_order_remark',
		'app.stock_item',
		'app.production_result_code',
		'app.stock_movement',
		'app.production_movement',
		'app.stock_item_log',
		'app.user_warehouse',
		'app.purchase_order_invoice',
		'app.client_user',
		'app.user_log',
		'app.purchase_order_state',
		'app.product_nature',
		'app.machine_product',
		'app.machine',
		'app.product_production',
		'app.operator',
		'app.shift',
		'app.incidence'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductionTypeProduct = ClassRegistry::init('ProductionTypeProduct');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductionTypeProduct);

		parent::tearDown();
	}

}
