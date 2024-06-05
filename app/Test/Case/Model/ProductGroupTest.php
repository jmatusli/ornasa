<?php
App::uses('ProductGroup', 'Model');

/**
 * ProductGroup Test Case
 *
 */
class ProductGroupTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.product_group',
		'app.product_category',
		'app.product',
		'app.price',
		'app.order',
		'app.user',
		'app.role',
		'app.invoice',
		'app.customer',
		'app.quotation',
		'app.products_quotation',
		'app.transaction',
		'app.currency',
		'app.transaction_type',
		'app.cashregister',
		'app.transaction_mode',
		'app.accounting_code',
		'app.userlog',
		'app.location',
		'app.orders_product'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductGroup = ClassRegistry::init('ProductGroup');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductGroup);

		parent::tearDown();
	}

}
