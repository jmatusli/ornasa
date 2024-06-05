<?php
App::uses('ProductionMovement', 'Model');

/**
 * ProductionMovement Test Case
 *
 */
class ProductionMovementTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.production_movement',
		'app.stock_item',
		'app.purchase_order',
		'app.production_run',
		'app.product_type',
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
		$this->ProductionMovement = ClassRegistry::init('ProductionMovement');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductionMovement);

		parent::tearDown();
	}

}
