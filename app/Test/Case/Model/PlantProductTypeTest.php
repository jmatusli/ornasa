<?php
App::uses('PlantProductType', 'Model');

/**
 * PlantProductType Test Case
 *
 */
class PlantProductTypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.plant_product_type',
		'app.plant',
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
		'app.purchase_order',
		'app.third_party',
		'app.client_type',
		'app.order',
		'app.user',
		'app.role',
		'app.client_user',
		'app.user_log',
		'app.user_plant',
		'app.user_warehouse',
		'app.warehouse',
		'app.sales_order',
		'app.quotation',
		'app.rejected_reason',
		'app.zone',
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
		'app.plant_production_result_code',
		'app.stock_movement',
		'app.production_movement',
		'app.stock_item_log',
		'app.stock_movement_type',
		'app.purchase_order_invoice',
		'app.price_client_category',
		'app.product_price_log',
		'app.purchase_order_state',
		'app.product_nature',
		'app.production_type',
		'app.plant_production_type',
		'app.unit',
		'app.machine_product',
		'app.product_production',
		'app.product_threshold_volume',
		'app.warehouse_product',
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
		$this->PlantProductType = ClassRegistry::init('PlantProductType');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PlantProductType);

		parent::tearDown();
	}

}
