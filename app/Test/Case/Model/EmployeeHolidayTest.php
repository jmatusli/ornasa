<?php
App::uses('EmployeeHoliday', 'Model');

/**
 * EmployeeHoliday Test Case
 *
 */
class EmployeeHolidayTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
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
		$this->EmployeeHoliday = ClassRegistry::init('EmployeeHoliday');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->EmployeeHoliday);

		parent::tearDown();
	}

}
