<?php
/**
 * PlantProductTypeFixture
 *
 */
class PlantProductTypeFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'assignment_datetime' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'plant_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'product_type_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'bool_assigned' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
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
			'assignment_datetime' => '2020-12-18 21:04:02',
			'plant_id' => 1,
			'product_type_id' => 1,
			'bool_assigned' => 1,
			'created' => '2020-12-18 21:04:02',
			'modified' => '2020-12-18 21:04:02'
		),
	);

}
