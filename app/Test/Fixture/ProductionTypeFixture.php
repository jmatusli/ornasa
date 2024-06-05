<?php
/**
 * ProductionTypeFixture
 *
 */
class ProductionTypeFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'short_description' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'long_description' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 200, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'list_order' => array('type' => 'integer', 'null' => false, 'default' => '100', 'unsigned' => false),
		'hex_color' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 7, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
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
			'name' => 'Lorem ipsum dolor sit amet',
			'short_description' => 'Lorem ipsum dolor sit amet',
			'long_description' => 'Lorem ipsum dolor sit amet',
			'list_order' => 1,
			'hex_color' => 'Lorem',
			'created' => '2020-07-08 11:25:33',
			'modified' => '2020-07-08 11:25:33'
		),
	);

}
