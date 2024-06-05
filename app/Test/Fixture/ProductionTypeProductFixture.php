<?php
/**
 * ProductionTypeProductFixture
 *
 */
class ProductionTypeProductFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'assignment_datetime' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'production_type_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'product_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
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
			'assignment_datetime' => '2020-07-08 16:45:45',
			'production_type_id' => 1,
			'product_id' => 1,
			'created' => '2020-07-08 16:45:45',
			'modified' => '2020-07-08 16:45:45'
		),
	);

}
