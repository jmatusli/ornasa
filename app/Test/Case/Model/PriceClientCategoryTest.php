<?php
App::uses('PriceClientCategory', 'Model');

/**
 * PriceClientCategory Test Case
 *
 */
class PriceClientCategoryTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.price_client_category'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->PriceClientCategory = ClassRegistry::init('PriceClientCategory');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PriceClientCategory);

		parent::tearDown();
	}

}
