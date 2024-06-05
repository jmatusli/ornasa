<?php
App::uses('QuotationsController', 'Controller');

/**
 * QuotationsController Test Case
 *
 */
class QuotationsControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.quotation',
		'app.user',
		'app.customer',
		'app.invoice',
		'app.order',
		'app.location',
		'app.transaction_type',
		'app.transaction',
		'app.product',
		'app.product_category',
		'app.price',
		'app.orders_product',
		'app.products_quotation'
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
