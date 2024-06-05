<?php
App::uses('Transaction', 'Model');

/**
 * Transaction Test Case
 *
 */
class TransactionTest extends CakeTestCase {

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
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Transaction = ClassRegistry::init('Transaction');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Transaction);

		parent::tearDown();
	}

}
