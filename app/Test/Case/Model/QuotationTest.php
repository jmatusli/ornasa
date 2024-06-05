<?php
App::uses('Quotation', 'Model');

/**
 * Quotation Test Case
 *
 */
class QuotationTest extends CakeTestCase {

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
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Quotation = ClassRegistry::init('Quotation');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Quotation);

		parent::tearDown();
	}

}
