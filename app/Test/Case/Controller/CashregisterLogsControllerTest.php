<?php
App::uses('CashregisterLogsController', 'Controller');

/**
 * CashregisterLogsController Test Case
 *
 */
class CashregisterLogsControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.cashregister_log',
		'app.user',
		'app.role',
		'app.invoice',
		'app.order',
		'app.location',
		'app.customer',
		'app.quotation',
		'app.product',
		'app.product_category',
		'app.product_group',
		'app.price',
		'app.orders_product',
		'app.products_quotation',
		'app.transaction',
		'app.currency',
		'app.exchange_rate',
		'app.transaction_type',
		'app.cashregister',
		'app.transaction_mode',
		'app.accounting_code',
		'app.userlog'
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
