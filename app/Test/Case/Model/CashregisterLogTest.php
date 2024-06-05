<?php
App::uses('CashregisterLog', 'Model');

/**
 * CashregisterLog Test Case
 *
 */
class CashregisterLogTest extends CakeTestCase {

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
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->CashregisterLog = ClassRegistry::init('CashregisterLog');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->CashregisterLog);

		parent::tearDown();
	}

}
