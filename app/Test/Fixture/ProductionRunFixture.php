<?php
/**
 * ProductionRunFixture
 *
 */
class ProductionRunFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'production_run_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_bin', 'charset' => 'utf8'),
		'product_type_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'product_type_quantity' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'machine_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'operator_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'shift_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'energy_use' => array('type' => 'float', 'null' => true, 'default' => null, 'unsigned' => false),
		'production_complete' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_bin', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'production_run_code' => 'Lorem ip',
			'product_type_id' => 1,
			'product_type_quantity' => 1,
			'machine_id' => 1,
			'operator_id' => 1,
			'shift_id' => 1,
			'energy_use' => 1,
			'production_complete' => 1,
			'created' => '2014-09-25 05:10:43',
			'modified' => '2014-09-25 05:10:43'
		),
	);

}
