<?php
App::uses('TransactionsController', 'Controller');

/**
 * TransactionsController Test Case
 *
 */
class TransactionsControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.transaction',
		'app.user',
		'app.order',
		'app.location',
		'app.customer',
		'app.invoice',
		'app.quotation',
		'app.product',
		'app.product_category',
		'app.price',
		'app.orders_product',
		'app.products_quotation',
		'app.transaction_type',
		'app.currency',
		'app.cashregister',
		'app.transaction_mode',
		'app.accounting'
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
