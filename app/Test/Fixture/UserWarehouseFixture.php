<?php
/**
 * UserWarehouseFixture
 *
 */
class UserWarehouseFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'warehouse_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'assignment_datetime' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'bool_assigned' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
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
			'user_id' => 1,
			'warehouse_id' => 1,
			'assignment_datetime' => '2020-05-07 07:53:35',
			'bool_assigned' => 1,
			'created' => '2020-05-07 07:53:35',
			'modified' => '2020-05-07 07:53:35'
		),
	);

}
