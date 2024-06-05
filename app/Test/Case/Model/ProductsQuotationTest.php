<?php
App::uses('ProductsQuotation', 'Model');

/**
 * ProductsQuotation Test Case
 *
 */
class ProductsQuotationTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.products_quotation',
		'app.product',
		'app.product_category',
		'app.price',
		'app.order',
		'app.user',
		'app.location',
		'app.customer',
		'app.invoice',
		'app.quotation',
		'app.transaction',
		'app.transaction_type',
		'app.orders_product'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductsQuotation = ClassRegistry::init('ProductsQuotation');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductsQuotation);

		parent::tearDown();
	}

}
