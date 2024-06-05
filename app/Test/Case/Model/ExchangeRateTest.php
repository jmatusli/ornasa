<?php
App::uses('ExchangeRate', 'Model');

/**
 * ExchangeRate Test Case
 *
 */
class ExchangeRateTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.exchange_rate',
		'app.conversion_currency',
		'app.base_currency'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ExchangeRate = ClassRegistry::init('ExchangeRate');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ExchangeRate);

		parent::tearDown();
	}

}
