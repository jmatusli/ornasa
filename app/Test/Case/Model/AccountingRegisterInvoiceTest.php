<?php
App::uses('AccountingRegisterInvoice', 'Model');

/**
 * AccountingRegisterInvoice Test Case
 *
 */
class AccountingRegisterInvoiceTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.accounting_register_invoice',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.accounting_movement',
		'app.accounting_code',
		'app.currency',
		'app.purchase_order_product',
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
		'app.cashbox_accounting_code'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AccountingRegisterInvoice = ClassRegistry::init('AccountingRegisterInvoice');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AccountingRegisterInvoice);

		parent::tearDown();
	}

}
