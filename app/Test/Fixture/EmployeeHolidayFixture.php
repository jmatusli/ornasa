<?php
/**
 * EmployeeHolidayFixture
 *
 */
class EmployeeHolidayFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'employee_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'holiday_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'days_taken' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '5,1', 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'employee_id' => 1,
			'holiday_date' => '2015-09-20 14:30:56',
			'days_taken' => '',
			'created' => '2015-09-20 14:30:56',
			'modified' => '2015-09-20 14:30:56'
		),
	);

}
