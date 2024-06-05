<?php
App::uses('HolidayType', 'Model');

/**
 * HolidayType Test Case
 *
 */
class HolidayTypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.holiday_type',
		'app.employee_holiday',
		'app.employee'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->HolidayType = ClassRegistry::init('HolidayType');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->HolidayType);

		parent::tearDown();
	}

}
