<?php
App::uses('StockItemLog', 'Model');

/**
 * StockItemLog Test Case
 *
 */
class StockItemLogTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.stock_item_log',
		'app.stock_item',
		'app.product',
		'app.product_type',
		'app.product_category',
		'app.production_run',
		'app.machine',
		'app.operator',
		'app.shift',
		'app.production_movement',
		'app.stock_movement',
		'app.order',
		'app.third_party',
		'app.stock_movement_type',
		'app.production_result_code'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->StockItemLog = ClassRegistry::init('StockItemLog');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->StockItemLog);

		parent::tearDown();
	}

}
