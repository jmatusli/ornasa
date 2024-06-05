<?php
/**
 * ProductionMovementFixture
 *
 */
class ProductionMovementFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_bin', 'charset' => 'utf8'),
		'description' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_bin', 'charset' => 'utf8'),
		'stock_item_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'production_run_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'product_type_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'product_type_quantity' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'product_type_unit_price' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'product_type_id' => array('column' => 'product_type_id', 'unique' => 0)
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
			'name' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'stock_item_id' => 1,
			'production_run_id' => 1,
			'product_type_id' => 1,
			'product_type_quantity' => 1,
			'product_type_unit_price' => 1,
			'created' => '2014-09-27 19:03:39',
			'modified' => '2014-09-27 19:03:39'
		),
	);

}
