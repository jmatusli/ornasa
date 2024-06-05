<?php
/**
 * StockItemLogFixture
 *
 */
class StockItemLogFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'stock_item_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'stock_movement_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'production_movement_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'stockitem_date' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'product_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'product_quantity' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'product_unit_price' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'modified' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'indexes' => array(
			
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
			'stock_item_id' => 1,
			'stock_movement_id' => 1,
			'production_movement_id' => 1,
			'stockitem_date' => '2014-10-11 12:01:18',
			'product_id' => 1,
			'product_quantity' => 1,
			'product_unit_price' => 1,
			'created' => 1,
			'modified' => 1
		),
	);

}
