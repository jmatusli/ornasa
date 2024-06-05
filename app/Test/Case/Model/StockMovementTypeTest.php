<?php
App::uses('StockMovementType', 'Model');

/**
 * StockMovementType Test Case
 *
 */
class StockMovementTypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.stock_movement_type',
		'app.order',
		'app.third_party'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->StockMovementType = ClassRegistry::init('StockMovementType');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->StockMovementType);

		parent::tearDown();
	}

}
