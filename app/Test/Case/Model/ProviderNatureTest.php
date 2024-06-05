<?php
App::uses('ProviderNature', 'Model');

/**
 * ProviderNature Test Case
 *
 */
class ProviderNatureTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.provider_nature',
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
		'app.order',
		'app.client_type',
		'app.quotation',
		'app.rejected_reason',
		'app.sales_order',
		'app.invoice',
		'app.warehouse',
		'app.stock_item',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.plant_product_type',
		'app.plant',
		'app.machine',
		'app.production_run',
		'app.operator',
		'app.shift',
		'app.production_type',
		'app.plant_production_type',
		'app.incidence',
		'app.production_movement',
		'app.machine_product',
		'app.user_plant',
		'app.product_nature',
		'app.unit',
		'app.product_price_log',
		'app.price_client_category',
		'app.stock_movement',
		'app.production_result_code',
		'app.product_production',
		'app.product_threshold_volume',
		'app.warehouse_product',
		'app.stock_item_log',
		'app.user_warehouse',
		'app.accounting_register_invoice',
		'app.cash_receipt_invoice',
		'app.cash_receipt',
		'app.cash_receipt_type',
		'app.accounting_register_cash_receipt',
		'app.zone',
		'app.sales_order_product',
		'app.sales_order_remark',
		'app.action_type',
		'app.quotation_product',
		'app.quotation_remark',
		'app.stock_movement_type',
		'app.purchase_order_invoice',
		'app.client_user',
		'app.user_log'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProviderNature = ClassRegistry::init('ProviderNature');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProviderNature);

		parent::tearDown();
	}

}
