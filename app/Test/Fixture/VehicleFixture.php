<?php
/**
 * VehicleFixture
 *
 */
class VehicleFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'warehouse_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 40, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'license_plate' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 15, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'bool_active' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'list_order' => array('type' => 'integer', 'null' => false, 'default' => '100', 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'warehouse_id' => 1,
			'name' => 'Lorem ipsum dolor sit amet',
			'license_plate' => 'Lorem ipsum d',
			'bool_active' => 1,
			'list_order' => 1,
			'created' => '2020-09-11 09:28:25',
			'modified' => '2020-09-11 09:28:25'
		),
	);

}
