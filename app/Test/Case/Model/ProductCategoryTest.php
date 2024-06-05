<?php
App::uses('ProductCategory', 'Model');

/**
 * ProductCategory Test Case
 *
 */
class ProductCategoryTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.product_category',
		'app.product_type',
		'app.product',
		'app.production_run',
		'app.machine',
		'app.operator',
		'app.shift',
		'app.production_movement',
		'app.stock_item',
		'app.production_result_code',
		'app.stock_movement',
		'app.order',
		'app.third_party',
		'app.stock_movement_type'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductCategory = ClassRegistry::init('ProductCategory');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductCategory);

		parent::tearDown();
	}

}
