<?php
App::uses('ProductionType', 'Model');

/**
 * ProductionType Test Case
 *
 */
class ProductionTypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.production_type'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductionType = ClassRegistry::init('ProductionType');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductionType);

		parent::tearDown();
	}

}
