<?php
App::uses('Shift', 'Model');

/**
 * Shift Test Case
 *
 */
class ShiftTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.shift',
		'app.production_run',
		'app.product_type',
		'app.stock_items',
		'app.product',
		'app.stock_item',
		'app.machine',
		'app.operator'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Shift = ClassRegistry::init('Shift');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Shift);

		parent::tearDown();
	}

}
