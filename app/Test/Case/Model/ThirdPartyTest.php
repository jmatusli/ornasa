<?php
App::uses('ThirdParty', 'Model');

/**
 * ThirdParty Test Case
 *
 */
class ThirdPartyTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.third_party',
		'app.order',
		'app.stock_movement_type'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ThirdParty = ClassRegistry::init('ThirdParty');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ThirdParty);

		parent::tearDown();
	}

}
