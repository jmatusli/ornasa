<?php
/**
 * ProductPriceLogFixture
 *
 */
class ProductPriceLogFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'price_datetime' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'product_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'raw_material_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'price' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '18,8', 'unsigned' => false),
		'currency_id' => array('type' => 'integer', 'null' => false, 'default' => '1', 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
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
			'price_datetime' => '2019-12-12 10:53:19',
			'product_id' => 1,
			'raw_material_id' => 1,
			'user_id' => 1,
			'price' => '',
			'currency_id' => 1,
			'created' => '2019-12-12 10:53:19',
			'modified' => '2019-12-12 10:53:19'
		),
	);

}
