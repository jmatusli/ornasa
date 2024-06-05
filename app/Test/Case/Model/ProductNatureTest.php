<?php
App::uses('ProductNature', 'Model');

/**
 * ProductNature Test Case
 *
 */
class ProductNatureTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.product_nature'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ProductNature = ClassRegistry::init('ProductNature');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProductNature);

		parent::tearDown();
	}

}
