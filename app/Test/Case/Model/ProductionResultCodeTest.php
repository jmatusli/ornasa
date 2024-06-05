<?php
App::uses('ProductionResultCode', 'Model');

/**
 * ProductionResultCode Test Case
 *
 */
class ProductionResultCodeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.production_result_code',
		'app.stock_item'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductionResultCode = ClassRegistry::init('ProductionResultCode');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductionResultCode);

		parent::tearDown();
	}

}
