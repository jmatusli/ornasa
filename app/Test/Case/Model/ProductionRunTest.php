<?php
App::uses('ProductionRun', 'Model');

/**
 * ProductionRun Test Case
 *
 */
class ProductionRunTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.production_run',
		'app.product_type',
		'app.stock_items',
		'app.product',
		'app.stock_item',
		'app.machine',
		'app.operator',
		'app.shift'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductionRun = ClassRegistry::init('ProductionRun');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductionRun);

		parent::tearDown();
	}

}
