<?php
App::uses('StockMovement', 'Model');

/**
 * StockMovement Test Case
 *
 */
class StockMovementTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.stock_movement',
		'app.stock_item',
		'app.product_type',
		'app.production_run',
		'app.machine',
		'app.operator',
		'app.shift',
		'app.production_movement',
		'app.product',
		'app.production_result_code',
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
		$this->StockMovement = ClassRegistry::init('StockMovement');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->StockMovement);

		parent::tearDown();
	}

}
