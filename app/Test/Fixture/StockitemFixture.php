<?php
/**
 * StockItemFixture
 *
 */
class StockItemFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'purchase_order_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'production_run_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'sale_order_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'product_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'product_type_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'product_quantity' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'product_unit_price' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'previous_quantity' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'remaining_quantity' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'production_result_code_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'stock_movement' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_bin', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'product_id' => array('column' => 'product_id', 'unique' => 0)
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
			'purchase_order_id' => 1,
			'production_run_id' => 1,
			'sale_order_id' => 1,
			'product_id' => 1,
			'product_type_id' => 1,
			'product_quantity' => 1,
			'product_unit_price' => 1,
			'previous_quantity' => 1,
			'remaining_quantity' => 1,
			'production_result_code_id' => 1,
			'stock_movement' => 'Lorem ipsum dolor sit amet',
			'created' => '2014-09-25 05:11:10',
			'modified' => '2014-09-25 05:11:10'
		),
	);

}
