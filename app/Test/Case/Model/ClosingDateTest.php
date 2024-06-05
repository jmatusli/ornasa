<?php
App::uses('ClosingDate', 'Model');

/**
 * ClosingDate Test Case
 *
 */
class ClosingDateTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.closing_date'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ClosingDate = ClassRegistry::init('ClosingDate');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ClosingDate);

		parent::tearDown();
	}

}
