<?php
App::uses('PurchaseOrderInvoice', 'Model');

/**
 * PurchaseOrderInvoice Test Case
 *
 */
class PurchaseOrderInvoiceTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.purchase_order_invoice',
		'app.purchase_order',
		'app.third_party',
		'app.accounting_code',
		'app.accounting_movement',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.currency',
		'app.purchase_order_product',
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
		'app.order',
		'app.stock_movement_type',
		'app.stock_movement',
		'app.production_result_code',
		'app.stock_item',
		'app.warehouse',
		'app.sales_order',
		'app.quotation',
		'app.rejected_reason',
		'app.quotation_image',
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
		'app.stock_item_log',
		'app.production_movement',
		'app.user_warehouse',
		'app.client_user',
		'app.user_log',
		'app.product_price_log',
		'app.price_client_category',
		'app.product_production',
		'app.purchase_order_state'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->PurchaseOrderInvoice = ClassRegistry::init('PurchaseOrderInvoice');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PurchaseOrderInvoice);

		parent::tearDown();
	}

}
