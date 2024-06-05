<?php
/**
 * ProviderNatureFixture
 *
 */
class ProviderNatureFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
		'abbreviation' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 30, 'collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'),
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
			'abbreviation' => 'Lorem ipsum dolor sit amet',
			'created' => '2020-12-30 08:18:20',
			'modified' => '2020-12-30 08:18:20'
		),
	);

}
