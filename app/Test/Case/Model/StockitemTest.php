<?php
App::uses('StockItem', 'Model');

/**
 * StockItem Test Case
 *
 */
class StockItemTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.stock_item',
		'app.purchase_order',
		'app.production_run',
		'app.product_type',
		'app.stock_items',
		'app.product',
		'app.machine',
		'app.operator',
		'app.shift',
		'app.sale_order',
		'app.production_result_code'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->StockItem = ClassRegistry::init('StockItem');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->StockItem);

		parent::tearDown();
	}

}
