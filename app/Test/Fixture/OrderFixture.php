<?php
/**
 * OrderFixture
 *
 */
class OrderFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'order_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'invoice_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'third_party_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'stock_movement_type_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'total_price' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
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
			'order_date' => '2014-09-25 05:10:24',
			'invoice_code' => 'Lorem ipsum dolor sit amet',
			'third_party_id' => 1,
			'stock_movement_type_id' => 1,
			'total_price' => 1,
			'created' => '2014-09-25 05:10:24',
			'modified' => '2014-09-25 05:10:24'
		),
	);

}
