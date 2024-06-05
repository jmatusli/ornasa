<?php
App::uses('AccountingRegisterInvoicesController', 'Controller');

/**
 * AccountingRegisterInvoicesController Test Case
 *
 */
class AccountingRegisterInvoicesControllerTest extends ControllerTestCase {

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
 * testIndex method
 *
 * @return void
 */
	public function testIndex() {
		$this->markTestIncomplete('testIndex not implemented.');
	}

/**
 * testView method
 *
 * @return void
 */
	public function testView() {
		$this->markTestIncomplete('testView not implemented.');
	}

/**
 * testAdd method
 *
 * @return void
 */
	public function testAdd() {
		$this->markTestIncomplete('testAdd not implemented.');
	}

/**
 * testEdit method
 *
 * @return void
 */
	public function testEdit() {
		$this->markTestIncomplete('testEdit not implemented.');
	}

/**
 * testDelete method
 *
 * @return void
 */
	public function testDelete() {
		$this->markTestIncomplete('testDelete not implemented.');
	}

}
